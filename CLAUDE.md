# CLAUDE.md — Zen Cart

This file configures Claude's behavior for interactive and agentic work in this codebase.

## Start here

Read these two files before doing anything else:
- `AGENTS.md` — architecture, bootstrapping, entry points, test/dev commands, plugin patterns
- `CONVENTIONS.md` — coding standards, naming rules, legacy exceptions, security conventions

## Git

Before making any changes, verify that the current branch is appropriate for the work being
requested. If it is not, ask before proceeding.

## Human review

Do not auto-commit. All changes are reviewed and committed manually by the developer.
Stage changes and summarize what was done; let the developer decide when to commit.

## Generating code

- Follow `CONVENTIONS.md` in full.
- When editing a legacy file listed in the "Accepted legacy exceptions" table, match the
  existing style of that file rather than reformatting it to PSR-12.
- Prefer targeted, minimal changes. Do not refactor code that is outside the scope of the
  current task.
- When adding new class files, include `declare(strict_types=1)`.

## Local developer preferences

Each developer may maintain a `CLAUDE.local.md` file (gitignored) for personal workflow
preferences. If that file is present, apply those preferences in addition to the above.
