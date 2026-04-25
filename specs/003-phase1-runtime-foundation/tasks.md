# Tasks: Phase 1 Runtime Foundation

**Input**: Design documents from `/specs/003-phase1-runtime-foundation/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for public runtime contracts, negotiation and fallback behavior, canonical `robots.txt` output, excluded-request handling, UTF-8/Arabic preservation, and compatibility handling.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g. `US1`, `US2`, `US3`)
- Include exact file paths in descriptions

## Path Conventions

- **WordPress plugin**: `agent-ready-wp.php`, `uninstall.php`, `src/`, `assets/`, `languages/`, `tests/` at repository root
- Organize runtime code by responsibility such as `src/Admin/`, `src/Application/`, `src/Infrastructure/`, `src/Integrations/`, and `src/Public/`
- Put integration tests under `tests/integration/`, pure logic tests under `tests/unit/`, and reusable fixtures/helpers under `tests/fixtures/` or `tests/bootstrap.php`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare runtime fixtures, test harness helpers, and module layout for markdown negotiation and content-signal behavior.

- [X] T001 Create runtime integration test files in `tests/integration/Runtime/RuntimeHookRegistrationTest.php`, `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`, `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`, `tests/integration/Runtime/ContentSignalsRobotsIntegrationTest.php`, and `tests/integration/Runtime/ContentSignalsFallbackIntegrationTest.php`
- [X] T002 [P] Create runtime unit test files in `tests/unit/Runtime/MarkdownAcceptPreferenceParserTest.php`, `tests/unit/Runtime/MarkdownEligibilityEvaluatorTest.php`, `tests/unit/Runtime/MarkdownRendererTest.php`, `tests/unit/Runtime/TokenEstimatorTest.php`, and `tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php`
- [X] T003 [P] Extend `tests/bootstrap.php` with request-context helpers for frontend document, excluded route, and `Accept`-header simulation
- [X] T004 [P] Add runtime fixtures in `tests/fixtures/markdown-accept-cases.json`, `tests/fixtures/markdown-expected-output.md`, `tests/fixtures/content-signals-robots.txt`, and any Arabic-content fixture needed for markdown verification
- [X] T005 Create runtime module directories and placeholder files under `src/Application/Runtime/Markdown/`, `src/Application/Runtime/ContentSignals/`, and `src/Infrastructure/WordPress/Runtime/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build shared runtime infrastructure and hook registration needed by all user stories.

**⚠️ CRITICAL**: No user story work should begin until this phase is complete.

