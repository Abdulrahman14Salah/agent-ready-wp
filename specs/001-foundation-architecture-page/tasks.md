# Tasks: Foundation and Architecture Page

**Input**: Design documents from `/specs/001-foundation-architecture-page/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for public integration boundaries,
security-sensitive behavior, option migrations, and pure transformation logic.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **WordPress plugin**: `agent-ready-wp.php`, `uninstall.php`, `src/`, `assets/`,
  `languages/`, `tests/` at repository root
- Organize runtime code by responsibility such as `src/Admin/`,
  `src/Application/`, `src/Domain/`, `src/Infrastructure/`,
  `src/Integrations/`, and `src/Public/`
- Put integration tests under `tests/integration/`, pure logic tests under
  `tests/unit/`, and reusable WordPress fixtures/bootstrap helpers under
  `tests/fixtures/` or `tests/bootstrap/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the plugin skeleton, admin assets, and test scaffolding that all later work depends on.

- [X] T001 Create the planned directories `src/Admin/Page/`, `src/Admin/Assets/`, `src/Admin/Ajax/`, `src/Admin/Notices/`, `src/Admin/ViewModel/`, `src/Application/Settings/`, `src/Application/Scan/`, `src/Application/Compatibility/`, `src/Infrastructure/WordPress/`, `src/Integrations/WooCommerce/`, `src/Public/`, `assets/js/`, `assets/css/`, `tests/integration/Admin/`, `tests/integration/Ajax/`, `tests/unit/Settings/`, `tests/unit/Scan/`, `tests/unit/Compatibility/`, and `tests/fixtures/`
- [X] T002 Create the plugin bootstrap and lifecycle files in `agent-ready-wp.php` and `uninstall.php`
- [X] T003 [P] Create empty admin asset entry files in `assets/js/admin-settings.js` and `assets/css/admin-settings.css`
- [X] T004 [P] Create test bootstrap and fixture files in `tests/bootstrap.php`, `tests/fixtures/scan-response-success.json`, and `tests/fixtures/scan-response-failure.json`
- [X] T005 [P] Create initial admin feature class stubs in `src/Admin/Page/SettingsPage.php`, `src/Admin/Assets/SettingsPageAssets.php`, and `src/Admin/Ajax/RunScanAction.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the shared settings, compatibility, scan, and hook infrastructure required by all user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [X] T006 Create default settings definitions in `src/Application/Settings/Defaults.php`
- [X] T007 Create option read/write access and normalization logic in `src/Application/Settings/SettingsRepository.php`
- [X] T008 Create settings sanitization and validation rules in `src/Application/Settings/SettingsSanitizer.php`
- [X] T009 [P] Create WooCommerce detection logic in `src/Integrations/WooCommerce/WooCommerceDetector.php`
- [X] T010 [P] Create environment and compatibility detection logic in `src/Application/Compatibility/EnvironmentDetector.php`
- [X] T011 [P] Create scan cache access logic in `src/Application/Scan/ScanCache.php`
- [X] T012 [P] Create remote scan client logic in `src/Application/Scan/ScanClient.php`
- [X] T013 [P] Create scan payload-to-summary mapping logic in `src/Application/Scan/ScanSummaryMapper.php`
- [X] T014 Create shared hook registration and bootstrap wiring in `src/Infrastructure/WordPress/Hooks.php` and `agent-ready-wp.php`
- [X] T015 Create shared admin notice rendering support in `src/Admin/Notices/CompatibilityNoticeRenderer.php`
- [X] T016 Create the page view-model factory for all page sections in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T017 Add foundational unit coverage for defaults, sanitizer, scan mapping, and compatibility detection in `tests/unit/Settings/DefaultsTest.php`, `tests/unit/Settings/SettingsSanitizerTest.php`, `tests/unit/Scan/ScanSummaryMapperTest.php`, and `tests/unit/Compatibility/EnvironmentDetectorTest.php`
- [X] T018 Add foundational integration coverage for option persistence and bootstrap registration in `tests/integration/Admin/SettingsRepositoryTest.php` and `tests/integration/Admin/PluginBootstrapTest.php`

**Checkpoint**: Foundation ready. User story work can now begin in parallel.

---

## Phase 3: User Story 1 - Review Site Readiness (Priority: P1) 🎯 MVP

**Goal**: Give administrators one page where they can see the current readiness summary, run a scan, and see refreshed results without a manual reload.

**Independent Test**: Open `Settings > Agent Ready` with no cached scan and with a cached scan; confirm the summary state changes correctly, the scan action works, and the summary refreshes in place without saving unsaved settings.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T019 [P] [US1] Add integration test for admin page registration and authorized access in `tests/integration/Admin/SettingsPageAccessTest.php`
- [X] T020 [P] [US1] Add integration test for empty-state and cached readiness summary rendering in `tests/integration/Admin/ReadinessSummaryRenderTest.php`
- [X] T021 [P] [US1] Add integration test for scan AJAX success and failure responses in `tests/integration/Ajax/RunScanActionTest.php`
- [X] T022 [P] [US1] Add unit test for in-page summary view-model shaping in `tests/unit/Scan/ReadinessSummaryViewModelTest.php`

### Implementation for User Story 1

- [X] T023 [P] [US1] Implement readiness summary and page-level state assembly in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T024 [US1] Implement admin page registration, route handling, and top-level page render in `src/Admin/Page/SettingsPage.php`
- [X] T025 [US1] Implement the remote scan AJAX action with nonce and capability checks in `src/Admin/Ajax/RunScanAction.php`
- [X] T026 [US1] Implement HTTP request execution and failure handling for scans in `src/Application/Scan/ScanClient.php`
- [X] T027 [US1] Implement cached scan loading, empty state, and refresh-failure fallback behavior in `src/Application/Scan/ScanCache.php` and `src/Application/Scan/ScanSummaryMapper.php`
- [X] T028 [US1] Implement admin JavaScript for explicit scan execution and in-place summary refresh in `assets/js/admin-settings.js`
- [X] T029 [US1] Implement admin asset registration and localized scan-action data in `src/Admin/Assets/SettingsPageAssets.php`
- [X] T030 [US1] Add summary-panel styling and status-state styling in `assets/css/admin-settings.css`

**Checkpoint**: User Story 1 should now provide a functional admin page, readiness summary, and separate scan flow.

---

## Phase 4: User Story 2 - Configure Phase 1 Capabilities (Priority: P2)

**Goal**: Let administrators configure all four Phase 1 capability panels through one page-level save flow with persisted values and readable previews.

**Independent Test**: Change settings across multiple Phase 1 panels, save once, reload the page, and confirm values persist, previews update, and a later scan does not implicitly save unsaved changes.

### Tests for User Story 2 ⚠️

- [X] T031 [P] [US2] Add integration test for page-level save across multiple capability sections in `tests/integration/Admin/SettingsSaveFlowTest.php`
- [X] T032 [P] [US2] Add integration test for unsaved changes remaining separate from scan execution in `tests/integration/Admin/UnsavedChangesBehaviorTest.php`
- [X] T033 [P] [US2] Add unit test for settings sanitization and normalization across all four capability groups in `tests/unit/Settings/SettingsRepositoryRoundTripTest.php`
- [X] T034 [P] [US2] Add unit test for preview generation logic in `tests/unit/Settings/CapabilityPreviewTest.php`

### Implementation for User Story 2

- [X] T035 [P] [US2] Implement persisted capability panel state and panel-control view models in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T036 [US2] Implement page-level settings form fields and save wiring for Markdown and Content Signals in `src/Admin/Page/SettingsPage.php`
- [X] T037 [US2] Implement page-level settings form fields and save wiring for API Catalog and WebMCP in `src/Admin/Page/SettingsPage.php`
- [X] T038 [US2] Implement full option sanitization for Markdown and Content Signals fields in `src/Application/Settings/SettingsSanitizer.php`
- [X] T039 [US2] Implement full option sanitization for API Catalog custom entries and WebMCP tool toggles in `src/Application/Settings/SettingsSanitizer.php`
- [X] T040 [US2] Implement preview generation for content-signal lines, selected post types, API Catalog entries, and WebMCP tools in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T041 [US2] Implement page-level save feedback and validation-error display that keeps the administrator on the page in `src/Admin/Page/SettingsPage.php`
- [X] T042 [US2] Implement rewrite-flush-on-save behavior only for API Catalog changes in `src/Application/Settings/SettingsRepository.php` and `agent-ready-wp.php`

**Checkpoint**: User Stories 1 and 2 should now allow full page configuration and persistence for the Phase 1 feature set.

---

## Phase 5: User Story 3 - Understand Compatibility and Limitations (Priority: P3)

**Goal**: Show clear environment-aware disabled states, conflict notices, and visible Phase 2 placeholders without blocking unrelated settings.

**Independent Test**: Open the page with and without WooCommerce, with compatibility conflicts such as physical `robots.txt` or API Catalog file conflicts, and confirm disabled states, warnings, and Phase 2 placeholders render correctly.

### Tests for User Story 3 ⚠️

- [X] T043 [P] [US3] Add integration test for WooCommerce-dependent disabled states in `tests/integration/Admin/CompatibilityStateRenderTest.php`
- [X] T044 [P] [US3] Add integration test for physical `robots.txt` and `.well-known` conflict warnings in `tests/integration/Admin/CompatibilityWarningsTest.php`
- [X] T045 [P] [US3] Add integration test for visible non-interactive Phase 2 placeholders in `tests/integration/Admin/PhasePlaceholderRenderTest.php`
- [X] T046 [P] [US3] Add unit test for compatibility warning creation and panel mapping in `tests/unit/Compatibility/CompatibilityWarningFactoryTest.php`

### Implementation for User Story 3

- [X] T047 [P] [US3] Implement environment-aware compatibility state assembly in `src/Application/Compatibility/EnvironmentDetector.php`
- [X] T048 [US3] Implement disabled-with-explanation control behavior for unavailable optional capabilities in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T049 [US3] Implement compatibility warning rendering for `robots.txt`, `.well-known`, and dependency issues in `src/Admin/Notices/CompatibilityNoticeRenderer.php`
- [X] T050 [US3] Implement Phase 2 “coming soon” placeholder rendering in `src/Public/AdminPagePlaceholders.php` and `src/Admin/Page/SettingsPage.php`
- [X] T051 [US3] Implement WooCommerce and CPT-specific panel annotations in `src/Integrations/WooCommerce/WooCommerceDetector.php` and `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T052 [US3] Add styling for disabled controls, warning notices, and placeholders in `assets/css/admin-settings.css`

