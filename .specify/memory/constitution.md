<!--
Sync Impact Report
- Version change: 0.0.0 -> 1.0.0
- Modified principles:
  - Template Principle 1 -> I. WordPress-Native Architecture
  - Template Principle 2 -> II. Security, Privacy, and Review Compliance
  - Template Principle 3 -> III. Stable Contracts and Graceful Degradation
  - Template Principle 4 -> IV. Testable Integration Boundaries
  - Template Principle 5 -> V. Operational Simplicity and Performance
- Added sections:
  - Delivery Standards
  - Implementation Workflow and Quality Gates
- Removed sections:
  - None
- Templates requiring updates:
  - ✅ .specify/templates/plan-template.md
  - ✅ .specify/templates/spec-template.md
  - ✅ .specify/templates/tasks-template.md
  - ✅ .specify/templates/checklist-template.md
  - ✅ .specify/templates/agent-file-template.md
  - ⚠ pending: .specify/templates/commands/ (directory not present in this repository)
- Follow-up TODOs:
  - None
-->
# Agent Ready WP Constitution

## Core Principles

### I. WordPress-Native Architecture
All implementation MUST use WordPress-native extension points and APIs before
introducing custom infrastructure. Features MUST be delivered through core hooks,
filters, rewrite rules, options, transients, REST APIs, enqueue APIs, and
activation/deactivation/uninstall hooks as appropriate. The plugin MUST NOT
require theme edits, core patches, or direct mutation of server files such as
physical `robots.txt` or `.well-known/*` files to deliver MVP behavior. New
custom database tables, bespoke frameworks, or heavy third-party dependencies
require explicit justification in plan complexity tracking because the project
goal is WordPress.org-friendly, portable plugin behavior.

### II. Security, Privacy, and Review Compliance
Every admin action, AJAX handler, setting save, and external request MUST meet
WordPress plugin review expectations. Inputs MUST be sanitized and validated at
ingress, outputs MUST be escaped for their render context, privileged actions
MUST enforce capability checks and nonces, and external HTTP calls MUST use the
WordPress HTTP API with explicit timeouts and failure handling. All user-facing
strings MUST be localizable with the `agent-ready-wp` text domain, and all
plugin-owned globals, functions, classes, options, and hooks MUST use a stable
`arwp_` or `ARWP_` prefix to avoid collisions.

### III. Stable Contracts and Graceful Degradation
User-visible behavior MUST remain predictable across feature toggles, missing
dependencies, and partial host compatibility. Each feature MUST have an explicit
enable/disable setting, preserve default WordPress behavior when disabled, and
fail soft when prerequisites are unavailable such as WooCommerce being inactive,
a physical `robots.txt` file existing, remote scan APIs being unreachable, or a
rewrite conflict blocking a `.well-known` endpoint. Option schema changes MUST
be migration-safe, and public HTTP contracts such as headers, response types,
query vars, and endpoint shapes MUST be treated as backward-compatible
interfaces once released.

### IV. Testable Integration Boundaries
Changes to hooks, rewrite endpoints, REST responses, admin workflows, option
migrations, and compatibility branches MUST ship with automated verification.
Pure transformation logic MUST have focused unit coverage, while public
integration boundaries MUST have integration or acceptance coverage proving the
feature works inside a WordPress execution path. Tests MUST specifically cover
security gates, fallback behavior, and compatibility branches for standard
posts/pages, public custom post types, and WooCommerce-aware behavior where the
plan claims support.

### V. Operational Simplicity and Performance
The plugin MUST minimize overhead on normal page requests and avoid surprising
site operators. Remote scans MUST be cached with transients rather than called
on every load, rewrite rules MUST be flushed only on lifecycle events, and
feature code MUST short-circuit quickly when disabled or not applicable. Assets
MUST be loaded through WordPress enqueue mechanisms, with inline JavaScript used
only when a browser API integration makes it necessary and after a registered
script handle exists. Any feature that can affect caching or content negotiation
MUST emit the required headers and document host-specific caveats.

## Delivery Standards

- Supported baseline is WordPress 6.0+ and PHP 8.0+ unless the constitution is
  amended with a new compatibility policy.
- The canonical storage model for MVP is one namespaced options array plus
  transients for cached external data; new persistence layers require explicit
  justification.
- The plugin structure documented in plans MUST reflect a real WordPress plugin
  layout with a bootstrap file, uninstall path, feature modules, tests, assets,
  and localization resources where applicable.
- Settings UIs MUST be admin-only, capability-gated, and built so that feature
  toggles map directly to runtime behavior without hidden side effects.
- Compatibility claims for WooCommerce, public custom post types, caching
  plugins, and `.well-known` routing MUST be documented in the plan and verified
  before release.

## Implementation Workflow and Quality Gates

Every feature spec, plan, and task list MUST demonstrate compliance with this
constitution before implementation begins. Plans MUST document the hook/filter
map, public endpoints, option schema, compatibility assumptions, and failure
paths. Tasks MUST include work for security hardening, uninstall behavior,
internationalization, and automated verification in addition to feature code.

Before a feature is considered complete, reviewers MUST confirm that:

- plugin behavior is implemented with WordPress-native APIs rather than
  filesystem or theme modifications;
- sanitization, escaping, capability checks, and nonces cover every privileged
  path;
- rewrite rules, headers, and response contracts are tested and documented;
- uninstall, deactivation, and migration behavior preserve site stability; and
- WordPress.org review constraints and stated compatibility scenarios remain
  satisfied.

## Governance

This constitution supersedes conflicting local practices for planning and
implementation in this repository. Amendments MUST update this document and any
affected templates in `.specify/templates/` within the same change. Versioning
follows semantic versioning for governance: MAJOR for incompatible principle
changes or removals, MINOR for new principles or materially expanded
requirements, and PATCH for clarifications that do not change intent.

Compliance review is mandatory at plan time, task generation time, and before
release. Any approved exception MUST be recorded in the relevant plan under
complexity tracking with the simpler alternative that was rejected and the
operational reason it was insufficient.

**Version**: 1.0.0 | **Ratified**: 2026-04-23 | **Last Amended**: 2026-04-23