- [X] T006 Create runtime feature settings gateway in `src/Application/Runtime/RuntimeFeatureSettingsGateway.php` using `src/Application/Settings/SettingsRepository.php`
- [X] T007 [P] Create runtime compatibility gateway in `src/Application/Runtime/RuntimeCompatibilityGateway.php` using `src/Application/Compatibility/EnvironmentDetector.php`
- [X] T008 [P] Create markdown request context factory in `src/Application/Runtime/Markdown/MarkdownRequestContextFactory.php`
- [X] T009 [P] Add excluded-request detection seams in `src/Application/Runtime/Markdown/MarkdownRequestContextFactory.php` and `tests/bootstrap.php`
- [X] T010 [P] Create runtime hook orchestrator in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`
- [X] T011 Wire runtime registrars in `src/Infrastructure/WordPress/Hooks.php` to register `template_redirect` and `robots_txt` callbacks
- [X] T012 [P] Create shared markdown response writer in `src/Application/Runtime/Markdown/MarkdownResponseWriter.php`
- [X] T013 [P] Create shared content-signal line normalizer in `src/Application/Runtime/ContentSignals/ContentSignalLineNormalizer.php`
- [X] T014 Create foundational runtime visibility/access helper in `src/Application/Runtime/Markdown/ContentVisibilityGuard.php`
- [X] T015 [P] Add foundational runtime unit coverage for gateways, context creation, and line normalization in `tests/unit/Runtime/MarkdownEligibilityEvaluatorTest.php` and `tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php`
- [X] T016 Add foundational runtime integration coverage for hook registration in `tests/integration/Runtime/RuntimeHookRegistrationTest.php`

**Checkpoint**: Runtime foundation is ready; user story work can begin.

---

## Phase 3: User Story 1 - Serve Markdown To Agents (Priority: P1) 🎯 MVP

**Goal**: Serve markdown for qualifying supported singular frontend document requests while preserving normal HTML or native default behavior for all non-qualifying and excluded requests.

**Independent Test**: Request supported singular content with `Accept: text/markdown` and verify markdown headers/body; verify browser-style and excluded requests stay on their original non-markdown behavior.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T017 [P] [US1] Add integration test for qualifying singular markdown response contract in `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`
- [X] T018 [P] [US1] Add integration test for normal browser-style HTML fallback in `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`
- [X] T019 [P] [US1] Add integration test for excluded-request fallback (`wp-admin`, REST, AJAX, feed, sitemap, login, asset analogs) in `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`
- [X] T020 [P] [US1] Add integration test for Arabic and UTF-8 preservation in `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`
- [X] T021 [P] [US1] Add integration test for WooCommerce product markdown behavior in `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`
- [X] T022 [P] [US1] Add unit tests for `Accept` preference parsing in `tests/unit/Runtime/MarkdownAcceptPreferenceParserTest.php`
- [X] T023 [P] [US1] Add unit tests for frontend-document eligibility and excluded-context reasons in `tests/unit/Runtime/MarkdownEligibilityEvaluatorTest.php`
- [X] T024 [P] [US1] Add unit tests for markdown rendering and UTF-8/Arabic preservation in `tests/unit/Runtime/MarkdownRendererTest.php` and `tests/unit/Runtime/TokenEstimatorTest.php`

### Implementation for User Story 1

- [X] T025 [US1] Implement `Accept` parser in `src/Application/Runtime/Markdown/MarkdownAcceptPreferenceParser.php`
- [X] T026 [US1] Extend markdown request context creation for eligible frontend document detection in `src/Application/Runtime/Markdown/MarkdownRequestContextFactory.php`
- [X] T027 [US1] Implement markdown eligibility evaluator with excluded-request fallback handling in `src/Application/Runtime/Markdown/MarkdownEligibilityEvaluator.php`
- [X] T028 [US1] Implement markdown renderer for headings, links, paragraphs, lists, and UTF-8-safe text conversion in `src/Application/Runtime/Markdown/MarkdownRenderer.php`
- [X] T029 [US1] Implement token estimation in `src/Application/Runtime/Markdown/TokenEstimator.php`
- [X] T030 [US1] Implement markdown runtime handler orchestration in `src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php`
- [X] T031 [US1] Ensure markdown response emits `Content-Type`, `Vary: Accept`, and `x-markdown-tokens` in `src/Application/Runtime/Markdown/MarkdownResponseWriter.php`
- [X] T032 [US1] Wire markdown runtime handler through `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`
- [X] T033 [US1] Update markdown contract notes in `specs/003-phase1-runtime-foundation/contracts/markdown-negotiation-contract.md` if implementation decisions require any final wording alignment

**Checkpoint**: User Story 1 is complete when qualifying singular frontend requests return markdown and excluded or unsupported requests preserve original behavior.

---

## Phase 4: User Story 2 - Publish Content Signals In robots.txt (Priority: P2)

**Goal**: Emit one canonical configured `Content-Signal` directive in generated `robots.txt` output and omit directives when disabled or unset.

**Independent Test**: Enable configured content signals and verify one canonical directive appears in `/robots.txt`; disable or unset values and verify the directive is absent.

### Tests for User Story 2 ⚠️

- [X] T034 [P] [US2] Add integration test for canonical single-line `Content-Signal` output in `tests/integration/Runtime/ContentSignalsRobotsIntegrationTest.php`
- [X] T035 [P] [US2] Add integration test for disabled and unset content-signal behavior in `tests/integration/Runtime/ContentSignalsRobotsIntegrationTest.php`
- [X] T036 [P] [US2] Add integration test for preserving default output when no directive should be emitted in `tests/integration/Runtime/ContentSignalsFallbackIntegrationTest.php`
- [X] T037 [P] [US2] Add unit tests for signal directive shaping and key canonicalization in `tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php`
- [X] T038 [P] [US2] Add unit tests for existing-line replacement behavior in `tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php`

### Implementation for User Story 2

- [X] T039 [US2] Implement directive builder in `src/Application/Runtime/ContentSignals/ContentSignalDirectiveBuilder.php`
- [X] T040 [US2] Implement canonical key mapping (`ai-train`, `search`, `ai-input`) in `src/Application/Runtime/ContentSignals/ContentSignalDirectiveBuilder.php`
- [X] T041 [US2] Implement existing `Content-Signal` line replacement in `src/Application/Runtime/ContentSignals/ContentSignalLineNormalizer.php`
- [X] T042 [US2] Implement robots output applier in `src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php`
- [X] T043 [US2] Wire robots filter callback through `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`
- [X] T044 [US2] Ensure disabled and unset short-circuit behavior in `src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php`

**Checkpoint**: User Stories 1 and 2 are complete when markdown and content-signal contracts both work independently.

---

## Phase 5: User Story 3 - Preserve Safe Fallbacks Under Compatibility Limits (Priority: P3)

**Goal**: Ensure unsupported contexts and compatibility conflicts fail safely without breaking default WordPress behavior or exposing inaccessible content.

**Independent Test**: Simulate unsupported markdown requests, excluded request classes, restricted content, and physical `robots.txt` conflict scenarios and confirm graceful fallback with no unsafe mutation.

### Tests for User Story 3 ⚠️

- [X] T045 [P] [US3] Add integration test for unsupported singular-context markdown fallback in `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`
- [X] T046 [P] [US3] Add integration test for access-control-safe markdown fallback in `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`
- [X] T047 [P] [US3] Add integration test for explicit excluded-request markdown fallback in `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`
- [X] T048 [P] [US3] Add integration test for physical `robots.txt` conflict fallback in `tests/integration/Runtime/ContentSignalsFallbackIntegrationTest.php`
- [X] T049 [P] [US3] Add unit tests for fallback reason mapping and ineligible-context coverage in `tests/unit/Runtime/MarkdownEligibilityEvaluatorTest.php`

### Implementation for User Story 3

- [X] T050 [US3] Enforce visibility and access-guard behavior in `src/Application/Runtime/Markdown/ContentVisibilityGuard.php` and `src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php`
- [X] T051 [US3] Ensure excluded and unsupported markdown requests never emit markdown headers or body in `src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php` and `src/Application/Runtime/Markdown/MarkdownResponseWriter.php`
- [X] T052 [US3] Implement graceful no-mutation physical `robots.txt` conflict handling in `src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php`
- [X] T053 [US3] Surface runtime conflict diagnostics via `src/Application/Compatibility/EnvironmentDetector.php` and `src/Admin/Notices/CompatibilityNoticeRenderer.php`
- [X] T054 [US3] Ensure runtime handlers short-circuit when features are disabled in `src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php` and `src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php`
- [X] T055 [US3] Extend runtime quick validation notes for excluded and conflict paths in `specs/003-phase1-runtime-foundation/quickstart.md`
- [X] T056 [US3] Keep runtime-scope assumptions aligned in `specs/003-phase1-runtime-foundation/plan.md` and `specs/003-phase1-runtime-foundation/research.md`

**Checkpoint**: All three user stories are complete when runtime contracts are stable and all compatibility fallbacks are safe.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Harden runtime behavior, documentation, and regression coverage across stories.

- [X] T057 [P] Add cross-story regression coverage for markdown headers, excluded-request fallbacks, and canonical content-signal output in `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`, `tests/integration/Runtime/MarkdownFallbackIntegrationTest.php`, and `tests/integration/Runtime/ContentSignalsRobotsIntegrationTest.php`
- [X] T058 [P] Add final UTF-8 and Arabic regression coverage in `tests/unit/Runtime/MarkdownRendererTest.php` and `tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php`
- [X] T059 Add security hardening review for markdown access handling and response-header emission in `src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php` and `src/Application/Runtime/Markdown/MarkdownResponseWriter.php`
- [X] T060 Add runtime feature documentation updates in `readme.txt` and `PLAN.md`
- [X] T061 Run quickstart validation scenarios from `specs/003-phase1-runtime-foundation/quickstart.md` and record outcomes in `specs/003-phase1-runtime-foundation/quickstart.md`
- [X] T062 [P] Final cleanup of runtime fixtures and helpers in `tests/fixtures/` and `tests/bootstrap.php`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; recommended MVP cut.
- **User Story 2 (Phase 4)**: Depends on Foundational completion; can proceed independently of US1 business logic.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and verifies fallback and compatibility behavior across US1 and US2 runtime paths.
- **Polish (Phase 6)**: Depends on completion of desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: No dependency on other stories after Foundational completion.
- **User Story 2 (P2)**: No hard dependency on US1 logic, but shares foundational runtime registration.
- **User Story 3 (P3)**: Depends on US1 and US2 runtime seams to verify fallback and compatibility behavior.

### Within Each User Story

- Tests MUST be written and fail before implementation begins.
- Request-context detection and pure transformation logic should be implemented before runtime handlers that consume them.
- Runtime handler integration should complete before compatibility polish and docs alignment.
- Story contract updates and regression checks should complete before the story checkpoint.

### Parallel Opportunities

- T002-T004 can run in parallel during Setup.
- T007-T010 and T012-T015 can run in parallel during Foundational once module paths exist.
- Test tasks marked `[P]` inside each story can run in parallel.
- US1 implementation tasks T025-T029 can run in parallel after foundational context exists.
- US2 implementation tasks T039-T042 can run in parallel.
- Polish tasks T057, T058, and T062 can run in parallel.

---

## Parallel Example: User Story 1

```bash
# Launch User Story 1 tests together:
Task: "Add integration test for qualifying singular markdown response contract in tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php"
Task: "Add integration test for excluded-request fallback in tests/integration/Runtime/MarkdownFallbackIntegrationTest.php"
Task: "Add integration test for Arabic and UTF-8 preservation in tests/integration/Runtime/MarkdownNegotiationIntegrationTest.php"
Task: "Add unit tests for Accept preference parsing in tests/unit/Runtime/MarkdownAcceptPreferenceParserTest.php"

