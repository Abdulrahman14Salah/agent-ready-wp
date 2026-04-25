---

description: "Task list for implementing Phase 1 Discovery Runtime"
---

# Tasks: Phase 1 Discovery Runtime

**Input**: Design documents from `/specs/004-phase1-discovery-runtime/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for public runtime endpoint/script contracts, settings-driven discovery output, WooCommerce-conditional behavior, and compatibility/fallback handling.

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

**Purpose**: Create discovery-runtime scaffolding, test entry points, and reusable fixtures before changing the runtime behavior.

- [X] T001 Create discovery runtime integration test files in `tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php`, `tests/integration/Runtime/ApiCatalogFallbackIntegrationTest.php`, `tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php`, and `tests/integration/Runtime/WebMcpFallbackIntegrationTest.php`
- [X] T002 [P] Create discovery runtime unit test files in `tests/unit/Runtime/ApiCatalogDocumentBuilderTest.php`, `tests/unit/Runtime/ApiCatalogRequestMatcherTest.php`, `tests/unit/Runtime/WebMcpToolResolverTest.php`, and `tests/unit/Runtime/WebMcpPayloadBuilderTest.php`
- [X] T003 [P] Extend `tests/bootstrap.php` with helpers for `.well-known` request routing, frontend enqueue capture, and browser-capability simulation
- [X] T004 [P] Add discovery fixtures in `tests/fixtures/api-catalog-custom-entries.json`, `tests/fixtures/api-catalog-linkset.json`, and `tests/fixtures/webmcp-runtime-payload.json`
- [X] T005 Create discovery runtime module directories and placeholder files under `src/Application/Runtime/ApiCatalog/`, `src/Application/Runtime/WebMcp/`, and `assets/js/webmcp-runtime.js`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the shared runtime infrastructure, settings accessors, and hook registration needed by all discovery stories.

**⚠️ CRITICAL**: No user story work should begin until this phase is complete.

- [X] T006 Extend `src/Application/Runtime/RuntimeFeatureSettingsGateway.php` with `getApiCatalogSettings()` and `getWebMcpSettings()` accessors
- [X] T007 [P] Extend `src/Application/Runtime/RuntimeCompatibilityGateway.php` to expose discovery-specific compatibility state consumed by API Catalog and WebMCP runtime modules
- [X] T008 [P] Create API Catalog request-context and decision scaffolding in `src/Application/Runtime/ApiCatalog/ApiCatalogRequestContextFactory.php` and `src/Application/Runtime/ApiCatalog/ApiCatalogResolutionDecision.php`
- [X] T009 [P] Create WebMCP runtime-context and emission-decision scaffolding in `src/Application/Runtime/WebMcp/WebMcpRuntimeContextFactory.php` and `src/Application/Runtime/WebMcp/WebMcpEmissionDecision.php`
- [X] T010 [P] Extend `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` with discovery hook-registration points for rewrite setup, query vars, API Catalog handling, and WebMCP enqueue flow
- [X] T011 Wire the discovery runtime registrars in `src/Infrastructure/WordPress/Hooks.php` so they instantiate alongside the existing runtime modules
- [X] T012 Create shared discovery unit coverage for gateway-backed settings and compatibility resolution in `tests/unit/Runtime/ApiCatalogRequestMatcherTest.php` and `tests/unit/Runtime/WebMcpToolResolverTest.php`
- [X] T013 Add foundational integration coverage for discovery hook registration in `tests/integration/Runtime/RuntimeHookRegistrationTest.php`

**Checkpoint**: Discovery runtime foundation is ready; user story implementation can begin.

---

## Phase 3: User Story 1 - Publish API Catalog Endpoint (Priority: P1) 🎯 MVP

**Goal**: Publish a machine-readable `/.well-known/api-catalog` endpoint that includes default and configured discovery entries when enabled.

**Independent Test**: Request `/.well-known/api-catalog` on an enabled site and verify a successful `application/linkset+json` response containing the default WordPress REST API entry and any eligible configured entries.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T014 [P] [US1] Add integration test for the enabled API Catalog response contract in `tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php`
- [X] T015 [P] [US1] Add integration test for WooCommerce-conditional API Catalog entries in `tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php`
- [X] T016 [P] [US1] Add integration test for configured custom entry output in `tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php`
- [X] T017 [P] [US1] Add unit tests for Linkset JSON document shaping in `tests/unit/Runtime/ApiCatalogDocumentBuilderTest.php`
- [X] T018 [P] [US1] Add unit tests for route matching and eligibility decisions in `tests/unit/Runtime/ApiCatalogRequestMatcherTest.php`

### Implementation for User Story 1

- [X] T019 [US1] Implement rewrite registration and query-var mapping for `/.well-known/api-catalog` in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`
- [X] T020 [US1] Implement API Catalog entry normalization in `src/Application/Runtime/ApiCatalog/ApiCatalogEntryFactory.php`
- [X] T021 [US1] Implement Linkset JSON document building in `src/Application/Runtime/ApiCatalog/ApiCatalogDocumentBuilder.php`
- [X] T022 [US1] Implement API Catalog request matching and resolution in `src/Application/Runtime/ApiCatalog/ApiCatalogRequestMatcher.php`
- [X] T023 [US1] Implement API Catalog response writing in `src/Application/Runtime/ApiCatalog/ApiCatalogResponseWriter.php`
- [X] T024 [US1] Implement the runtime handler that emits API Catalog output during the catalog route in `src/Application/Runtime/ApiCatalog/ApiCatalogRuntimeHandler.php`
- [X] T025 [US1] Extend `src/Application/Runtime/RuntimeFeatureSettingsGateway.php` to resolve default WordPress, WooCommerce, and custom entry data for the API Catalog
- [X] T026 [US1] Update the discovery contract notes in `specs/004-phase1-discovery-runtime/contracts/api-catalog-contract.md`

