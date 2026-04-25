---

description: "Task list for implementing the Phase 2 Foundation and Architecture Page"
---

# Tasks: Phase 2 Foundation and Architecture Page

**Input**: Design documents from `/specs/002-phase2-foundation-page/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for public integration boundaries, shared save behavior, validation, preview shaping, and option-schema compatibility.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **WordPress plugin**: `agent-ready-wp.php`, `uninstall.php`, `src/`, `assets/`, `languages/`, `tests/` at repository root
- Organize runtime code by responsibility such as `src/Admin/`, `src/Application/`, `src/Infrastructure/`, `src/Integrations/`, and `src/Public/`
- Put integration tests under `tests/integration/`, pure logic tests under `tests/unit/`, and reusable fixtures/helpers under `tests/fixtures/` or `tests/bootstrap.php`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the feature-specific test scaffolding and reusable fixtures before changing the existing Phase 1 admin page.

- [X] T001 Create Phase 2 integration test files in `tests/integration/Admin/Phase2McpServerCardRenderTest.php`, `tests/integration/Admin/Phase2SharedSaveFlowTest.php`, `tests/integration/Admin/Phase2ApplicabilityStateTest.php`, and `tests/integration/Admin/Phase2ValidationFeedbackTest.php`
- [X] T002 [P] Create Phase 2 unit test files in `tests/unit/Settings/Phase2DefaultsTest.php`, `tests/unit/Settings/Phase2SanitizerTest.php`, `tests/unit/Settings/Phase2PreviewSummaryTest.php`, and `tests/unit/Compatibility/Phase2ApplicabilityViewModelTest.php`
- [X] T003 [P] Extend `tests/bootstrap.php` with reusable Phase 2 settings payload builders, draft-preview helpers, and current-user setup helpers
- [X] T004 [P] Add Phase 2 fixture data for draft previews and invalid submissions in `tests/fixtures/phase2-preview-draft.json` and `tests/fixtures/phase2-invalid-settings.json`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Extend the shared settings and page infrastructure so the existing Agent Ready page can safely support Phase 2 data and same-page validation.

**⚠️ CRITICAL**: No user story work should begin until this phase is complete.

- [X] T005 Extend the default option schema with explicit `protected_apis` and `protected_resource` keys in `src/Application/Settings/Defaults.php`
- [X] T006 Extend `src/Application/Settings/SettingsRepository.php` to normalize reads for all Phase 2 nested keys from the `agent_ready_wp_settings` option
- [X] T007 Extend `src/Application/Settings/SettingsRepository.php` to merge and persist Phase 2 keys without dropping existing Phase 1 values
- [X] T008 Extend `src/Application/Settings/SettingsSanitizer.php` with reusable Phase 2 sanitizers for booleans, text fields, URLs, and URL-list fields
- [X] T009 Extend `src/Application/Settings/SettingsRepository.php` and `src/Application/Settings/SettingsSanitizer.php` with a validate-then-persist workflow that rejects invalid Phase 2 saves without calling `update_option()`
- [X] T010 Extend `src/Admin/ViewModel/SettingsPageViewModelFactory.php` to expose base Phase 2 section containers, pending validation messages, and draft-preview data
- [X] T011 Replace the current `options.php`-only save path with a same-page shared save handler scaffold in `src/Admin/Page/SettingsPage.php`
- [X] T012 Extend `src/Admin/Page/SettingsPage.php` and `src/Admin/ViewModel/SettingsPageViewModelFactory.php` with a dedicated live `phase_two_sections` container so Phase 2 no longer depends on placeholder-only rendering
- [X] T013 Extend `src/Admin/Assets/SettingsPageAssets.php` to localize Phase 2 field labels, preview labels, and applicability-state strings for the existing admin page
- [X] T014 Add foundational unit coverage for Phase 2 defaults and repository round-trip compatibility in `tests/unit/Settings/Phase2DefaultsTest.php` and `tests/unit/Settings/SettingsRepositoryRoundTripTest.php`
- [X] T015 Add foundational integration coverage for Phase 2 option persistence compatibility in `tests/integration/Admin/SettingsRepositoryTest.php` and `tests/integration/Admin/SettingsPageRegressionTest.php`

**Checkpoint**: The existing settings page can now carry Phase 2 state, reject invalid saves in place, and render live Phase 2 sections.

---

## Phase 3: User Story 1 - Configure Discovery Metadata (Priority: P1) 🎯 MVP

**Goal**: Let administrators configure the MCP Server Card on the existing Agent Ready page and review a readable draft preview before saving.

**Independent Test**: Open the Agent Ready page, fill in valid MCP Server Card values, save once, reload the page, and confirm the values persist and remain understandable without touching any Phase 2 protected-API fields.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T016 [P] [US1] Add integration test for MCP Server Card section rendering and empty-state guidance in `tests/integration/Admin/Phase2McpServerCardRenderTest.php`
- [X] T017 [P] [US1] Add integration test for saving and reloading MCP Server Card values through the shared page form in `tests/integration/Admin/Phase2SharedSaveFlowTest.php`
- [X] T018 [P] [US1] Add unit test for MCP Server Card preview formatting in `tests/unit/Settings/Phase2PreviewSummaryTest.php`
- [X] T019 [P] [US1] Add unit test for MCP Server Card validation requirements in `tests/unit/Settings/Phase2SanitizerTest.php`

### Implementation for User Story 1

- [X] T020 [US1] Extend `src/Admin/ViewModel/SettingsPageViewModelFactory.php` to build MCP Server Card section state, help text, and missing-field summaries
- [X] T021 [US1] Render live MCP Server Card controls and section copy on the existing page in `src/Admin/Page/SettingsPage.php`
- [X] T022 [US1] Wire MCP Server Card fields into the shared same-page save handler in `src/Admin/Page/SettingsPage.php` and `src/Application/Settings/SettingsRepository.php`
- [X] T023 [US1] Implement required `name`, `version`, and `transport` URL validation for MCP Server Card fields in `src/Application/Settings/SettingsSanitizer.php`
- [X] T024 [US1] Generate draft MCP Server Card preview items that match `specs/002-phase2-foundation-page/contracts/phase2-preview-schema.json` in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T025 [US1] Extend `assets/js/admin-settings.js` to update the MCP Server Card draft preview before save
- [X] T026 [US1] Extend `assets/css/admin-settings.css` with MCP Server Card section, preview, and empty-state styling
- [X] T027 [US1] Extend `src/Admin/Assets/SettingsPageAssets.php` with localized copy for MCP Server Card preview and required-field messaging

**Checkpoint**: User Story 1 is complete when administrators can configure and save MCP Server Card metadata independently of the protected-API sections.

---

## Phase 4: User Story 2 - Configure OAuth Discovery Details (Priority: P2)

**Goal**: Let administrators declare whether protected APIs exist, configure OAuth discovery and protected-resource metadata, and keep invalid values on the page for correction.

**Independent Test**: Turn the protected-API toggle on, enter valid OAuth and protected-resource values, save the page, reload it, and confirm the values persist; then submit invalid values and confirm the page rejects the save while preserving entered values and showing targeted feedback.

### Tests for User Story 2 ⚠️

- [X] T028 [P] [US2] Add integration test for the protected-API applicability toggle and disabled OAuth/Protected Resource sections in `tests/integration/Admin/Phase2ApplicabilityStateTest.php`
- [X] T029 [P] [US2] Add integration test for saving valid OAuth and protected-resource values through the shared page-level save flow in `tests/integration/Admin/Phase2SharedSaveFlowTest.php`
- [X] T030 [P] [US2] Add integration test for invalid Phase 2 saves preserving entered values and showing targeted feedback in `tests/integration/Admin/Phase2ValidationFeedbackTest.php`
- [X] T031 [P] [US2] Add unit test for OAuth discovery and protected-resource validation rules in `tests/unit/Settings/Phase2SanitizerTest.php`
- [X] T032 [P] [US2] Add unit test for OAuth discovery and protected-resource preview formatting in `tests/unit/Settings/Phase2PreviewSummaryTest.php`

### Implementation for User Story 2

- [X] T033 [US2] Extend `src/Admin/ViewModel/SettingsPageViewModelFactory.php` with the explicit protected-API applicability control and grouped OAuth section state
- [X] T034 [US2] Render the protected-API applicability toggle and grouped OAuth discovery fields in `src/Admin/Page/SettingsPage.php`
- [X] T035 [US2] Render the protected-resource fields and section-level disabled reasons in `src/Admin/Page/SettingsPage.php`
- [X] T036 [US2] Wire protected-API applicability, OAuth discovery, and protected-resource fields into the shared same-page save path in `src/Admin/Page/SettingsPage.php` and `src/Application/Settings/SettingsRepository.php`
- [X] T037 [US2] Implement validation for `issuer`, `authorization_endpoint`, `token_endpoint`, `jwks_uri`, `resource`, and `authorization_servers` in `src/Application/Settings/SettingsSanitizer.php`
- [X] T038 [US2] Preserve invalid Phase 2 values and field-targeted errors for redisplay after rejected saves in `src/Admin/Page/SettingsPage.php` and `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T039 [US2] Generate draft preview items for OAuth discovery and protected-resource sections in `src/Admin/ViewModel/SettingsPageViewModelFactory.php`
- [X] T040 [US2] Extend `assets/js/admin-settings.js` to toggle disabled Phase 2 sections and live-update OAuth/protected-resource draft previews
- [X] T041 [US2] Extend `assets/css/admin-settings.css` with disabled-section, field-error, and grouped-section styling for OAuth and protected-resource settings
- [X] T042 [US2] Extend `src/Admin/Assets/SettingsPageAssets.php` with localized strings for disabled-state explanations and validation labels

