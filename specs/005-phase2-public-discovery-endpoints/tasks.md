---

description: "Task list for implementing Phase 2 Public Discovery Endpoints"
---

# Tasks: Phase 2 Public Discovery Endpoints

**Input**: Design documents from `/specs/005-phase2-public-discovery-endpoints/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for all three public JSON endpoint contracts, per-endpoint eligibility decisions, physical-file conflict fallbacks, and shared runtime registration behavior.

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

**Purpose**: Create Phase 2 endpoint test files, fixtures, and module scaffolding before changing runtime behavior.

- [X] T001 Create Phase 2 endpoint integration test files in `tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php`, `tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php`, and `tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php`
- [X] T002 [P] Create Phase 2 endpoint unit test files in `tests/unit/Runtime/McpServerCardDocumentBuilderTest.php`, `tests/unit/Runtime/McpServerCardRequestMatcherTest.php`, `tests/unit/Runtime/OAuthDiscoveryDocumentBuilderTest.php`, `tests/unit/Runtime/OAuthDiscoveryRequestMatcherTest.php`, `tests/unit/Runtime/ProtectedResourceDocumentBuilderTest.php`, and `tests/unit/Runtime/ProtectedResourceRequestMatcherTest.php`
- [X] T003 [P] Extend `tests/bootstrap.php` with `.well-known` request helpers, Phase 2 settings presets, and physical-file conflict helpers for the new endpoints
- [X] T004 [P] Add endpoint-response fixtures in `tests/fixtures/mcp-server-card-response.json`, `tests/fixtures/oauth-discovery-response.json`, and `tests/fixtures/protected-resource-response.json`
- [X] T005 Create runtime module directories and placeholder files under `src/Application/Runtime/McpServerCard/`, `src/Application/Runtime/OAuthDiscovery/`, and `src/Application/Runtime/ProtectedResource/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the shared runtime accessors, compatibility detection, and registration seams required by all Phase 2 endpoints.

**⚠️ CRITICAL**: No user story work should begin until this phase is complete.

- [X] T006 Extend `src/Application/Runtime/RuntimeFeatureSettingsGateway.php` with `getMcpServerCardSettings()`, `getProtectedApisSettings()`, `getOAuthSettings()`, and `getProtectedResourceSettings()`
- [X] T007 [P] Extend `src/Application/Compatibility/EnvironmentDetector.php` with physical-file conflict detection and warnings for `/.well-known/mcp/server-card.json`, `/.well-known/openid-configuration`, and `/.well-known/oauth-protected-resource`
- [X] T008 [P] Extend `src/Application/Runtime/RuntimeCompatibilityGateway.php` with `getMcpServerCardCompatibility()`, `getOAuthDiscoveryCompatibility()`, and `getProtectedResourceCompatibility()`
- [X] T009 [P] Add Phase 2 compatibility unit coverage in `tests/unit/Compatibility/EnvironmentDetectorTest.php` for the new `.well-known` conflict states
- [X] T010 [P] Add gateway coverage for Phase 2 settings and compatibility accessors in `tests/unit/Runtime/McpServerCardRequestMatcherTest.php`, `tests/unit/Runtime/OAuthDiscoveryRequestMatcherTest.php`, and `tests/unit/Runtime/ProtectedResourceRequestMatcherTest.php`
- [X] T011 [P] Extend `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` constructor and registration seams to accept additional Phase 2 endpoint handlers
- [X] T012 Wire the shared Phase 2 runtime dependencies in `src/Infrastructure/WordPress/Hooks.php` alongside the existing Phase 1 runtime modules
- [X] T013 Add foundational registration coverage for the new Phase 2 hooks in `tests/integration/Runtime/RuntimeHookRegistrationTest.php`

**Checkpoint**: Shared runtime infrastructure is ready; each endpoint story can now be implemented and tested independently.

---

## Phase 3: User Story 1 - Publish MCP Server Card (Priority: P1) 🎯 MVP

**Goal**: Publish a machine-readable `/.well-known/mcp/server-card.json` endpoint from saved MCP Server Card settings and active runtime capabilities.

**Independent Test**: Enable valid MCP Server Card settings, request `/.well-known/mcp/server-card.json`, and verify a successful JSON response containing the saved identity fields plus active capability metadata.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T014 [P] [US1] Add integration test for successful MCP Server Card publication in `tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php`
- [X] T015 [P] [US1] Add integration test for disabled or incomplete MCP Server Card fallback in `tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php`
- [X] T016 [P] [US1] Add integration test for physical `/.well-known/mcp/server-card.json` conflict fallback in `tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php`
- [X] T017 [P] [US1] Add unit tests for MCP Server Card document shaping and capability output in `tests/unit/Runtime/McpServerCardDocumentBuilderTest.php`
- [X] T018 [P] [US1] Add unit tests for MCP Server Card route eligibility and reason codes in `tests/unit/Runtime/McpServerCardRequestMatcherTest.php`