**Checkpoint**: User Story 1 is complete when the API Catalog endpoint works independently and emits the expected Linkset discovery document.

---

## Phase 4: User Story 2 - Expose WebMCP Runtime Script (Priority: P2)

**Goal**: Emit a public frontend WebMCP runtime script that exposes the configured compatible tools only when WebMCP is enabled.

**Independent Test**: Load any public frontend page with WebMCP enabled and verify that the plugin emits the runtime asset and registration payload with the expected tool set.

### Tests for User Story 2 ⚠️

- [X] T027 [P] [US2] Add integration test for WebMCP runtime asset emission on public frontend pages in `tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php`
- [X] T028 [P] [US2] Add integration test for WooCommerce-conditional tool exposure in `tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php`
- [X] T029 [P] [US2] Add integration test for disabled WebMCP behavior in `tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php`
- [X] T030 [P] [US2] Add unit tests for settings-driven tool resolution in `tests/unit/Runtime/WebMcpToolResolverTest.php`
- [X] T031 [P] [US2] Add unit tests for frontend payload shaping in `tests/unit/Runtime/WebMcpPayloadBuilderTest.php`

### Implementation for User Story 2

- [X] T032 [US2] Implement WebMCP tool resolution from saved settings and compatibility state in `src/Application/Runtime/WebMcp/WebMcpToolResolver.php`
- [X] T033 [US2] Implement frontend payload building for localized runtime data in `src/Application/Runtime/WebMcp/WebMcpPayloadBuilder.php`
- [X] T034 [US2] Implement the WebMCP enqueue/runtime coordinator in `src/Application/Runtime/WebMcp/WebMcpRuntimeEmitter.php`
- [X] T035 [US2] Create the public runtime asset with capability detection and registration wiring in `assets/js/webmcp-runtime.js`
- [X] T036 [US2] Extend `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` to enqueue the WebMCP runtime on public frontend requests only
- [X] T037 [US2] Extend `src/Application/Runtime/RuntimeFeatureSettingsGateway.php` to expose saved WebMCP tool toggles for runtime resolution
- [X] T038 [US2] Update the runtime behavior notes in `specs/004-phase1-discovery-runtime/contracts/webmcp-runtime-contract.md`

**Checkpoint**: User Stories 1 and 2 are complete when both the API Catalog endpoint and WebMCP frontend runtime work independently.

---

## Phase 5: User Story 3 - Preserve Safe Discovery Fallbacks (Priority: P3)

**Goal**: Ensure discovery runtime behavior degrades safely when features are disabled, physical-file conflicts exist, or browser capability is unavailable.

**Independent Test**: Simulate physical `/.well-known/api-catalog` conflicts, disabled settings, and unsupported browser capability, then verify that default WordPress/host behavior remains authoritative with no fatal errors.

### Tests for User Story 3 ⚠️