**Checkpoint**: User Stories 1 and 2 are complete when all Phase 2 fields save through one page-level action and invalid values are rejected in place without partial persistence.

---

## Phase 5: User Story 3 - Understand Phase 2 Readiness and Dependencies (Priority: P3)

**Goal**: Make the expanded page explain when Phase 2 applies, why some sections are disabled, and how the new sections relate to the existing Phase 1 workflow.

**Independent Test**: View the page with protected APIs both off and on, confirm the disabled sections remain readable with explanations, confirm draft previews are clearly labeled as draft, and confirm the page still feels like one continuous Phase 1 + Phase 2 experience with one save button.

### Tests for User Story 3 ⚠️

- [X] T043 [P] [US3] Add integration test for non-applicable Phase 2 explanatory text and readable disabled sections in `tests/integration/Admin/Phase2ApplicabilityStateTest.php`
- [X] T044 [P] [US3] Add integration test for one continuous page experience with Phase 1 content and a single save button in `tests/integration/Admin/SettingsPageRegressionTest.php`
- [X] T045 [P] [US3] Add integration test for draft-vs-saved preview wording and missing-configuration guidance in `tests/integration/Admin/Phase2ValidationFeedbackTest.php`
- [X] T046 [P] [US3] Add unit test for applicability-state, disabled-reason, and missing-guidance mapping in `tests/unit/Compatibility/Phase2ApplicabilityViewModelTest.php`

