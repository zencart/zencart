# `.ai/` — agent guidance, single source of truth

Everything an AI coding agent (Claude Code, OpenAI Codex, GitHub Copilot, or any tool that
reads `AGENTS.md`) needs beyond the always-loaded core lives here, split by topic so each
piece loads only when it is relevant.

| Directory | Contents | Loaded when |
|---|---|---|
| `.ai/rules/*.md` | Conventions for one area of the tree. Frontmatter `paths:` lists the globs the rule applies to. | A tool that supports path-scoped rules loads it when a matching file is read. Everything else reads it via the index in `AGENTS.md`. |
| `.ai/skills/<name>/SKILL.md` | Procedures and topic maps in the [Agent Skills](https://agentskills.io/specification) format. Frontmatter `description` says when to use it. | A tool that supports skills activates it from the description. Everything else reads it via the index in `AGENTS.md`. |

`AGENTS.md` (always loaded by every tool) carries the core guidance plus a generated index of
these files, so an agent whose tool has no auto-loading, or whose task does not touch a
matching path, still knows which file to open.

## Sync to tool-specific locations

Each tool discovers files only from its own fixed paths, so a generated stub is placed in
each of them. A stub is just the frontmatter the tool needs for discovery (`paths:` or
`applyTo:` for a rule, `name` and `description` for a skill) plus one line telling the agent to
read the source file here. No guide text is duplicated; when a rule fires or a skill activates
the agent makes one extra read. Stubs rather than symlinks because a Windows checkout without
symlink support would turn a link into a one-line text file and load that as the whole rule.

| Stub | Consumer | Generated from |
|---|---|---|
| `.claude/rules/<name>.md` | Claude Code (`paths:` frontmatter honored natively) | `.ai/rules/<name>.md` |
| `.github/instructions/<name>.instructions.md` | GitHub Copilot (`applyTo:` frontmatter) | `.ai/rules/<name>.md` |
| `.claude/skills/<name>/SKILL.md` | Claude Code, Copilot | `.ai/skills/<name>/SKILL.md` |
| `.agents/skills/<name>/SKILL.md` | Codex, Copilot | `.ai/skills/<name>/SKILL.md` |
| index block in `AGENTS.md` | every tool | both |

Zen Cart is installed in the webroot, so the sync also keeps a blank `index.html` in each of
these directories and a deny-all `.htaccess` at the top of `.ai/`, `.agents/` and `.claude/`,
matching `logs/` and `cache/`. The tools ignore both files. `.gitattributes` also marks these
directories `export-ignore` so release archives built with `git archive` omit them, as they
already do for `AGENTS.md`.

Edit the source under `.ai/`, then regenerate:

```
composer docs-sync
```

CI runs `composer docs-check`, which fails when a copy is out of date or when any doc
references a path, script, class, function or constant that no longer exists.

## Adding a rule

Create `.ai/rules/<topic>.md` with a `paths:` list of globs, then run `composer docs-sync`.
Keep it to conventions and traps for that area; procedures belong in a skill.

## Adding a skill

Create `.ai/skills/<name>/SKILL.md` with `name` (must equal the directory name, lowercase
and hyphens) and `description` (what it does and when to use it, with the keywords an agent
would see in a request). Keep `SKILL.md` short; put long reference material in sibling files
and link to them. Then run `composer docs-sync`.

Two kinds of skill are useful here:

- **Procedures**: create a plugin, convert a plugin, add a storefront page, review a PR.
- **Topic maps** for features that span several directories (checkout, product listing):
  the map of where the pieces live is the part an agent cannot cheaply derive from the tree.