# Launch independent implementation work together:
Task: "Implement Accept parser in src/Application/Runtime/Markdown/MarkdownAcceptPreferenceParser.php"
Task: "Extend markdown request context creation for eligible frontend document detection in src/Application/Runtime/Markdown/MarkdownRequestContextFactory.php"
Task: "Implement markdown renderer in src/Application/Runtime/Markdown/MarkdownRenderer.php"
Task: "Implement token estimation in src/Application/Runtime/Markdown/TokenEstimator.php"
```

---

## Parallel Example: User Story 2

```bash
# Launch User Story 2 tests together:
Task: "Add integration test for canonical single-line Content-Signal output in tests/integration/Runtime/ContentSignalsRobotsIntegrationTest.php"
Task: "Add unit tests for signal directive shaping in tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php"
Task: "Add unit tests for existing-line replacement behavior in tests/unit/Runtime/ContentSignalDirectiveBuilderTest.php"

# Launch independent implementation work together:
Task: "Implement directive builder in src/Application/Runtime/ContentSignals/ContentSignalDirectiveBuilder.php"
Task: "Implement existing Content-Signal line replacement in src/Application/Runtime/ContentSignals/ContentSignalLineNormalizer.php"
Task: "Implement robots output applier in src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php"
```

---

## Parallel Example: User Story 3

```bash
# Launch User Story 3 tests together:
Task: "Add integration test for explicit excluded-request markdown fallback in tests/integration/Runtime/MarkdownFallbackIntegrationTest.php"
Task: "Add integration test for physical robots.txt conflict fallback in tests/integration/Runtime/ContentSignalsFallbackIntegrationTest.php"
Task: "Add unit tests for fallback reason mapping and ineligible-context coverage in tests/unit/Runtime/MarkdownEligibilityEvaluatorTest.php"

