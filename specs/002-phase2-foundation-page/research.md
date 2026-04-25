# Research: Phase 2 Foundation and Architecture Page

## Decision 1: Extend the existing Agent Ready page instead of creating a second admin page

**Decision**: Add Phase 2 configuration areas to the existing `Settings > Agent Ready`
page rather than building a separate administration screen.

**Rationale**: The clarified spec requires a continuous experience with the
same shared page-level save flow. Reusing the existing page also matches the
current implementation already present in the repository.

**Alternatives considered**:

- Separate Phase 2 page: rejected because it breaks continuity and introduces a
  second save/navigation workflow.
- Modal or wizard flow: rejected because the current plugin interaction model is
  a server-rendered settings page, not a guided multi-step interface.

## Decision 2: Use an explicit protected-API applicability toggle

**Decision**: Add a dedicated administrator-controlled setting that declares
whether the site has protected APIs.

**Rationale**: Applicability should not be guessed from partial field values.
An explicit toggle gives predictable disabled/enabled states for the OAuth and
protected-resource sections and reduces ambiguous validation outcomes.

**Alternatives considered**:

- Infer applicability from filled fields: rejected because it makes state
  detection opaque and error-prone.
- Always treat protected APIs as applicable: rejected because many sites will
  not need those fields yet.

## Decision 3: Keep OAuth and protected-resource sections visible but disabled when not applicable

**Decision**: When the protected-API toggle is off, render the OAuth and
protected-resource sections in a disabled state with explanatory text.

**Rationale**: This mirrors the established Phase 1 pattern for unavailable
controls and preserves discoverability of future-relevant settings.

**Alternatives considered**:

- Hide the sections entirely: rejected because it obscures what becomes
  available later.
- Leave sections editable regardless of applicability: rejected because it
  permits misleading configuration.

## Decision 4: Keep a single shared save flow for Phase 1 and Phase 2

**Decision**: Phase 2 settings are saved through the same page-level save action
already used by the existing page.

**Rationale**: The clarified spec explicitly chose a shared save model. This
also avoids introducing two competing save semantics on a single page.

**Alternatives considered**:

- Separate Phase 2 save button: rejected because it complicates the page and
  risks inconsistent save expectations.
- Dual save modes: rejected because it increases UX and validation complexity
  without clear value.

## Decision 5: Treat previews as draft output only

**Decision**: Show readable pre-save previews for Phase 2 metadata, but do not
change published values until the shared save succeeds.

**Rationale**: Administrators need preview confidence, but the page must not
imply live publication before validation and save are complete.

**Alternatives considered**:

- Immediate live preview as publication: rejected because it blurs draft and
  saved state.
- No preview: rejected because the spec requires readable metadata summaries.

## Decision 6: Reject invalid saves in place and preserve entered values

**Decision**: On invalid Phase 2 input, keep the administrator on the page,
preserve entered values, and show targeted validation messages.

**Rationale**: This is the safest behavior for configuration that can describe
protected API infrastructure. Partial saves could create confusing metadata, and
resetting the form would create unnecessary friction.

**Alternatives considered**:

- Partial save of valid fields only: rejected because it can leave published
  discovery metadata in an inconsistent state.
- Full reset to last saved values: rejected because it discards administrator
  effort and slows correction.

## Decision 7: Extend the existing settings option schema

**Decision**: Add Phase 2 keys to the existing `agent_ready_wp_settings` array
for protected API applicability, MCP Server Card fields, OAuth discovery fields,
and protected-resource fields.

**Rationale**: The project already uses one namespaced option array, and the
constitution favors that model unless a new persistence layer is justified.

**Alternatives considered**:

- Separate option for Phase 2 only: rejected because it fragments related
  configuration owned by one page.
- Custom table: rejected because the data remains flat, low-volume, and
  administrator-managed.
