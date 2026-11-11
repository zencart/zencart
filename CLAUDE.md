# CLAUDE.md — Zen Cart

@AGENTS.md
@CONVENTIONS.md

## Claude Code specifics

- Before making any changes, verify that the current branch is appropriate for the work being
  requested. If it is not, ask before proceeding.
- Do not auto-commit. All changes are reviewed and committed manually by the developer.
  Stage changes and summarize what was done; let the developer decide when to commit.
- Follow `CONVENTIONS.md` in full. When editing a legacy file listed in its "Accepted legacy
  exceptions" table, match that file's existing style rather than reformatting it to PSR-12.
- Prefer targeted, minimal changes. Do not refactor code outside the scope of the current task.
- New class files include `declare(strict_types=1)`.
- `.claude/rules/` and `.claude/skills/` are generated stubs pointing at `.ai/rules/` and
  `.ai/skills/`. Edit the source under `.ai/` and run `composer docs-sync`; never edit the stubs.
- Each developer may keep a gitignored `CLAUDE.local.md` with personal workflow preferences.
  If present, apply it in addition to the above.