### Implementation for User Story 1

- [X] T019 [P] [US1] Implement capability derivation for MCP Server Card output in `src/Application/Runtime/McpServerCard/McpServerCardCapabilityResolver.php`
- [X] T020 [P] [US1] Implement MCP Server Card request-context loading in `src/Application/Runtime/McpServerCard/McpServerCardRequestContextFactory.php`
- [X] T021 [P] [US1] Implement MCP Server Card resolution decisions in `src/Application/Runtime/McpServerCard/McpServerCardResolutionDecision.php`
- [X] T022 [P] [US1] Implement MCP Server Card document building and JSON response writing in `src/Application/Runtime/McpServerCard/McpServerCardDocumentBuilder.php` and `src/Application/Runtime/McpServerCard/McpServerCardResponseWriter.php`
- [X] T023 [US1] Implement MCP Server Card route matching and runtime handling in `src/Application/Runtime/McpServerCard/McpServerCardRequestMatcher.php` and `src/Application/Runtime/McpServerCard/McpServerCardRuntimeHandler.php`
- [X] T024 [US1] Register the MCP Server Card rewrite, query var, and request handler in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` and `src/Infrastructure/WordPress/Hooks.php`
- [X] T025 [US1] Update the public MCP Server Card contract notes in `specs/005-phase2-public-discovery-endpoints/contracts/mcp-server-card-contract.md`

**Checkpoint**: User Story 1 is complete when the MCP Server Card endpoint works independently and publishes the expected saved identity plus capability metadata.

---

## Phase 4: User Story 2 - Publish OAuth/OIDC Discovery Metadata (Priority: P2)

**Goal**: Publish `/.well-known/openid-configuration` only when protected APIs are applicable and the saved OAuth discovery values are complete.

**Independent Test**: Enable protected APIs and valid OAuth settings, request `/.well-known/openid-configuration`, and verify the saved issuer, authorization endpoint, token endpoint, and JWKS URI are returned.

### Tests for User Story 2 ⚠️

- [X] T026 [P] [US2] Add integration test for successful OAuth discovery publication in `tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php`
- [X] T027 [P] [US2] Add integration test for protected-API-disabled or OAuth-disabled fallback in `tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php`
- [X] T028 [P] [US2] Add integration test for incomplete OAuth settings fallback in `tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php`
- [X] T029 [P] [US2] Add integration test for physical `/.well-known/openid-configuration` conflict fallback in `tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php`
- [X] T030 [P] [US2] Add unit tests for OAuth discovery document shaping in `tests/unit/Runtime/OAuthDiscoveryDocumentBuilderTest.php`
- [X] T031 [P] [US2] Add unit tests for OAuth discovery eligibility and reason codes in `tests/unit/Runtime/OAuthDiscoveryRequestMatcherTest.php`

### Implementation for User Story 2

- [X] T032 [P] [US2] Implement OAuth discovery request-context loading in `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryRequestContextFactory.php`
- [X] T033 [P] [US2] Implement OAuth discovery resolution decisions in `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryResolutionDecision.php`
- [X] T034 [P] [US2] Implement OAuth discovery document building and JSON response writing in `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryDocumentBuilder.php` and `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryResponseWriter.php`
- [X] T035 [US2] Implement OAuth discovery route matching and runtime handling in `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryRequestMatcher.php` and `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryRuntimeHandler.php`
- [X] T036 [US2] Register the OAuth discovery rewrite, query var, and request handler in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` and `src/Infrastructure/WordPress/Hooks.php`
- [X] T037 [US2] Update the public OAuth discovery contract notes in `specs/005-phase2-public-discovery-endpoints/contracts/oauth-discovery-contract.md`

**Checkpoint**: User Stories 1 and 2 are complete when both the MCP Server Card and OAuth discovery endpoints work independently and preserve fallback behavior.

---

## Phase 5: User Story 3 - Publish Protected Resource Metadata Safely (Priority: P3)

**Goal**: Publish `/.well-known/oauth-protected-resource` only when the saved protected-resource configuration is complete and safe to expose.

**Independent Test**: Enable protected APIs and complete protected-resource settings, request `/.well-known/oauth-protected-resource`, and verify the saved resource identifier and authorization server list are returned only in the valid case.

