# Zen Boost Documentation Ingestion Plan

## Implementation Form

`zen-boost` should be implemented as an encapsulated Zen Cart plugin under:

- `zc_plugins/zen-boost/v1.0.0/`

The plugin should own the Zen Cart-specific integration points, cached documentation snapshots, generated JSON catalogs, diagnostics, and developer-facing tools.

The primary command surface should use the encapsulated-plugin console command framework being developed on the `feature/console-command-framework` branch. Zen Boost should not introduce a separate bespoke command dispatcher unless that framework needs a thin bridge entrypoint.

An optional MCP server can be exposed from the same plugin for local development use in editors such as VS Code. That MCP server should read from the plugin's flat-file docs and code catalogs instead of depending on separate infrastructure.

## Goal

Create a docs-aware developer tooling layer for Zen Cart that can use official documentation from `docs.zen-cart.com` together with local repository code to answer questions, generate scaffolds, and validate plugin implementations, without requiring extra infrastructure such as SQLite, Redis, or a vector store.

## Core Direction

The system should treat Zen Cart's official documentation as the primary source for public guidance and conventions, while treating the local codebase as the primary source for actual runtime behavior.

When documentation and code differ, the tooling should surface the mismatch instead of silently picking one.

## Initial Scope

Start by ingesting a small set of high-value developer documentation sections:

- `https://docs.zen-cart.com/dev/`
- `https://docs.zen-cart.com/dev/plugins/`
- `https://docs.zen-cart.com/dev/plugins/encapsulated_plugins/`
- `https://docs.zen-cart.com/dev/plugins/encapsulated_plugins/manifests/`
- `https://docs.zen-cart.com/dev/schema/`

These sections are enough to support plugin development, manifest validation, and developer guidance for an initial MVP.

## Architecture

### Plugin Layout

The first implementation should live inside a plugin structure similar to:

- `zc_plugins/zen-boost/v1.0.0/manifest.php`
- `zc_plugins/zen-boost/v1.0.0/Installer/ScriptedInstaller.php`
- `zc_plugins/zen-boost/v1.0.0/catalog/includes/classes/`
- `zc_plugins/zen-boost/v1.0.0/admin/includes/classes/`
- `zc_plugins/zen-boost/v1.0.0/admin/`
- plugin-local console command classes using the encapsulated-plugin console command convention
- `zc_plugins/zen-boost/v1.0.0/resources/docs-cache/`
- `zc_plugins/zen-boost/v1.0.0/resources/catalogs/`
- optional `zc_plugins/zen-boost/v1.0.0/bin/` entrypoints only where needed for MCP or framework bridging

Suggested responsibilities:

- `resources/docs-cache/` stores fetched documentation snapshots
- `resources/catalogs/` stores generated JSON chunk and repo catalogs
- `admin/` exposes diagnostics or developer tools pages
- plugin console commands expose catalog rebuild, search, comparison, and plugin diagnostic actions
- `bin/` is reserved for a future MCP entrypoint or a small bridge script if the console framework requires one

### Console Command Integration

Zen Boost should be an early real-world consumer of the encapsulated-plugin console command framework.

The exact directory names and registration metadata should follow the conventions from `feature/console-command-framework`, but the intended command shape is:

- docs fetch command
- docs index command
- repository index command
- docs/repo search command
- docs/code comparison command
- plugin doctor command
- plugin scaffold command, later

The command classes should be thin adapters over framework-neutral services. The services should contain the actual fetch, parse, index, search, and inspect logic so they can be unit tested without booting the console runtime.

Example responsibility split:

- console command class: parse input, call service, render output, return exit code
- service class: perform fetch/index/search/inspection behavior
- storage class: read/write JSON catalogs and raw page snapshots

This keeps Zen Boost aligned with core console-command work and avoids a second plugin-specific CLI mechanism.

### 1. Documentation Ingestion

Build a crawler or fetch pipeline that stores:

- page URL
- page title
- heading structure
- normalized body text
- topic tags
- fetch date
- last-modified date when available

The fetch layer should cache raw page content locally as flat files so repeated indexing runs do not need to re-download unchanged pages.

### 2. Documentation Chunking

Chunk documentation by heading section instead of fixed-size token windows.

Each chunk should retain:

- source URL
- heading path
- short excerpt
- topic tags such as `plugin`, `manifest`, `installer`, `schema`, `language-files`
- version hints such as `1.5.8`, `2.2`, or `3.0` when detected

This should make retrieval more accurate for procedural docs while keeping the data simple enough to search from flat files.

### 3. Repository Indexing

Build a lightweight searchable catalog from the local repository, with priority on:

- `zc_plugins/*/*/manifest.php`
- plugin installer classes
- `includes/application_top.php`
- `includes/classes/`
- `includes/init_includes/`
- local Markdown plans and developer docs under `docs/`

This catalog should extract:

- file path
- class names
- function names
- selected comments or docblocks
- neighboring code snippets for retrieval context

The catalog can be written as JSON files generated into a local cache directory inside the project or plugin.

### 4. Retrieval Layer

For a given developer question:

1. classify the query as docs-only, code-only, or mixed
2. retrieve relevant documentation chunks
3. retrieve relevant local code or repo docs
4. merge the evidence into one grounded result

The retrieval layer should prefer:

