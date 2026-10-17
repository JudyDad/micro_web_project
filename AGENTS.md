# Project delivery instructions

- This is the canonical Git checkout for Micro Projects web development. Remote: https://github.com/JudyDad/micro_web_project.git.
- The user explicitly requires commits and pushes for every task and phase worked on. Commit coherent task changes with clear messages and push the current branch after verification. Record incomplete work truthfully; do not claim phase acceptance for drafts. Do not force-push or overwrite unrelated changes.
- Follow `docs/planning/team-execution-plan.md` and the newer decisions in `docs/planning/p0-4-scope-contract.md`. Phase 0 passed static discovery review. The user explicitly approved the Phase 1 preservation schema on 17 October 2026; see `docs/planning/phase-1/schema-approval.md`. Implement that approved scope without asking again. Operational schema definitions absent from that design remain open work.
- Keep the desktop source at `D:\MyProjects\MicroProject newTest` read-only. Never commit source backups, business rows, credentials, local environment secrets, private attachments or database dumps.
- Build locally. At project completion, before deployment, ask the user for hosting/provider/runtime capabilities and internet versus office/VPN access. This deferred question must not block local development.
- Build online operation with draft recovery, not automatic committed offline transactions. Reuse desktop credentials where technically compatible; retain source-qualified historical identities and city permissions.
- Maintain `docs/delivery/task-board.md`. Planning artifacts were copied from `D:\MyProjects\MicroProjects-Web-Plan`; update this checkout's copies for future work to keep commits authoritative.