### Tests for User Story 3 ⚠️

- [X] T038 [P] [US3] Add integration test for successful Protected Resource publication in `tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php`
- [X] T039 [P] [US3] Add integration test for protected-API-disabled or feature-disabled fallback in `tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php`
- [X] T040 [P] [US3] Add integration test for incomplete Protected Resource settings fallback in `tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php`
- [X] T041 [P] [US3] Add integration test for physical `/.well-known/oauth-protected-resource` conflict fallback in `tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php`
- [X] T042 [P] [US3] Add unit tests for Protected Resource document shaping in `tests/unit/Runtime/ProtectedResourceDocumentBuilderTest.php`
- [X] T043 [P] [US3] Add unit tests for Protected Resource eligibility and reason codes in `tests/unit/Runtime/ProtectedResourceRequestMatcherTest.php`

### Implementation for User Story 3

- [X] T044 [P] [US3] Implement Protected Resource request-context loading in `src/Application/Runtime/ProtectedResource/ProtectedResourceRequestContextFactory.php`
- [X] T045 [P] [US3] Implement Protected Resource resolution decisions in `src/Application/Runtime/ProtectedResource/ProtectedResourceResolutionDecision.php`
- [X] T046 [P] [US3] Implement Protected Resource document building and JSON response writing in `src/Application/Runtime/ProtectedResource/ProtectedResourceDocumentBuilder.php` and `src/Application/Runtime/ProtectedResource/ProtectedResourceResponseWriter.php`
- [X] T047 [US3] Implement Protected Resource route matching and runtime handling in `src/Application/Runtime/ProtectedResource/ProtectedResourceRequestMatcher.php` and `src/Application/Runtime/ProtectedResource/ProtectedResourceRuntimeHandler.php`
- [X] T048 [US3] Register the Protected Resource rewrite, query var, and request handler in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php` and `src/Infrastructure/WordPress/Hooks.php`
- [X] T049 [US3] Update the public Protected Resource contract notes in `specs/005-phase2-public-discovery-endpoints/contracts/protected-resource-contract.md`

**Checkpoint**: All three Phase 2 endpoints are independently functional and withhold output safely when their own configuration is disabled, incomplete, or conflicting.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Finish documentation, harden shared response behavior, and add regression coverage for endpoint coexistence.

- [X] T050 [P] Add coexistence and non-interference regression coverage in `tests/integration/Runtime/RuntimeHookRegistrationTest.php` and `tests/integration/Runtime/PhaseTwoDiscoveryCoexistenceIntegrationTest.php`
- [X] T051 Add shared JSON response hardening and no-file-mutation guards in `src/Application/Runtime/McpServerCard/McpServerCardResponseWriter.php`, `src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryResponseWriter.php`, `src/Application/Runtime/ProtectedResource/ProtectedResourceResponseWriter.php`, and the three `*RuntimeHandler.php` files
- [X] T052 [P] Update the implementation-facing validation guide in `specs/005-phase2-public-discovery-endpoints/quickstart.md`
- [X] T053 [P] Update public feature documentation for the new `.well-known` endpoints in `readme.txt`
- [X] T054 Run the validation scenarios from `specs/005-phase2-public-discovery-endpoints/quickstart.md` and record outcomes in `specs/005-phase2-public-discovery-endpoints/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; recommended MVP cut.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and reuses the shared Phase 2 settings and compatibility infrastructure.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and follows the same runtime pattern as User Story 2 while remaining independently testable.
- **Polish (Phase 6)**: Depends on completion of the desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: No dependency on other stories after Foundational completion.
- **User Story 2 (P2)**: No hard dependency on User Story 1 business logic, but it should follow the MCP Server Card pattern for consistency.
- **User Story 3 (P3)**: No hard dependency on User Story 2 business logic, but it benefits from the established OAuth discovery runtime pattern and shared protected-API gating.

### Within Each User Story

- Tests MUST be written and fail before implementation begins.
- Request-context loading and decision objects should land before document builders and runtime handlers that consume them.
- Runtime handler wiring should be completed before story-specific contract documentation updates.
- Each story is complete only when the endpoint contract and its fallback behavior are both verified.

### Parallel Opportunities

- T002-T004 can run in parallel during Setup.
- T007-T011 can run in parallel during Foundational once the module paths exist.
- Tests inside each user story phase marked `[P]` can run in parallel.
- US1 implementation tasks T019-T022 can run in parallel once the contract shape is fixed.
- US2 implementation tasks T032-T034 can run in parallel.
- US3 implementation tasks T044-T046 can run in parallel.
- Polish tasks T050, T052, and T053 can run in parallel.