**Checkpoint**: All three user stories should now be independently functional and collectively satisfy the feature spec.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final hardening, accessibility, and verification work that spans all user stories.

- [X] T053 [P] Add i18n wrappers for all admin-page strings in `src/Admin/Page/SettingsPage.php`, `src/Admin/Notices/CompatibilityNoticeRenderer.php`, and `src/Public/AdminPagePlaceholders.php`
- [X] T054 Add security hardening review for nonces, capability checks, escaping, and remote error handling in `src/Admin/Ajax/RunScanAction.php`, `src/Admin/Page/SettingsPage.php`, and `src/Application/Scan/ScanClient.php`
- [X] T055 [P] Add regression coverage for same-page scan refresh, page-level save, and disabled-state rendering in `tests/integration/Admin/SettingsPageRegressionTest.php` and `tests/integration/Ajax/RunScanActionRegressionTest.php`
- [X] T056 Add accessibility and admin-UI polish for labels, status text, and disabled readability in `assets/css/admin-settings.css` and `src/Admin/Page/SettingsPage.php`
- [X] T057 Update plugin readme and usage notes for the new admin page in `readme.txt` and `specs/001-foundation-architecture-page/quickstart.md`
- [ ] T058 Run the validation scenarios from `specs/001-foundation-architecture-page/quickstart.md` and record any required fixes in `specs/001-foundation-architecture-page/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user story work.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; recommended MVP cut.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and should build on the page shell from User Story 1.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and integrates best after User Story 2 panel rendering exists.
- **Polish (Phase 6)**: Depends on completion of the desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: No feature-story dependency after Foundational; establishes the admin page, summary panel, and scan contract.
- **User Story 2 (P2)**: Depends on the page shell from US1 because settings panels live inside the same admin page.
- **User Story 3 (P3)**: Depends on panel rendering from US2 so compatibility states can disable and annotate the correct controls.

### Within Each User Story

- Tests MUST be written and fail before implementation begins.
- View-model and mapping work should land before page rendering changes that consume them.
- Server-side behavior should be implemented before JavaScript enhancements that depend on it.
- Security, validation, and fallback behavior must be completed before the story is considered done.

### Parallel Opportunities

- T003-T005 can run in parallel once the directory structure decision is set.
- T009-T013 can run in parallel in the Foundational phase because they target separate files.
- Story tests within each phase can run in parallel.
- T035 and T040 can run in parallel in US2 after the panel structure is defined.
- T047, T049, and T052 can run in parallel in US3 because they touch separate responsibilities.
- T053 and T055 can run in parallel during Polish.

---

## Parallel Example: User Story 1

```bash
# Launch User Story 1 tests together:
Task: "Add integration test for admin page registration and authorized access in tests/integration/Admin/SettingsPageAccessTest.php"
Task: "Add integration test for empty-state and cached readiness summary rendering in tests/integration/Admin/ReadinessSummaryRenderTest.php"
Task: "Add integration test for scan AJAX success and failure responses in tests/integration/Ajax/RunScanActionTest.php"
Task: "Add unit test for in-page summary view-model shaping in tests/unit/Scan/ReadinessSummaryViewModelTest.php"

