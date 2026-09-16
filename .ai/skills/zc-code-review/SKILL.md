---
name: zc-code-review
description: Review a Zen Cart pull request, patch, diff, or changeset as a core maintainer, leading with bugs, security regressions, BC breaks, deployment risk and missing tests, and applying the project's legacy exceptions. Use when asked to review, audit, critique, or assess a PR, patch, diff, branch, or commit in this repository.
---

# Review as a Zen Cart maintainer

Review as a Zen Cart maintainer, not as a general PHP style checker. Lead with material findings: bugs, security regressions, backwards-compatibility breaks, deployment risks, and missing tests for changed behavior.

Use `CONVENTIONS.md` as the baseline:

- Favor stability over purity. Do not ask for broad rewrites, unrelated refactors, or legacy cleanup unless the change introduces a real defect.
- Apply PSR-12, naming, `declare(strict_types=1)`, and no-colon-syntax rules to new code and to files already being modified, while respecting the documented legacy exceptions.
- Flag direct edits to bootstrap/path files listed as "should never be directly edited"; prefer `extra_configures`, `init_includes`, plugin hooks, or other established extension points.
- Do not flag template files for outputting a closing form tag via PHP (`<?= '</form>' ?>`); it pairs with `zen_draw_form()` intentionally.

Security-sensitive changes:

- Preserve the early request-sanitizing behavior in `includes/application_top.php`.
- User-supplied output must go through `zen_output_string_protected()`.
- Avoid spreading direct `$_GET`, `$_POST`, `$_REQUEST` access where sanitized helpers exist.
- Admin fields: require the documented sanitization whitelist approach before relaxing sanitization. Read `.ai/rules/admin.md` before reporting an admin authorization, CSRF, or sanitization finding; those are enforced centrally.
- Check file/path handling, upload/download behavior, redirects, webhook/payment listeners, and plugin autoload paths for traversal or trust-boundary mistakes.

Data and compatibility:

- SQL uses table-name constants such as `TABLE_ORDERS`, never raw or prefixed names.
- Check query construction for unsafe interpolation, missing casting, and changed assumptions about the database layer.
- Keep compatibility with the supported runtime for this branch: PHP 8.3-8.5 and the supported MySQL/MariaDB versions.
- No production dependencies on Composer autoloading; Composer is for the test suite.

Plugins: verify against `.ai/rules/plugins.md` (layout, manifest, installer idempotency, gitignore allowlist).

Tests: see the review expectations in `.ai/rules/testing.md`; say when a useful test was not run or cannot be run.

Output: findings first, ordered by severity, with file/line references. Flag only material issues; no spelling, formatting, or style nits unless they cause a defect or violate a rule applied to new/touched code. Prefer minimal, deterministic fixes that fit the existing procedural, bootstrap, template, and plugin patterns. If nothing is found, say so and note remaining test gaps or assumptions.

If a gitignored `dev-REVIEW-CALIBRATION.md` exists at the repo root, read it for private calibration notes but never quote or commit it.
