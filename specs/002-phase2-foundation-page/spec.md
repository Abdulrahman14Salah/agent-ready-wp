# Feature Specification: Phase 2 Foundation and Architecture Page

**Feature Branch**: `002-phase2-foundation-page`  
**Created**: 2026-04-23  
**Status**: Draft  
**Input**: User description: "Read PLAN.md and create a specification for the phase 2: Foundation and Architecture page ONLY."

## Clarifications

### Session 2026-04-23

- Q: When protected APIs are not applicable, should the OAuth and Protected Resource sections be hidden, disabled with explanation, or remain fully editable? → A: The OAuth and Protected Resource sections remain visible in a disabled state with an explanation.
- Q: How should the page determine whether protected API settings are applicable? → A: The page includes an explicit administrator-controlled toggle indicating whether the site has protected APIs.
- Q: Should Phase 2 settings use a separate save action or the same shared page-level save as Phase 1? → A: The page keeps one shared page-level save action for both Phase 1 and Phase 2 settings.
- Q: Should the page preview Phase 2 metadata before save, and when do published values actually change? → A: The page shows a readable preview before save, but only published values change after a successful save.
- Q: How should the page handle invalid Phase 2 values during save? → A: The page rejects the save, preserves entered values, and shows targeted validation feedback on the page.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Configure Discovery Metadata (Priority: P1)

As a site administrator, I want to configure the Phase 2 discovery metadata from
the Agent Ready page so I can publish additional machine-readable information
without editing code or server files.

**Why this priority**: Phase 2 begins by expanding what the site advertises to
agents. Discovery metadata is the highest-value addition because it exposes
server identity and protected-resource information that administrators must
control explicitly.

**Independent Test**: Open the Phase 2 sections of the page, enter valid server
card and related discovery values, save once, reload the page, and confirm the
entered values persist and remain understandable.

**Acceptance Scenarios**:

1. **Given** the administrator opens the Agent Ready page with Phase 2
   capabilities available, **When** they review the page, **Then** they can
   find a dedicated configuration section for the MCP Server Card.
2. **Given** the administrator enters valid MCP Server Card details, **When**
   they save the page, **Then** those values persist and are visible on the next
   page load.
3. **Given** the administrator has not configured Phase 2 discovery metadata,
   **When** they view the page, **Then** the section clearly communicates what
   is missing and what still needs configuration.

---

### User Story 2 - Configure OAuth Discovery Details (Priority: P2)

As a site administrator, I want to enter the OAuth and OpenID discovery details
for my protected APIs so I can expose a coherent protected-resource setup
through the same page.

**Why this priority**: Protected-resource configuration depends on the
administrator supplying correct issuer and endpoint details. It is valuable only
after the discovery area itself is available.

**Independent Test**: Enter valid OAuth discovery values, save the page, reload
it, and confirm the values persist and are presented in a clear grouped form.

**Acceptance Scenarios**:

1. **Given** the site has protected APIs that require discovery metadata,
   **When** the administrator opens the Phase 2 section, **Then** the page shows
   a clear protected-API applicability control and grouped fields for issuer and
   related OAuth discovery details.
2. **Given** the administrator provides valid OAuth discovery information,
   **When** they save the page, **Then** the information persists and remains
   clearly associated with the correct protected-resource configuration area.
3. **Given** the administrator provides incomplete or inconsistent protected API
   information, **When** they attempt to save, **Then** the page stays in
   context, preserves the entered values, and makes it clear what must be
   corrected.
4. **Given** the administrator changes both existing page settings and new
   Phase 2 settings, **When** they save the page, **Then** those changes are
   handled through the same shared page-level save flow.

---

### User Story 3 - Understand Phase 2 Readiness and Dependencies (Priority: P3)

As a site administrator, I want the page to explain when Phase 2 settings apply
and what dependencies or prerequisites affect them so I can avoid configuring
discovery metadata that does not match my site.

**Why this priority**: Phase 2 introduces more configuration complexity than
Phase 1. Clear dependency guidance reduces configuration errors and avoids
publishing misleading metadata.

**Independent Test**: View the page under applicable and non-applicable Phase 2
conditions and confirm the page explains what is available, what is optional,
and what prerequisites or limitations still apply.

**Acceptance Scenarios**:

1. **Given** Phase 2 configuration is relevant to the site, **When** the
   administrator reviews the page, **Then** the page explains what each Phase 2
   section represents and when it should be used.
2. **Given** a Phase 2 section depends on protected APIs or other prerequisites,
   **When** those prerequisites are absent or incomplete, **Then** the page
   shows the relevant sections in a disabled state with a clear explanation
   rather than hiding them.
3. **Given** the administrator has already completed Phase 1 settings, **When**
   they review the expanded page, **Then** the new Phase 2 sections appear as an
   extension of the existing experience rather than a disconnected workflow.

### Edge Cases

- The administrator opens the page before any Phase 2 values have been entered,
  so the page must make the missing information obvious without implying the
  site is already fully configured.