# Launch independent implementation work after tests exist:
Task: "Implement readiness summary and page-level state assembly in src/Admin/ViewModel/SettingsPageViewModelFactory.php"
Task: "Implement HTTP request execution and failure handling for scans in src/Application/Scan/ScanClient.php"
Task: "Implement admin asset registration and localized scan-action data in src/Admin/Assets/SettingsPageAssets.php"
```

---

## Parallel Example: User Story 2

```bash
# Launch User Story 2 tests together:
Task: "Add integration test for page-level save across multiple capability sections in tests/integration/Admin/SettingsSaveFlowTest.php"
Task: "Add integration test for unsaved changes remaining separate from scan execution in tests/integration/Admin/UnsavedChangesBehaviorTest.php"
Task: "Add unit test for settings sanitization and normalization across all four capability groups in tests/unit/Settings/SettingsRepositoryRoundTripTest.php"
Task: "Add unit test for preview generation logic in tests/unit/Settings/CapabilityPreviewTest.php"

# Launch implementation work that can proceed in parallel:
Task: "Implement full option sanitization for Markdown and Content Signals fields in src/Application/Settings/SettingsSanitizer.php"
Task: "Implement full option sanitization for API Catalog custom entries and WebMCP tool toggles in src/Application/Settings/SettingsSanitizer.php"
Task: "Implement preview generation for content-signal lines, selected post types, API Catalog entries, and WebMCP tools in src/Admin/ViewModel/SettingsPageViewModelFactory.php"
```

---

## Parallel Example: User Story 3

```bash
# Launch User Story 3 tests together:
Task: "Add integration test for WooCommerce-dependent disabled states in tests/integration/Admin/CompatibilityStateRenderTest.php"
Task: "Add integration test for physical robots.txt and .well-known conflict warnings in tests/integration/Admin/CompatibilityWarningsTest.php"
Task: "Add integration test for visible non-interactive Phase 2 placeholders in tests/integration/Admin/PhasePlaceholderRenderTest.php"
Task: "Add unit test for compatibility warning creation and panel mapping in tests/unit/Compatibility/CompatibilityWarningFactoryTest.php"

