# Implementation Plan: Phase 2 Foundation and Architecture Page

**Branch**: `002-phase2-foundation-page` | **Date**: 2026-04-23 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-phase2-foundation-page/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Extend the existing `Settings > Agent Ready` admin page so administrators can
configure Phase 2 discovery metadata on the same page as Phase 1 settings. The
page will add editable MCP Server Card settings, an explicit protected-API
applicability toggle, grouped OAuth and protected-resource settings, draft
previews, and in-place validation feedback, while preserving the existing
single save flow and disabled-with-explanation behavior for non-applicable
sections.

## Technical Context

**Language/Version**: PHP 8.0+ for plugin runtime, JavaScript for progressive admin-page enhancement  
**Primary Dependencies**: WordPress 6.0+ core admin APIs, Settings API/options handling, existing Agent Ready page classes, admin notices, optional WooCommerce detection only for shared page continuity  
**Storage**: Existing `agent_ready_wp_settings` option array extended with Phase 2 keys for protected API applicability, MCP Server Card settings, OAuth discovery settings, and protected-resource settings  
**Testing**: PHPUnit-style unit tests, lightweight integration tests against the existing page view model and settings save flow, manual admin acceptance checks for shared save behavior and disabled Phase 2 states  
**Target Platform**: WordPress 6.0+ single-site admin experience, PHP 8.0+, extension of the already implemented Agent Ready settings page  
**Project Type**: WordPress plugin admin feature extension  
**Performance Goals**: No additional remote calls during page load or save; Phase 2 previews are generated from local settings only; existing page render remains server-first with bounded admin-page enhancement  
**Constraints**: Must extend the existing page instead of creating a second admin workflow; must preserve one shared page-level save action; must reject invalid Phase 2 saves in place and preserve entered values; must keep non-applicable OAuth/protected-resource sections visible but disabled with explanation; must remain WordPress.org-review friendly and avoid direct filesystem mutation  
**Scale/Scope**: One existing settings page gains new Phase 2 panels for MCP Server Card, OAuth discovery, and protected-resource configuration, plus preview and validation behavior; underlying well-known endpoint publication is out of scope for this feature plan

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

Post-design review: PASS. The Phase 2 design extends the existing WordPress
admin page, uses the same options-driven save flow, adds no new remote page-load
work, and keeps validation, capability gating, and graceful degradation inside
WordPress-native page infrastructure.

## Project Structure

### Documentation (this feature)

```text
specs/002-phase2-foundation-page/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── phase2-admin-page-contract.md
│   └── phase2-preview-schema.json
└── tasks.md
```

### Source Code (repository root)

```text
agent-ready-wp.php
uninstall.php
readme.txt
src/
├── Admin/
│   ├── Page/
│   │   └── SettingsPage.php
│   ├── Assets/
│   │   └── SettingsPageAssets.php
│   ├── Notices/
│   │   └── CompatibilityNoticeRenderer.php
│   └── ViewModel/
│       └── SettingsPageViewModelFactory.php
├── Application/
│   ├── Settings/
│   │   ├── Defaults.php
│   │   ├── SettingsRepository.php
│   │   └── SettingsSanitizer.php
│   └── Compatibility/
│       └── EnvironmentDetector.php
├── Infrastructure/
│   └── WordPress/
│       └── Hooks.php
└── Public/
    └── AdminPagePlaceholders.php
assets/
├── css/
│   └── admin-settings.css
└── js/
    └── admin-settings.js
tests/
├── integration/
│   ├── Admin/
│   └── Ajax/
└── unit/
    ├── Settings/
    └── Compatibility/
```

**Structure Decision**: Reuse the existing admin-page architecture and extend
the current settings repository, sanitizer, view-model factory, and page
renderer rather than introducing a new page controller. This keeps the Phase 2
feature aligned with the implemented Phase 1 page and minimizes duplicated admin
infrastructure.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No constitution violations identified for this feature.
