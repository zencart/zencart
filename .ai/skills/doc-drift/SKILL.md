---
name: doc-drift
description: Find and fix claims in the agent/developer docs (AGENTS.md, CONVENTIONS.md, README.md, .ai/ rules and skills) that the code no longer backs up, using the doc-drift checker plus a semantic re-read. Use when asked to check, audit, or refresh the docs, after renaming or deleting files the docs mention, or when CI's docs-check job fails.
argument-hint: [git range or doc files]
---

# Doc drift

Check the agent/developer docs for claims the code no longer backs up, and fix them. `$ARGUMENTS` may name a git range (e.g. `origin/master...HEAD`) to focus on one change set, or specific doc files; with no arguments, check everything.

The docs are `AGENTS.md`, `CONVENTIONS.md`, `CLAUDE.md`, `README.md`, `CONTRIBUTING.md`, `.github/copilot-instructions.md`, and everything under `.ai/`. Every concrete statement in them (a path, a composer script, a class or function name, a constant, a config value, a version range, a count) is a fact about some other file, and nothing re-reads the doc when that file changes.

1. Mechanical pass first: `php not_for_release/dev_tools/doc-drift-check.php --stale` (add `--changed=<range>` when a range was given, or list the doc files). Read every ERROR and WARN, plus the INFO lines tagged `stale`, `changed`, or gitignored/runtime-generated.
2. For each ERROR, find out what replaced the thing rather than deleting the sentence: `git log -1 --diff-filter=D -- <path>` names the removing commit, `git log -S'<symbol>' --oneline` finds a rename, and the commit message usually states the new arrangement. Rewrite the claim to match the current code. The line exists because an agent needs that fact.
3. Semantic pass, which the script cannot do: for gitignored paths (`configure.php`, `local/configure.php`, `vendor/`), for `--stale` hits, and for any paragraph near a fixed line, re-read the claim against the code it describes. Behavioral claims ("admin has its own configure.php", "X is only initialized in Y", dependency lists, exact counts, version ranges) go stale without any path disappearing.
4. When rewriting, avoid volatile facts: prefer "a few dozen files" to an exact count, name the composer script instead of paraphrasing it, and point at the mechanism (`admin/includes/javascript_loader.php`) rather than restating its behavior.
5. A doc line that is legitimately an illustration the checker cannot resolve can carry an HTML comment containing `doc-drift:ignore`; use that sparingly and never to silence a real mismatch.
6. If you edited anything under `.ai/`, run `composer docs-sync` so the tool-specific copies and the index in `AGENTS.md` are regenerated.
7. Re-run `composer docs-check` until it exits 0. Report what was stale, which commit made it stale, and what you changed. Do not stage or commit.