- [X] T039 [P] [US3] Add integration test for physical API Catalog file-conflict fallback in `tests/integration/Runtime/ApiCatalogFallbackIntegrationTest.php`
- [X] T040 [P] [US3] Add integration test for API Catalog disabled fallback behavior in `tests/integration/Runtime/ApiCatalogFallbackIntegrationTest.php`
- [X] T041 [P] [US3] Add integration test for unsupported-browser WebMCP no-op behavior in `tests/integration/Runtime/WebMcpFallbackIntegrationTest.php`
- [X] T042 [P] [US3] Add integration test for partial or empty tool-selection fallback in `tests/integration/Runtime/WebMcpFallbackIntegrationTest.php`
- [X] T043 [P] [US3] Add unit tests for stable discovery fallback reason mapping in `tests/unit/Runtime/ApiCatalogRequestMatcherTest.php` and `tests/unit/Runtime/WebMcpToolResolverTest.php`

### Implementation for User Story 3

- [X] T044 [US3] Implement physical-file conflict short-circuit behavior in `src/Application/Runtime/ApiCatalog/ApiCatalogRequestMatcher.php` and `src/Application/Runtime/ApiCatalog/ApiCatalogRuntimeHandler.php`
- [X] T045 [US3] Implement disabled-feature and no-tools short-circuit behavior in `src/Application/Runtime/WebMcp/WebMcpRuntimeEmitter.php` and `src/Application/Runtime/WebMcp/WebMcpToolResolver.php`
- [X] T046 [US3] Extend `assets/js/webmcp-runtime.js` so unsupported browsers exit without registration or unhandled runtime errors
- [X] T047 [US3] Surface stable discovery compatibility outcomes through `src/Application/Compatibility/EnvironmentDetector.php` and `src/Application/Runtime/RuntimeCompatibilityGateway.php`
- [X] T048 [US3] Ensure discovery runtime paths never mutate physical files or override host-default behavior in `src/Application/Runtime/ApiCatalog/ApiCatalogRuntimeHandler.php`
- [X] T049 [US3] Update fallback validation guidance in `specs/004-phase1-discovery-runtime/quickstart.md`

**Checkpoint**: All three user stories are complete when discovery output is stable and every incompatibility path degrades safely.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Harden discovery runtime behavior, update shared docs, and finish regression coverage across stories.

- [X] T050 [P] Add i18n wrappers for any new runtime discovery strings in `src/Application/Runtime/ApiCatalog/`, `src/Application/Runtime/WebMcp/`, and `assets/js/webmcp-runtime.js`
- [X] T051 Add security and performance hardening for discovery routing, response emission, and asset enqueue behavior in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`, `src/Application/Runtime/ApiCatalog/ApiCatalogResponseWriter.php`, and `src/Application/Runtime/WebMcp/WebMcpRuntimeEmitter.php`
- [X] T052 [P] Add cross-story regression coverage for hook registration, public discovery contracts, and fallback behavior in `tests/integration/Runtime/RuntimeHookRegistrationTest.php`, `tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php`, and `tests/integration/Runtime/WebMcpFallbackIntegrationTest.php`
- [X] T053 Add feature documentation updates in `readme.txt` and `specs/004-phase1-discovery-runtime/quickstart.md`
- [X] T054 Run the validation scenarios from `specs/004-phase1-discovery-runtime/quickstart.md` and record outcomes in `specs/004-phase1-discovery-runtime/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; recommended MVP cut.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and reuses the runtime hook/enqueue scaffolding from Phase 2.
- **User Story 3 (Phase 5)**: Depends on the concrete runtime paths from User Stories 1 and 2 so fallback behavior can be verified against real output.
- **Polish (Phase 6)**: Depends on completion of the desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: No dependency on other stories after Foundational completion.
- **User Story 2 (P2)**: No hard dependency on API Catalog business logic, but it shares the same runtime hook and compatibility infrastructure.
- **User Story 3 (P3)**: Depends on the API Catalog and WebMCP runtime paths being in place so their compatibility branches can be verified.

### Within Each User Story

- Tests MUST be written and fail before implementation begins.
- Shared resolution logic should land before response writers and runtime handlers that consume it.
- Hook/enqueue integration should be completed before documentation and polish tasks.
- Story contracts and fallback guidance should be updated before the story is considered complete.

### Parallel Opportunities

- T002-T004 can run in parallel during Setup.
- T007-T010 can run in parallel during Foundational once the module paths exist.
- Tests inside each story phase marked `[P]` can run in parallel.
- US1 implementation tasks T020-T023 can run in parallel once the route design is fixed.
- US2 implementation tasks T032-T035 can run in parallel.
- Polish tasks T050 and T052 can run in parallel.

