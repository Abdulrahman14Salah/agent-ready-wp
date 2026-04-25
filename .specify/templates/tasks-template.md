---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for public integration boundaries,
security-sensitive behavior, option migrations, and pure transformation logic.
Only documentation-only or repo-maintenance tasks may omit tests.

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

<!-- 
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.
  
  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/
  
  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment
  
  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create the WordPress plugin structure defined in plan.md
- [ ] T002 Add the plugin bootstrap file, lifecycle hooks, and uninstall entry
- [ ] T003 [P] Configure linting, formatting, and WordPress coding standards
- [ ] T004 [P] Configure PHPUnit and WordPress test bootstrap

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T005 Register core settings schema, defaults, and migration-safe option access
- [ ] T006 [P] Build shared sanitization, escaping, nonce, and capability helpers
- [ ] T007 [P] Establish shared hook/rewrite registration infrastructure
- [ ] T008 Create admin notice or error reporting infrastructure for graceful degradation
- [ ] T009 Document compatibility constraints and test fixtures for WP, CPT, and WooCommerce paths

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T010 [P] [US1] Integration test for the public WordPress contract in tests/integration/
- [ ] T011 [P] [US1] Unit test for pure transformation or validation logic in tests/unit/

### Implementation for User Story 1

- [ ] T012 [P] [US1] Implement shared data structures or view models in `src/`
- [ ] T013 [US1] Implement the WordPress hook, endpoint, or settings behavior in `src/`
- [ ] T014 [US1] Add sanitization, escaping, capability, and nonce enforcement
- [ ] T015 [US1] Add graceful fallback behavior and compatibility handling
- [ ] T016 [US1] Wire assets or inline script behavior through enqueue APIs where needed
- [ ] T017 [US1] Update docs or compatibility notes for this story

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 2 ⚠️

- [ ] T018 [P] [US2] Integration test for the public WordPress contract in tests/integration/
- [ ] T019 [P] [US2] Unit test for pure transformation or validation logic in tests/unit/

### Implementation for User Story 2

- [ ] T020 [P] [US2] Extend shared plugin structures in `src/`
- [ ] T021 [US2] Implement the WordPress hook, endpoint, or settings behavior in `src/`
- [ ] T022 [US2] Add compatibility and graceful-degradation handling
- [ ] T023 [US2] Integrate with prior stories without changing their public contracts

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 3 ⚠️

- [ ] T024 [P] [US3] Integration test for the public WordPress contract in tests/integration/
- [ ] T025 [P] [US3] Unit test for pure transformation or validation logic in tests/unit/

### Implementation for User Story 3

- [ ] T026 [P] [US3] Extend shared plugin structures in `src/`
- [ ] T027 [US3] Implement the WordPress hook, endpoint, or settings behavior in `src/`
- [ ] T028 [US3] Add compatibility, fallback, and cleanup behavior

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Additional regression tests in tests/unit/ and tests/integration/
- [ ] TXXX Security hardening
- [ ] TXXX Internationalization and WordPress.org review pass
- [ ] TXXX Run quickstart.md validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Shared WordPress infrastructure before story-specific hooks or endpoints
- Data structures and validation before request handlers and rendering
- Core implementation before compatibility polish
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- All tests for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together (if tests requested):
Task: "Integration test for the public WordPress contract in tests/integration/"
Task: "Unit test for pure transformation or validation logic in tests/unit/"

# Launch independent implementation work for User Story 1 together:
Task: "Implement shared data structures or view models in src/"
Task: "Implement the WordPress hook, endpoint, or settings behavior in src/"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing
- Include tasks for security, graceful degradation, uninstall/migration impact,
  and compatibility claims when the story changes those behaviors
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
