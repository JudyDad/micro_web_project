# Phase 2 runtime database permissions

Use a separate migration/provisioning account and runtime account. Keep credentials outside Git. The revised runtime needs SELECT on the synthetic application database, INSERT on account_audit_events, and column-only UPDATE on web_accounts.password_hash/updated_at for successful password upgrades. It must not receive DDL, business writes, principal provisioning, grant/publication changes or audit UPDATE/DELETE. Legacy audit_events and web_principals are read-only for this runtime.

Example for a dedicated local database; substitute the host and generated secret privately:

```sql
CREATE USER 'micro_projects_web_local'@'127.0.0.1' IDENTIFIED BY '<private-generated-secret>';
GRANT SELECT ON micro_projects_local.* TO 'micro_projects_web_local'@'127.0.0.1';
GRANT INSERT ON micro_projects_local.account_audit_events TO 'micro_projects_web_local'@'127.0.0.1';
GRANT UPDATE (password_hash, updated_at) ON micro_projects_local.web_accounts TO 'micro_projects_web_local'@'127.0.0.1';
```

For an existing Phase 2 runtime identity, revoke its previous INSERT on audit_events and UPDATE(password_hash,updated_at) on web_principals after upgrading. Grant the revised permissions shown above. Provision the synthetic fixture with the migration account before switching the local .env to the runtime account. The application fails rather than silently discarding audit errors. RuntimeAuditPrivilegesTest verifies first-login credential bridging and password upgrade under restricted privileges, audit inserts, and denied audit mutation/business writes/account disabling/grant changes/publication changes. Reviewed credential/link rows use shared locks with SELECT-only access; the global account row uses an exclusive lock for its permitted password update. Administrators remain privileged and require protected operational controls/backups.

Only the synthetic `projects.view` grant is provisioned in Phase 2. Real staff grants require D07 decisions and the controlled AccountProvisioner service with reviewed principal IDs, city capabilities and a decision locator. Account linking never assigns access implicitly. Global disabled_at and explicit city capabilities are authoritative after transition. Modern account login is independent of source publication; source data access still requires an explicit permitted publication. Before the first modern upgrade, the selected credential bridge must be reviewed, unrevoked and active in its original publication. Snapshot replacement never silently relinks historical identities.