### Implementation for User Story 3

- [X] T047 [US3] Extend `src/Admin/ViewModel/SettingsPageViewModelFactory.php` to assemble applicability explanations, disabled reasons, and incomplete-configuration guidance for all Phase 2 sections
- [X] T048 [US3] Render section descriptions, disabled explanations, missing-setup guidance, and draft-state labels in `src/Admin/Page/SettingsPage.php`
- [X] T049 [US3] Replace the old Phase 2 “coming soon” placeholder output with live continuity messaging in `src/Public/AdminPagePlaceholders.php` and `src/Admin/Page/SettingsPage.php`
- [X] T050 [US3] Extend `src/Admin/Notices/CompatibilityNoticeRenderer.php` and `src/Admin/Page/SettingsPage.php` to surface any Phase 2 page-level warnings without splitting the existing workflow
- [X] T051 [US3] Extend `assets/css/admin-settings.css` with accessible explanation panels, non-color status cues, and readable disabled text for Phase 2
- [X] T052 [US3] Extend `assets/js/admin-settings.js` only as needed to keep draft labels and disabled-state hints in sync with applicability changes

**Checkpoint**: All three user stories are complete when the Phase 2 extension is understandable, discoverable, and continuous with the existing Agent Ready page.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Harden the full Phase 1 + Phase 2 page, update documentation, and run the final acceptance pass.