---

## Parallel Example: User Story 1

```bash
# Launch User Story 1 tests together:
Task: "Add integration test for the enabled API Catalog response contract in tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php"
Task: "Add integration test for WooCommerce-conditional API Catalog entries in tests/integration/Runtime/ApiCatalogEndpointIntegrationTest.php"
Task: "Add unit tests for Linkset JSON document shaping in tests/unit/Runtime/ApiCatalogDocumentBuilderTest.php"
Task: "Add unit tests for route matching and eligibility decisions in tests/unit/Runtime/ApiCatalogRequestMatcherTest.php"

# Launch independent implementation work together:
Task: "Implement API Catalog entry normalization in src/Application/Runtime/ApiCatalog/ApiCatalogEntryFactory.php"
Task: "Implement Linkset JSON document building in src/Application/Runtime/ApiCatalog/ApiCatalogDocumentBuilder.php"
Task: "Implement API Catalog request matching and resolution in src/Application/Runtime/ApiCatalog/ApiCatalogRequestMatcher.php"
```

---

## Parallel Example: User Story 2

```bash
# Launch User Story 2 tests together:
Task: "Add integration test for WebMCP runtime asset emission on public frontend pages in tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php"
Task: "Add integration test for WooCommerce-conditional tool exposure in tests/integration/Runtime/WebMcpRuntimeIntegrationTest.php"
Task: "Add unit tests for settings-driven tool resolution in tests/unit/Runtime/WebMcpToolResolverTest.php"
Task: "Add unit tests for frontend payload shaping in tests/unit/Runtime/WebMcpPayloadBuilderTest.php"

# Launch independent implementation work together:
Task: "Implement WebMCP tool resolution from saved settings and compatibility state in src/Application/Runtime/WebMcp/WebMcpToolResolver.php"
Task: "Implement frontend payload building for localized runtime data in src/Application/Runtime/WebMcp/WebMcpPayloadBuilder.php"
Task: "Create the public runtime asset with capability detection and registration wiring in assets/js/webmcp-runtime.js"
```

---

## Parallel Example: User Story 3

```bash
# Launch User Story 3 tests together:
Task: "Add integration test for physical API Catalog file-conflict fallback in tests/integration/Runtime/ApiCatalogFallbackIntegrationTest.php"
Task: "Add integration test for unsupported-browser WebMCP no-op behavior in tests/integration/Runtime/WebMcpFallbackIntegrationTest.php"
Task: "Add unit tests for stable discovery fallback reason mapping in tests/unit/Runtime/ApiCatalogRequestMatcherTest.php and tests/unit/Runtime/WebMcpToolResolverTest.php"

# Launch independent implementation work together:
Task: "Implement physical-file conflict short-circuit behavior in src/Application/Runtime/ApiCatalog/ApiCatalogRequestMatcher.php and src/Application/Runtime/ApiCatalog/ApiCatalogRuntimeHandler.php"
Task: "Implement disabled-feature and no-tools short-circuit behavior in src/Application/Runtime/WebMcp/WebMcpRuntimeEmitter.php and src/Application/Runtime/WebMcp/WebMcpToolResolver.php"
Task: "Extend assets/js/webmcp-runtime.js so unsupported browsers exit without registration or unhandled runtime errors"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate the API Catalog endpoint independently.
5. Demo or ship the API Catalog discovery contract as the MVP increment.

### Incremental Delivery

1. Setup + Foundational -> discovery runtime infrastructure ready.
2. Add User Story 1 -> API Catalog endpoint available.
3. Add User Story 2 -> WebMCP public runtime available.
4. Add User Story 3 -> compatibility-safe fallbacks and diagnostics complete.
5. Complete Polish phase for regression hardening and documentation.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. After Foundational completion:
   - Developer A: US1 API Catalog runtime contract.
   - Developer B: US2 WebMCP runtime contract.
   - Developer C: US3 compatibility and fallback behavior.
3. Integrate and run cross-story regression in Phase 6.

---

## Notes

- `[P]` tasks target disjoint files and can run concurrently.
- `[US*]` labels map each task to its independently testable story.
- Keep runtime scope limited to F4 API Catalog and F5 WebMCP for this feature branch.
- Verify test tasks fail before implementation tasks.
- Preserve default WordPress and host behavior in all disabled and conflict paths.