- official docs for intended conventions
- local code for actual implementation details

### 5. Answer Layer

Responses produced by the tooling should include three parts:

- documented approach
- current repo behavior
- mismatch or confidence note

This is especially important for areas where docs may lag the codebase.

## Storage Strategy

For the MVP, use a zero-extra-infrastructure local-first design:

- cached raw pages as local files
- chunk records stored as JSON
- repository symbol and snippet records stored as JSON
- plain text, keyword, and heading-aware ranking

Do not require SQLite, Redis, embeddings, or a hosted search service for the first version.

If search quality later proves insufficient, a hosted documentation API can be considered as a second-phase enhancement.

## Console Surface

The first useful interface should be console commands contributed by the `zen-boost` encapsulated plugin.

Suggested commands:

- `docs:search <term>`
- `docs:ask "<question>"`
- `docs:compare "<question>"`
- `plugin:doctor <plugin>`
- `make:plugin <name>`

Examples of intended use:

- search manifest fields and requirements
- explain how plugin installers are discovered
- compare manifest docs to a local plugin manifest
- diagnose why a plugin is not loading

The first version of `docs:search` should use:

- exact term matching
- heading matches
- tag matches
- simple weighted keyword ranking

This should be enough for Zen Cart's documentation size without introducing semantic search infrastructure.

The exact command namespace should be chosen to fit the console framework's conventions. If the framework prefers plugin-prefixed names, the commands could become:

- `zen-boost:docs:search <term>`
- `zen-boost:docs:ask "<question>"`
- `zen-boost:docs:compare "<question>"`
- `zen-boost:plugin:doctor <plugin>`
- `zen-boost:make:plugin <name>`

The important constraint is that command registration should happen through the encapsulated-plugin console command framework, not through a standalone Zen Boost router.

## MCP Option

The plugin can optionally expose an MCP server for local development.

That MCP server should:

- run from the local project
- read the plugin's docs snapshots and JSON catalogs
- search the local repository in addition to docs snapshots
- expose Zen Cart-specific tools to editors or agents

Likely first MCP tools:

- `search_docs`
- `search_repo`
- `compare_docs_to_code`
- `inspect_plugin_manifest`
- `inspect_plugin_installer`

This keeps the editor integration lightweight. The editor talks to the local MCP server, and the MCP server talks only to local files plus the current Zen Cart codebase.

## Guardrails

- Prefer official Zen Cart docs over forum discussions for baseline guidance.
- Prefer local repository code over docs when runtime behavior disagrees with the docs.
- Always keep source URLs attached to indexed documentation records.
- Store fetch dates so the snapshot's freshness is visible.
- Reindex incrementally where possible instead of fully crawling every run.
- Keep the search implementation understandable and file-based for the first release.

## MVP Implementation Order

1. Review and adopt the encapsulated-plugin console command conventions from `feature/console-command-framework`.
2. Scaffold `zc_plugins/zen-boost/v1.0.0/` as an encapsulated plugin with console-command registration metadata.
3. Implement the service layer independently of the command runtime.
4. Implement `plugin:doctor` first because it can run entirely from local files and validates the plugin command integration without network access.
5. Build a fetcher for the selected `docs.zen-cart.com` sections.
6. Store raw page snapshots and metadata locally.
7. Chunk the docs by heading and tag the chunks.
8. Write chunk and repo catalogs as local JSON files.
9. Implement a combined retrieval command for docs plus code using keyword and heading-aware search.
10. Add a simple comparison mode that reports docs/code mismatches.
11. Add developer-facing commands such as `make:plugin`.
12. Add an optional local MCP server entrypoint backed by the same flat-file catalogs.

## Best First Deliverable

The best first deliverable is a plugin-provided console command that can inspect a local plugin and report manifest/installer/convention issues.

The initial target should be:

- `plugin:doctor <plugin>`

or the framework-preferred equivalent, for example:

- `zen-boost:plugin:doctor <plugin>`

This command can be implemented without network access and should prove:

- Zen Boost can register commands from an encapsulated plugin
- command classes can call plugin-local services
- services can inspect local plugin manifests and installers
- output can distinguish hard errors from warnings

After that, add documentation indexing and search commands that answer questions such as:

- what fields belong in a plugin manifest
- how plugin classes are loaded
- how an installer should be structured
- whether a local plugin matches the documented conventions

That would prove the value of documentation ingestion before investing in a larger assistant, IDE integration layer, or hosted search service.

After that CLI is stable, the same search and comparison services can be reused behind an MCP server without changing the storage model.

## What Not To Do

- Do not try to ingest the entire Zen Cart ecosystem at once.
- Do not rely on documentation alone when local code can be inspected.
- Do not hide docs/code disagreements from the user.
- Do not introduce a large infrastructure dependency for the first version.
- Do not over-engineer search before validating that basic keyword retrieval is insufficient.
- Do not create a separate Zen Boost command router if the encapsulated-plugin console command framework can provide the command surface.

## Summary

The best approach is a dual-source documentation system:

- ingest official `docs.zen-cart.com` pages for conventions and guidance
- write flat-file JSON catalogs for docs and local code
- retrieve from both and present the result together

This would give a future `zen-boost` toolkit a reliable foundation for search, scaffolding, diagnostics, and developer assistance.
