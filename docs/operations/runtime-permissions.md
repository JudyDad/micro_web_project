# Phase 2 runtime database permissions

Use a separate migration/provisioning account and runtime account. Keep credentials outside Git. The runtime needs SELECT on the synthetic application database, INSERT on audit_events, and column-only UPDATE on web_principals.password_hash/updated_at for successful password upgrades. It must not receive DDL, business writes, principal provisioning, grant/publication changes or audit UPDATE/DELETE.

Example for a dedicated local database; substitute the host and generated secret privately:

```sql
CREATE USER 'micro_projects_web_local'@'127.0.0.1' IDENTIFIED BY '<private-generated-secret>';
GRANT SELECT ON micro_projects_local.* TO 'micro_projects_web_local'@'127.0.0.1';
GRANT INSERT ON micro_projects_local.audit_events TO 'micro_projects_web_local'@'127.0.0.1';
GRANT UPDATE (password_hash, updated_at) ON micro_projects_local.web_principals TO 'micro_projects_web_local'@'127.0.0.1';
```

Provision the synthetic fixture with the migration account before switching the local .env to the runtime account. The application fails rather than silently discarding audit errors. RuntimeAuditPrivilegesTest verifies that audit inserts succeed while audit mutation, business writes, disabling accounts, changing grants and publishing snapshots fail under the runtime identity. This is append-only enforcement for the application account; administrators remain privileged and require protected operational controls/backups.

Only the synthetic `projects.view` grant is provisioned in Phase 2. Real staff grants require D07 decisions and an audited provisioning workflow. Replacing a publication must not rebind principals by matching usernames; current principals bind to immutable snapshot occurrences and access fails closed if that occurrence is no longer published.