- [X] T053 [P] Add i18n wrappers for all new Phase 2 strings in `src/Admin/Page/SettingsPage.php`, `src/Admin/ViewModel/SettingsPageViewModelFactory.php`, `src/Admin/Assets/SettingsPageAssets.php`, and `src/Public/AdminPagePlaceholders.php`
- [X] T054 Add security hardening for the shared-save nonce, capability checks, URL escaping, and validation-failure redisplay in `src/Admin/Page/SettingsPage.php` and `src/Application/Settings/SettingsSanitizer.php`
- [X] T055 [P] Add regression coverage for shared save, rejected-save redisplay, and draft preview generation in `tests/integration/Admin/Phase2SharedSaveFlowTest.php`, `tests/integration/Admin/Phase2ValidationFeedbackTest.php`, and `tests/unit/Settings/Phase2PreviewSummaryTest.php`
- [X] T056 Add documentation for the new Phase 2 admin sections in `readme.txt` and `specs/002-phase2-foundation-page/quickstart.md`
- [ ] T057 Run the validation scenarios from `specs/002-phase2-foundation-page/quickstart.md` and record any required fixes in `specs/002-phase2-foundation-page/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; recommended MVP cut.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and the live Phase 2 section shell from User Story 1.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and benefits from the real Phase 2 rendering added in User Stories 1 and 2.
- **Polish (Phase 6)**: Depends on completion of the desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: No dependency on the other stories after Foundational completion.
- **User Story 2 (P2)**: Depends on the shared save/draft infrastructure from Foundational and integrates into the live page shell built in US1.
- **User Story 3 (P3)**: Depends on the presence of live Phase 2 sections so the page can explain actual disabled and incomplete states instead of placeholders.

### Within Each User Story

- Tests MUST be written and fail before implementation begins.
- View-model and settings-shaping logic should land before page rendering changes that consume it.
- Shared save and validation behavior must be implemented before preview and UX polish.
- Security, escaping, and same-page redisplay behavior must be complete before a story is considered done.

### Parallel Opportunities

- T002-T004 can run in parallel during Setup because they target different files.
- T006, T008, T010, and T013 can run in parallel in Foundational once the option-key decisions are fixed.
- Test tasks within each user story can run in parallel.
- T025-T027 can run in parallel near the end of US1.
- T040-T042 can run in parallel near the end of US2.
- T050-T052 can run in parallel near the end of US3.
- T053 and T055 can run in parallel during Polish.

---

## Parallel Example: User Story 1

```bash
# Launch User Story 1 tests together:
Task: "Add integration test for MCP Server Card section rendering and empty-state guidance in tests/integration/Admin/Phase2McpServerCardRenderTest.php"
Task: "Add integration test for saving and reloading MCP Server Card values through the shared page form in tests/integration/Admin/Phase2SharedSaveFlowTest.php"
Task: "Add unit test for MCP Server Card preview formatting in tests/unit/Settings/Phase2PreviewSummaryTest.php"
Task: "Add unit test for MCP Server Card validation requirements in tests/unit/Settings/Phase2SanitizerTest.php"