---

## Parallel Example: User Story 1

```bash
# Launch User Story 1 tests together:
Task: "Add integration test for successful MCP Server Card publication in tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php"
Task: "Add integration test for disabled or incomplete MCP Server Card fallback in tests/integration/Runtime/McpServerCardEndpointIntegrationTest.php"
Task: "Add unit tests for MCP Server Card document shaping and capability output in tests/unit/Runtime/McpServerCardDocumentBuilderTest.php"
Task: "Add unit tests for MCP Server Card route eligibility and reason codes in tests/unit/Runtime/McpServerCardRequestMatcherTest.php"

# Launch independent implementation work together:
Task: "Implement capability derivation for MCP Server Card output in src/Application/Runtime/McpServerCard/McpServerCardCapabilityResolver.php"
Task: "Implement MCP Server Card request-context loading in src/Application/Runtime/McpServerCard/McpServerCardRequestContextFactory.php"
Task: "Implement MCP Server Card document building and JSON response writing in src/Application/Runtime/McpServerCard/McpServerCardDocumentBuilder.php and src/Application/Runtime/McpServerCard/McpServerCardResponseWriter.php"
```

---

## Parallel Example: User Story 2

```bash
# Launch User Story 2 tests together:
Task: "Add integration test for successful OAuth discovery publication in tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php"
Task: "Add integration test for incomplete OAuth settings fallback in tests/integration/Runtime/OAuthDiscoveryEndpointIntegrationTest.php"
Task: "Add unit tests for OAuth discovery document shaping in tests/unit/Runtime/OAuthDiscoveryDocumentBuilderTest.php"
Task: "Add unit tests for OAuth discovery eligibility and reason codes in tests/unit/Runtime/OAuthDiscoveryRequestMatcherTest.php"

# Launch independent implementation work together:
Task: "Implement OAuth discovery request-context loading in src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryRequestContextFactory.php"
Task: "Implement OAuth discovery resolution decisions in src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryResolutionDecision.php"
Task: "Implement OAuth discovery document building and JSON response writing in src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryDocumentBuilder.php and src/Application/Runtime/OAuthDiscovery/OAuthDiscoveryResponseWriter.php"
```

---

## Parallel Example: User Story 3

```bash
# Launch User Story 3 tests together:
Task: "Add integration test for successful Protected Resource publication in tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php"
Task: "Add integration test for physical /.well-known/oauth-protected-resource conflict fallback in tests/integration/Runtime/ProtectedResourceEndpointIntegrationTest.php"
Task: "Add unit tests for Protected Resource document shaping in tests/unit/Runtime/ProtectedResourceDocumentBuilderTest.php"
Task: "Add unit tests for Protected Resource eligibility and reason codes in tests/unit/Runtime/ProtectedResourceRequestMatcherTest.php"

# Launch independent implementation work together:
Task: "Implement Protected Resource request-context loading in src/Application/Runtime/ProtectedResource/ProtectedResourceRequestContextFactory.php"
Task: "Implement Protected Resource resolution decisions in src/Application/Runtime/ProtectedResource/ProtectedResourceResolutionDecision.php"
Task: "Implement Protected Resource document building and JSON response writing in src/Application/Runtime/ProtectedResource/ProtectedResourceDocumentBuilder.php and src/Application/Runtime/ProtectedResource/ProtectedResourceResponseWriter.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate `/.well-known/mcp/server-card.json` independently.
5. Demo or ship the MCP Server Card endpoint as the MVP increment.

### Incremental Delivery

1. Setup + Foundational -> Phase 2 runtime infrastructure ready.
2. Add User Story 1 -> MCP Server Card endpoint available.
3. Add User Story 2 -> OAuth discovery endpoint available.
4. Add User Story 3 -> Protected Resource endpoint available.
5. Complete Polish phase for coexistence regression coverage and documentation.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. After Foundational completion:
   - Developer A: US1 MCP Server Card runtime contract.
   - Developer B: US2 OAuth discovery runtime contract.
   - Developer C: US3 Protected Resource runtime contract.
3. Integrate and run cross-story regression in Phase 6.

---

## Notes

- `[P]` tasks target disjoint files and can run concurrently.
- `[US*]` labels map each task to its independently testable story.
- Keep runtime scope limited to F6 MCP Server Card, F7 OAuth/OIDC discovery, and F8 OAuth Protected Resource for this branch.
- Verify test tasks fail before implementation tasks.
- Preserve default WordPress and host behavior in all disabled, incomplete, and physical-file conflict paths.
