"""Independently validate the metadata-only Phase 3 proposal; never execute DDL."""

import hashlib
import json
import re
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
PLAN = ROOT / "docs/planning"
PROPOSAL = PLAN / "phase-3/operational-schema.json"


def require(condition, message):
    if not condition:
        raise ValueError(message)


def read_json(path):
    return json.loads(path.read_text(encoding="utf-8-sig"))


def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def validate():
    proposal = read_json(PROPOSAL)
    source_path = PLAN / "source-schemas.json"
    manifest_path = ROOT / "database/schema/preservation-v1.json"
    sources = read_json(source_path)
    manifest = read_json(manifest_path)
    require(proposal["source_manifest_sha256"] == digest(manifest_path), "Preservation manifest changed")
    require(proposal["source_schema_hash_encoding"] == "UTF-8 metadata text with LF newlines; preservation manifest remains byte-for-byte", "Unknown metadata hash encoding")
    source_metadata_hash = hashlib.sha256(source_path.read_bytes().replace(b"\r\n", b"\n")).hexdigest()
    require(proposal["source_schema_sha256"] == source_metadata_hash, "Source DDL evidence changed")

    # Parse source DDL independently rather than trusting generator counts.
    originals = {}
    for city, tables in sources.items():
        for name, ddl in tables.items():
            columns = {}
            for line in ddl.splitlines():
                match = re.match(r"\s*`([^`]+)`\s+(.+?)\s*,?\s*$", line)
                if match:
                    columns[match[1]] = match[2].rstrip(",")
            require(columns, f"Unparsed source columns: {city}.{name}")
            originals[(city, name)] = columns

    preserved = {(row["city"], row["source_table"]): row for row in manifest["tables"]}
    mappings = proposal["mappings"]
    require(len(mappings) == len(originals), "Missing or duplicate source table mapping")
    require(len({(row["city"], row["source_table"]) for row in mappings}) == len(mappings), "Duplicate source mapping")
    require(set(originals) == {(row["city"], row["source_table"]) for row in mappings}, "Source table coverage mismatch")

    physical = proposal["tables"] + proposal["support_tables"]
    by_name = {table["name"]: table for table in physical}
    require(len(by_name) == len(physical), "Duplicate target table name")
    require(len({name.casefold() for name in by_name}) == len(by_name), "Case-insensitive table name collision")
    column_sets = {}
    for name, table in by_name.items():
        require(len(name) <= 64, f"Table identifier too long: {name}")
        columns = {column["name"]: column["definition"] for column in table["columns"]}
        require(len(columns) == len(table["columns"]), f"Duplicate column: {name}")
        require(len({col.casefold() for col in columns}) == len(columns), f"Case-insensitive column collision: {name}")
        require(all(len(col) <= 64 for col in columns), f"Column identifier too long: {name}")
        column_sets[name] = columns
        for index in table["indexes"]:
            require(all(col in columns for col in index["columns"]), f"Unknown indexed column: {name}")
            require(bool(index.get("reason")), f"Unexplained index: {name}")
        for fk in table["foreign_keys"]:
            require(all(col in columns for col in fk["columns"]), f"Unknown FK column: {name}")
            require(len(fk["columns"]) == len(fk["parent_columns"]), f"FK arity mismatch: {name}")
            require("RESTRICT" in fk["actions"].upper() and "CASCADE" not in fk["actions"].upper(), f"Destructive FK action: {name}")

    for table in physical:
        for fk in table["foreign_keys"]:
            parent = fk["parent_table"]
            if parent in by_name:
                require(all(col in column_sets[parent] for col in fk["parent_columns"]), f"Unknown parent column: {parent}")
                unique = [idx["columns"] for idx in by_name[parent]["indexes"] if idx["kind"].upper() in ("PRIMARY", "UNIQUE")]
                require(fk["parent_columns"] in unique, f"FK lacks exact referenced unique key: {table['name']} -> {parent}")

    column_count = 0
    archive_count = 0
    for mapping in mappings:
        key = (mapping["city"], mapping["source_table"])
        source = originals[key]
        require(mapping["preservation_table"] == preserved[key]["target_table"], f"Wrong preservation target: {key}")
        require(mapping["legacy_primary_key"] == preserved[key]["legacy_primary_key"], f"Changed key component/order: {key}")
        columns = mapping["columns"]
        require(len(columns) == len(source), f"Source column count mismatch: {key}")
        require({col["source_column"] for col in columns} == set(source), f"Source column coverage mismatch: {key}")
        target = mapping["target_table"]
        if target is None:
            archive_count += 1
        else:
            require(target in by_name, f"Missing operational target: {key}")
        for column in columns:
            col = column["source_column"]
            require(column["source_definition"] == source[col], f"Changed source definition: {key}.{col}")
            require(column["preservation_column"] == col, f"Wrong preservation column: {key}.{col}")
            if target is not None:
                dest = column["target_column"]
                require(dest in column_sets[target], f"Unmapped target column: {key}.{col}")
                require(column["target_definition"] == column_sets[target][dest], f"Target definition mismatch: {key}.{col}")
            else:
                require(column["target_column"] is None, f"Archive-only column has operational target: {key}.{col}")
            require(bool(column["disposition"]), f"Missing disposition: {key}.{col}")
            column_count += 1

    expected_relationships = set()
    for row in manifest["tables"]:
        for rel in row["source_foreign_keys"]:
            expected_relationships.add((row["city"], row["source_table"], rel["contract"], tuple(rel["child_columns"]), rel["parent_table"], tuple(rel["parent_columns"])))
    actual_relationships = set()
    for rel in proposal["relationships"]:
        for occurrence in rel["occurrences"]:
            city = occurrence["city"]
            child = (city, rel["child_source_table"])
            parent = (city, rel["parent_source_table"])
            require(child in originals and parent in originals, f"Relationship table missing: {rel['id']}")
            require(all(col in originals[child] for col in rel["child_columns"]), f"Unknown child source column: {rel['id']}")
            require(all(col in originals[parent] for col in rel["parent_columns"]), f"Unknown parent source column: {rel['id']}")
            if occurrence["contract"] != "application-only":
                key = (city, child[1], occurrence["contract"], tuple(rel["child_columns"]), parent[1], tuple(rel["parent_columns"]))
                require(key not in actual_relationships, f"Duplicate relationship occurrence: {key}")
                actual_relationships.add(key)
    require(expected_relationships == actual_relationships, "Declared relationship coverage mismatch")

    return {
        "source_city_table_instances": len(originals),
        "source_table_names": len({name for _, name in originals}),
        "source_columns": column_count,
        "shared_tables": len(proposal["tables"]),
        "support_tables": len(proposal["support_tables"]),
        "preservation_only_instances": archive_count,
        "declared_relationship_occurrences": len(actual_relationships),
        "result": "metadata checks passed; no SQL executed or data parity certified",
    }


if __name__ == "__main__":
    print(json.dumps(validate(), indent=2))
