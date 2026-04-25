# Implementation Plan: Phase 1 Runtime Foundation

**Branch**: `003-phase1-runtime-foundation` | **Date**: 2026-04-24 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/003-phase1-runtime-foundation/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver runtime-only Phase 1 behavior for Markdown Negotiation (F2) and
Content Signals (F3) using WordPress-native request hooks. The implementation
will serve markdown only for qualified requests and supported content while
preserving normal HTML behavior for all other cases, and will emit one canonical
`Content-Signal` directive in generated `robots.txt` output when configured.
The markdown MVP remains limited to eligible frontend document requests for
supported singular WordPress content, preserves UTF-8/Arabic text fidelity, and
falls back to normal handling for excluded request classes such as admin,
system, auth, feed, sitemap, and asset paths. The plan excludes admin-page
behavior already covered in `001`/`002` and excludes F4–F8 runtime endpoints.

## Technical Context

**Language/Version**: PHP 8.0+ for plugin runtime behavior  
**Primary Dependencies**: WordPress 6.0+ hooks/filters (`template_redirect`, `robots_txt`), existing settings repository and compatibility detector, optional WooCommerce detection for product markdown scope  
**Storage**: Existing `agent_ready_wp_settings` option array only; no new persistent store for this phase  
**Testing**: PHPUnit unit tests for negotiation and directive shaping; WordPress integration tests for runtime request contracts and fallback behavior; manual curl-based acceptance checks for headers and robots output  
**Target Platform**: WordPress 6.0+ single-site installs on PHP 8.0+, classic and block-theme frontends, optional WooCommerce  
**Project Type**: WordPress plugin runtime extension  
**Performance Goals**: No additional remote requests on frontend runtime paths; runtime handlers short-circuit when feature disabled or request non-qualifying; markdown conversion limited to qualifying singular frontend document requests only  
**Constraints**: Must preserve default WordPress behavior when features are disabled or request/context is unsupported; must honor content visibility/access controls; must not mutate physical `robots.txt`; must output `Vary: Accept` on markdown responses; must preserve UTF-8/Arabic content fidelity; must exclude `wp-admin`, REST, AJAX, cron, feeds, sitemaps, login/register, and asset requests from markdown negotiation in the MVP; scope limited to F2/F3 only  
**Scale/Scope**: Runtime coverage for posts/pages/public CPTs plus optional WooCommerce product content in markdown negotiation for eligible singular frontend document requests, and generated robots content-signal output from configured tri-state values

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Uses WordPress-native hooks, filters, rewrite rules, enqueue APIs, options,
      and lifecycle hooks instead of theme edits, core patches, or direct
      mutation of physical server files.
- [x] Defines sanitization, escaping, capability checks, and nonce coverage for
      every admin, AJAX, settings, and external-request path.
- [x] Maps each public contract that changes behavior, such as headers, rewrite
      endpoints, robots output, WebMCP exposure, REST/AJAX responses, or option
      migrations, to automated tests or explicit acceptance coverage.
- [x] Documents fallback behavior for missing prerequisites and conflicts,
      including WooCommerce absence, physical `robots.txt`, `.well-known`
      conflicts, disabled hooks, and remote API failures.
- [x] Shows that performance-sensitive behavior short-circuits when disabled,
      avoids uncached remote work on normal requests, and flushes rewrite rules
      only on lifecycle events.

Post-design review: PASS. Planned artifacts define hook-scoped runtime behavior,
explicit fallback rules, and testable response contracts for both markdown and
content-signal output without introducing non-native infrastructure or violating
WordPress review constraints.

## Project Structure

### Documentation (this feature)

```text
specs/003-phase1-runtime-foundation/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── markdown-negotiation-contract.md
│   └── content-signals-contract.md
└── tasks.md
```

### Source Code (repository root)

```text
agent-ready-wp.php
uninstall.php
readme.txt
src/
├── Application/
│   ├── Runtime/
│   │   ├── Markdown/
│   │   └── ContentSignals/
│   ├── Settings/
│   └── Compatibility/
├── Infrastructure/
│   └── WordPress/
│       └── Hooks.php
├── Integrations/
│   └── WooCommerce/
└── Public/
    └── Runtime/
assets/
tests/
├── integration/
│   └── Runtime/
├── unit/
│   └── Runtime/
└── fixtures/
```

**Structure Decision**: Implement F2/F3 as runtime modules under
`src/Application/Runtime/` with lightweight orchestration from
`src/Infrastructure/WordPress/Hooks.php`, and keep admin-facing configuration
reuse in the existing settings layer. This preserves the current architecture,
isolates runtime contracts for unit/integration testing, and avoids coupling to
admin rendering code.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No constitution violations identified for this feature.