# Launch independent implementation work together:
Task: "Ensure excluded and unsupported markdown requests never emit markdown headers or body in src/Application/Runtime/Markdown/MarkdownRuntimeHandler.php and src/Application/Runtime/Markdown/MarkdownResponseWriter.php"
Task: "Implement graceful no-mutation physical robots.txt conflict handling in src/Application/Runtime/ContentSignals/ContentSignalsRobotsFilter.php"
Task: "Surface runtime conflict diagnostics via src/Application/Compatibility/EnvironmentDetector.php and src/Admin/Notices/CompatibilityNoticeRenderer.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate markdown negotiation behavior independently.
5. Demo or ship MVP runtime markdown behavior.

### Incremental Delivery

1. Setup + Foundational → runtime infrastructure ready.
2. Add User Story 1 → markdown contract behavior available.
3. Add User Story 2 → content-signal `robots.txt` behavior available.
4. Add User Story 3 → compatibility-safe fallbacks and diagnostics complete.
5. Complete Phase 6 for regression hardening and documentation.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. After Foundational completion:
   - Developer A: US1 markdown runtime contract and UTF-8 preservation.
   - Developer B: US2 content-signal runtime contract.
   - Developer C: US3 fallback and compatibility behavior.
3. Integrate and run cross-story regression in Phase 6.

---

## Notes

- `[P]` tasks target disjoint files and can run concurrently.
- `[US*]` labels map each task to its independently testable story.
- Keep runtime scope limited to F2/F3 and the clarified singular frontend markdown MVP for this feature branch.
- Verify test tasks fail before implementation tasks.
- Preserve default WordPress or native request behavior in all non-eligible and conflict paths.