# Launch independent finishing work together:
Task: "Extend assets/js/admin-settings.js to update the MCP Server Card draft preview before save"
Task: "Extend assets/css/admin-settings.css with MCP Server Card section, preview, and empty-state styling"
Task: "Extend src/Admin/Assets/SettingsPageAssets.php with localized copy for MCP Server Card preview and required-field messaging"
```

---

## Parallel Example: User Story 2

```bash
# Launch User Story 2 tests together:
Task: "Add integration test for the protected-API applicability toggle and disabled OAuth/Protected Resource sections in tests/integration/Admin/Phase2ApplicabilityStateTest.php"
Task: "Add integration test for saving valid OAuth and protected-resource values through the shared page-level save flow in tests/integration/Admin/Phase2SharedSaveFlowTest.php"
Task: "Add integration test for invalid Phase 2 saves preserving entered values and showing targeted feedback in tests/integration/Admin/Phase2ValidationFeedbackTest.php"
Task: "Add unit test for OAuth discovery and protected-resource validation rules in tests/unit/Settings/Phase2SanitizerTest.php"
Task: "Add unit test for OAuth discovery and protected-resource preview formatting in tests/unit/Settings/Phase2PreviewSummaryTest.php"

# Launch independent finishing work together:
Task: "Extend assets/js/admin-settings.js to toggle disabled Phase 2 sections and live-update OAuth/protected-resource draft previews"
Task: "Extend assets/css/admin-settings.css with disabled-section, field-error, and grouped-section styling for OAuth and protected-resource settings"
Task: "Extend src/Admin/Assets/SettingsPageAssets.php with localized strings for disabled-state explanations and validation labels"
```

---

## Parallel Example: User Story 3

```bash
# Launch User Story 3 tests together:
Task: "Add integration test for non-applicable Phase 2 explanatory text and readable disabled sections in tests/integration/Admin/Phase2ApplicabilityStateTest.php"
Task: "Add integration test for one continuous page experience with Phase 1 content and a single save button in tests/integration/Admin/SettingsPageRegressionTest.php"
Task: "Add integration test for draft-vs-saved preview wording and missing-configuration guidance in tests/integration/Admin/Phase2ValidationFeedbackTest.php"
Task: "Add unit test for applicability-state, disabled-reason, and missing-guidance mapping in tests/unit/Compatibility/Phase2ApplicabilityViewModelTest.php"

# Launch independent finishing work together:
Task: "Extend src/Admin/Notices/CompatibilityNoticeRenderer.php and src/Admin/Page/SettingsPage.php to surface any Phase 2 page-level warnings without splitting the existing workflow"
Task: "Extend assets/css/admin-settings.css with accessible explanation panels, non-color status cues, and readable disabled text for Phase 2"
Task: "Extend assets/js/admin-settings.js only as needed to keep draft labels and disabled-state hints in sync with applicability changes"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate MCP Server Card render, save, reload, and draft preview behavior.
5. Demo or ship the Phase 2 page in a limited MCP Server Card-only state if needed.

### Incremental Delivery

1. Setup + Foundational → the existing page can carry Phase 2 state and reject invalid saves in place.
2. Add User Story 1 → administrators can configure MCP Server Card metadata.
3. Add User Story 2 → administrators can configure protected-API applicability, OAuth discovery, and protected-resource metadata.
4. Add User Story 3 → the full page explains applicability, disabled states, and continuity with Phase 1.
5. Finish Polish → finalize documentation, regression coverage, and manual validation.

### Parallel Team Strategy

1. One developer completes Setup + Foundational.
2. After Foundational completion:
   Developer A: User Story 1
   Developer B: User Story 2
   Developer C: User Story 3 explanatory and continuity work
3. Rejoin for Polish and quickstart validation.

---

## Notes

- [P] tasks mean different files and no unmet dependency on another incomplete task.
- The current repo still contains placeholder-only Phase 2 rendering, so implementation must explicitly replace placeholder output with live section rendering.
- The current page posts to `options.php`; the spec requires same-page rejection with preserved values, so the shared save flow must be upgraded rather than only extending the existing sanitize callback.
- The underlying public Phase 2 endpoints remain out of scope for this feature; this tasks file only covers the admin page experience.