# Launch implementation work that can proceed in parallel:
Task: "Implement compatibility warning rendering for robots.txt, .well-known, and dependency issues in src/Admin/Notices/CompatibilityNoticeRenderer.php"
Task: "Implement Phase 2 coming soon placeholder rendering in src/Public/AdminPagePlaceholders.php and src/Admin/Page/SettingsPage.php"
Task: "Add styling for disabled controls, warning notices, and placeholders in assets/css/admin-settings.css"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate the page route, empty-state summary, cached summary, and scan refresh behavior.
5. Ship/demo if only readiness review is required first.

### Incremental Delivery

1. Setup + Foundational → admin page infrastructure is ready.
2. Add User Story 1 → administrators can review readiness and run scans.
3. Add User Story 2 → administrators can configure and save the four Phase 1 panels.
4. Add User Story 3 → administrators see compatibility guidance and Phase 2 placeholders.
5. Finish Polish → harden security, accessibility, and docs.

### Parallel Team Strategy

With multiple developers:

1. One developer handles Setup and bootstrap wiring.
2. One developer handles settings infrastructure and sanitization in Phase 2.
3. After Foundational is done:
   - Developer A: User Story 1 page + scan flow
   - Developer B: User Story 2 settings panels + previews
   - Developer C: User Story 3 compatibility rendering + placeholders
4. Merge into Polish after all stories pass their independent tests.

---

## Notes

- Every task includes an explicit target file or directory to reduce ambiguity for smaller models.
- Do not skip test tasks; this feature has required automated coverage.
- Preserve WordPress-native behavior: use options, transients, admin-ajax, nonces, and capability checks rather than custom frameworks.
- Keep scan execution separate from settings persistence.
- Keep unavailable optional controls visible and disabled with explanatory text.
- Do not implement Phase 2 features; render placeholders only.
