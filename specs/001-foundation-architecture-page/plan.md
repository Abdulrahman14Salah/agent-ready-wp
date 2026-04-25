# Implementation Plan: Foundation and Architecture Page

**Branch**: `001-foundation-architecture-page` | **Date**: 2026-04-23 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-foundation-architecture-page/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver a single WordPress admin settings page under `Settings > Agent Ready`
that lets site administrators review the latest agent-readiness scan, configure
all four Phase 1 capabilities, understand compatibility limits, and view Phase
2 placeholders. The feature uses WordPress-native admin page, options, AJAX,
transient, and capability APIs, with a page-level save action for settings and a
separate scan action that refreshes the summary in place without implicitly
saving unsaved changes.

## Technical Context

**Language/Version**: PHP 8.0+ for plugin runtime, JavaScript for progressive admin-page enhancement  
**Primary Dependencies**: WordPress 6.0+ core admin APIs, Settings API/options handling, admin-ajax, transients, HTTP API, optional WooCommerce detection  
**Storage**: One `agent_ready_wp_settings` option array plus one `agent_ready_wp_scan_cache` transient keyed to the site scan result  
**Testing**: PHPUnit for unit logic, WordPress integration tests for page behavior and settings persistence, manual admin acceptance checks for in-page scan refresh and compatibility states  
**Target Platform**: WordPress 6.0+ single-site admin experience, PHP 8.0+, classic and block-theme sites, optional WooCommerce installs  
**Project Type**: WordPress plugin admin feature  
**Performance Goals**: No remote scan requests during normal page load; scan calls happen only on explicit admin action; page render uses cached scan data only; rewrite flushing remains outside normal admin saves except when API Catalog configuration changes  
**Constraints**: Must remain WordPress.org-review friendly, admin-only, no theme edits, no direct filesystem mutation, same-page scan refresh, page-level save for settings, separate save and scan actions, visible disabled controls for unavailable options, visible Phase 2 placeholders  
**Scale/Scope**: One settings page covering readiness summary, four Phase 1 configuration panels, compatibility warnings, and two Phase 2 placeholders; supports posts/pages, public CPT discovery, and WooCommerce-aware options where available

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

Post-design review: PASS. Phase 1 artifacts define WordPress-native page
registration, page-level settings persistence, separate AJAX scan refresh,
compatibility-state rendering, and verification coverage without violating the
constitution.

## Project Structure

### Documentation (this feature)

```text
specs/001-foundation-architecture-page/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── admin-page-contract.md
│   └── run-scan-response.json
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
│   ├── Ajax/
│   │   └── RunScanAction.php
│   ├── Notices/
│   │   └── CompatibilityNoticeRenderer.php
│   └── ViewModel/
│       └── SettingsPageViewModelFactory.php
├── Application/
│   ├── Settings/
│   │   ├── Defaults.php
│   │   ├── SettingsRepository.php
│   │   └── SettingsSanitizer.php
│   ├── Scan/
│   │   ├── ScanClient.php
│   │   ├── ScanCache.php
│   │   └── ScanSummaryMapper.php
│   └── Compatibility/
│       └── EnvironmentDetector.php
├── Domain/
│   ├── Settings/
│   ├── Scan/
│   └── Compatibility/
├── Infrastructure/
│   └── WordPress/
│       ├── Hooks.php
│       └── Http.php
├── Integrations/
│   └── WooCommerce/
│       └── WooCommerceDetector.php
└── Public/
    └── AdminPagePlaceholders.php
assets/
├── css/
│   └── admin-settings.css
└── js/
    └── admin-settings.js
languages/
tests/
├── integration/
│   ├── Admin/
│   └── Ajax/
├── unit/
│   ├── Settings/
│   ├── Scan/
│   └── Compatibility/
└── fixtures/
```

**Structure Decision**: Use a WordPress plugin layout centered on an
admin-specific slice under `src/Admin/`, with application services handling
settings, scan caching, and compatibility detection. This keeps the page
feature isolated, supports WordPress integration tests, and avoids mixing admin
presentation logic with future runtime feature implementations.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No constitution violations identified for this feature.
