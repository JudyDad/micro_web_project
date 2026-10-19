# P4.1 people and families workflow contract

Date: 19 October 2026. Static source review; this document is not evidence of runtime acceptance or five-city data parity.

## Authority and scope

`docs/planning/team-execution-plan.md` P4.1 covers beneficiaries, family links, education/work/history and duplicate-number search without silent person consolidation. The approved P3 physical schema supports new web entities explicitly (`phase-3/schema-proposal.md`, paragraphs beginning “New web entities” and “Source defaults”). The instruction to start P4.1 authorizes implementation of that workflow; no additional business-table migration is inherently necessary for these records.

Desktop evidence is `D:/MyProjects/MicroProject newTest/MyWorkApplication/Application_New_Form.cs`. Source remains read-only. Source behaviors below are evidence, not authorization to reproduce unsafe SQL, incomplete access checks, or accidental data loss.

## Field and relationship mapping

All `op_*` records use an `operational_entities.id` identity with the same city, source and source-table discriminator. New web rows use `identity_kind=web`, null original-key bytes/hash and zero fabricated origins. Raw `c_` legacy IDs may be null; canonical references carry resolved entity IDs. Do not put a web identity into an original numeric legacy ID column.

| Domain | Approved table / fields | Critical relationships |
|---|---|---|
| Person | `op_person`: first/last/father/mother names, English name, sex, birth date, national number, registration place, phone/mobile/mobile2, address, intermediary, home notes, military fields, work-years text, household counts and other notes | Marital status, parish, two priests, street, home asset type and person state; preserve unresolved legacy reference states |
| Family | `op_family.c_F_Number` | Family number is text, nullable historically; no uniqueness constraint |
| Household membership | `op_person_family`: Person_ID, P_Provider_ID, Family_ID, Relation_ID, EducationLevel_ID, Education_ID, HasWork, Work_Name | Member and provider are separate person relationships; family and relation are separate entities. Do not infer a provider from the member or collapse multiple associations |
| Education | `op_person_education`: Person_ID, Education_ID, Level_ID | Person, education and education level. Education's type is reached through `op_education` to `op_educationtype`; family-member education remains on the family association |
| Courses | `op_person_course`: Person_ID, SubCategory_ID, Name, Place, Donor, Date | Person and subcategory. Date is historical text (`varchar(200)`), not a newly assumed date type |
| Work / experience | `op_person_workexp`: Person_ID, MicroProject_ID, Work_ID, W_WorkType_ID, W_Description, W_BeginDate, W_EndDate, W_PlaceState, W_Place, W_Contact, W_Salary, W_CauseOfLoseDescription, W_Status | Person, optional project context, work catalog and work type. Start/end years are `varchar(4)`, salary preserves the approved double storage |
| Application association | `op_person_microproject` | Beneficiary/partner role belongs to the person-project association, not a global person type. Do not add uniqueness of person/project pairs |

Physical reference mappings are authoritative in `docs/planning/phase-3/operational-schema.json` and `schema-physical.md` (person at line 2050, course 2287, education 2326, membership 2368, work 2707). Every selected relationship must be server-validated for the same city **and source**, including a shared catalog's source-specific alias. A matching name or original ID is not sufficient. Check the related entity's table kind as well.

## Validation and duplicate decisions

1. Preserve national and family numbers as strings, including leading zeroes. Never make either globally unique. The desktop explicitly allows continuing after a duplicate warning. Display all accessible matches rather than silently choosing the desktop's first returned row.
2. National-number search (`Application_New_Form.cs:7741`) checks the supplied number and its variant with exactly one leading zero added or removed, excluding the current person. The desktop joins project associations, so its search omits unlinked people; the web directory may show those people explicitly as well. Empty input must return no duplicate candidates without accessing character zero. Treat `%` and `_` literally in number searches; desktop `LIKE` wildcard expansion is not an intentional identity rule.
3. National length other than 11 produces a warning in the desktop (`:2395`), not an unconditional save rejection. Do not reject existing historical values merely because they fail that length hint, and do not normalize them on read or save.
4. Family-number search (`:7836`) looks for a matching family with a beneficiary relation and a different provider. Its dialog (`:3238`) offers opening the existing application, explicitly reusing the family, or cancelling. Family reuse is a deliberate same-source association, never person consolidation. The directory can reveal matching family rows independently of whether they currently have an application.
5. The application save checks first name, last name, sex and person-project role (`:4316` onward). Additional **new applicant** validation under `validateBenificiry` requires father/mother names, national number, birth date, marital status, parish, intermediary, at least one contact number, street, detailed address and home type (`:4345–4470`). Full application adds at least one priest and conditional military validation. These are application-submission checks; applying all of them to a standalone family member or an unrelated partial edit would change desktop behavior. P4.2 must retain that distinction.
6. New standalone people must have an explicitly documented minimal identity contract; require first and last names and a selected sex when creating the beneficiary-facing identity slice. Any narrower field editor validates supplied fields and lengths/types without forcing cleanup of unrelated historical fields. Household members follow their own role-specific contract rather than full applicant requirements. A birth date newly supplied must be a valid calendar date; do not invent age eligibility limits.
7. Current work writes `W_Status='Yes'` (`:4999`). Previous experience writes `'NO'` (`:5059`) while the loader selects `'No'` (`:7446`). Target payload collation is binary, so display/filter both known historical spellings explicitly. Preserve the stored spelling and expose unknown/null states instead of silently classifying them. Do not coerce historical course dates or work years into dates.
8. Repeated education, course, work and household rows are not automatically duplicates. No new uniqueness constraint on person/education, member/family, national number or family number is supported by the approved preservation schema or the observed continue-after-warning flow.

## Safe write implementation boundary

Create/update can proceed on the approved tables once the implementation supplies transactional identity allocation, scoped relationship validation, explicit per-city mutation authorization, audit events and stale-edit protection. No schema gap prevents the basic person/family/education/course/work payloads themselves.

The current account service initially exposes only `projects.view` and `attachments.download`; neither grants a people mutation. Add explicit people read/write capability names and provision only synthetic grants for local testing. Real staff assignment and intended legacy-role mapping remain a business decision (scope contract D07). An account's capability in one city must never authorize another city's search, record URL or mutation. Recheck disabled/revoked access on every action.

Existing account audit support needs an explicit event/subject extension for operational people. Its original subject validation targets preserved project/file records. Never record invented legacy authors; record the current web actor and scoped entity. Transactional row locking plus comparison of the exact originally loaded editable state can provide stale-write rejection without a new version column; the implementation must cover aggregate child edits consistently. Do not claim conflict protection from a timestamp that does not exist on the typed tables.

Durable draft/retry storage or detailed before/after change history would require a reviewed storage design if not supported by existing tables. Do not call imported snapshot provenance a complete web edit history. Read-only directory delivery is valid as an incremental P4.1 slice, but must remain marked partial if create/update or the related-row editors are absent.

Never update immutable preservation rows or rewrite raw source IDs to follow a web relationship. A legacy entity edit must retain its origins. Import/reconciliation acceptance and concurrent importer behavior remain separate from locally tested P4.1 writes; use synthetic fixtures until Phase 3 validates the restored data.

## Required verification

Test Arabic values and string numbers; leading-zero candidates and literal wildcard characters; duplicate acceptance without merges; two cities with equal legacy IDs; unauthorized direct record/child references; revoked capability; null/sentinel/unknown historical values; current and historical work spellings; safe empty search; stale edits; transaction rollback and duplicate-submit behavior for each enabled write. Clearly report any unimplemented workflow or untested database/browser path.