- The site does not expose protected APIs, so the page must explain when OAuth
  discovery information is not currently applicable and must keep those related
  sections visible but disabled with an explanation.
- The administrator saves only part of the Phase 2 configuration, so the page
  must preserve already valid values while keeping missing requirements visible.
- The administrator enters values that appear inconsistent with one another, so
  the page must reject the save, preserve entered values, and prevent silent
  acceptance of confusing or incomplete discovery metadata.
- The page contains both existing Phase 1 settings and new Phase 2 settings, so
  the expanded experience must remain understandable rather than feeling like
  two unrelated tools.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST extend the existing Agent Ready settings page with
  dedicated Phase 2 configuration areas rather than creating a separate
  disconnected administration experience.
- **FR-002**: The page MUST provide a clearly labeled MCP Server Card section
  where the administrator can enter the server name, version, and transport
  information needed for Phase 2 discovery metadata.
- **FR-003**: The page MUST provide a clearly labeled OAuth and protected
  resource configuration area where the administrator can enter issuer and
  related discovery details when the site has protected APIs.
- **FR-003a**: The page MUST include an explicit administrator-controlled
  setting that indicates whether the site has protected APIs and uses that
  choice to determine whether the OAuth and protected-resource sections are
  active or disabled.
- **FR-004**: The page MUST persist valid Phase 2 settings and show the saved
  values on subsequent visits.
- **FR-005**: The page MUST make incomplete Phase 2 setup visible by clearly
  showing what required information is still missing.
- **FR-006**: The page MUST keep the administrator on the same page when Phase 2
  validation fails and explain what must be corrected.
- **FR-006a**: When Phase 2 validation fails, the page MUST preserve the
  administrator's entered values and present targeted validation feedback rather
  than partially saving valid fields or resetting the form to the last saved
  state.
- **FR-007**: The page MUST present Phase 2 settings in grouped sections that
  make it clear which values belong to discovery metadata, OAuth discovery, and
  protected-resource configuration.
- **FR-008**: The page MUST explain when OAuth discovery information is
  applicable and when it is not relevant because protected APIs are absent or
  not yet configured.
- **FR-008a**: When protected APIs are not applicable, the OAuth and protected
  resource configuration areas MUST remain visible in a disabled state with
  explanatory text rather than being hidden or presented as active.
- **FR-009**: The page MUST preserve the overall continuity of the existing
  Agent Ready experience so Phase 2 settings feel like an extension of the same
  page rather than a new workflow.
- **FR-010**: The page MUST restrict access to authorized site administrators
  and prevent unauthorized users from changing Phase 2 settings.
- **FR-011**: The page MUST allow administrators to review a readable summary or
  preview of the Phase 2 metadata they are configuring before leaving the page.
- **FR-011a**: The page MUST treat the preview as a draft representation of the
  pending configuration and MUST NOT imply that published Phase 2 metadata has
  changed until the shared save action succeeds.
- **FR-012**: The page MUST support a single save flow that includes the new
  Phase 2 settings without forcing the administrator into a separate save model.
- **FR-012a**: The page MUST use the same shared page-level save action for
  both existing settings and new Phase 2 settings rather than introducing a
  separate save workflow.

### Key Entities *(include if feature involves data)*

- **MCP Server Card Settings**: The administrator-provided server identity
  values used to describe the server in Phase 2 discovery metadata.
- **OAuth Discovery Settings**: The administrator-provided issuer and related
  discovery values for protected API scenarios.
- **Protected Resource Settings**: The administrator-provided values that define
  how the site points clients to the correct protected-resource metadata.
- **Protected API Applicability Setting**: The administrator-controlled value
  that declares whether protected APIs are present and therefore whether OAuth
  and protected-resource settings apply.
- **Phase 2 Applicability State**: The page-level context that explains whether
  protected API metadata is currently relevant, incomplete, or not applicable.
- **Phase 2 Preview Summary**: A readable representation of what the configured
  Phase 2 metadata will communicate once published, while still being clearly
  identified as a pre-save draft until the configuration is saved successfully.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In usability review, at least 90% of target administrators can
  identify where to configure MCP Server Card details within 1 minute of opening
  the page.
- **SC-002**: At least 90% of target administrators can enter and save a valid
  Phase 2 configuration from the page in under 5 minutes without needing code
  changes.
- **SC-003**: Saved Phase 2 values are retained and accurately shown on page
  reload in 100% of validation scenarios.
- **SC-004**: In all validation scenarios with incomplete or non-applicable
  protected API data, the page explains the limitation without making the
  broader Phase 2 experience confusing or unusable.

## Assumptions

- The existing Agent Ready page from Phase 1 already exists and this feature
  extends that same page rather than replacing it.
- The Phase 2 scope for this spec is limited to the page experience for MCP
  Server Card, OAuth discovery, and protected-resource configuration.
- Publishing and runtime handling of the underlying Phase 2 endpoints will be
  implemented separately from this page-focused specification.
- The target users remain site administrators managing a single-site WordPress
  installation during the current release scope.
