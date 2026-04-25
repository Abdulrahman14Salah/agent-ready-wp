# Feature Specification: Foundation and Architecture Page

**Feature Branch**: `001-foundation-architecture-page`  
**Created**: 2026-04-23  
**Status**: Draft  
**Input**: User description: "Read PLAN.md and create a specification for the phase 1: Foundation and Architecture page ONLY."

## Clarifications

### Session 2026-04-23

- Q: Should the page include all four Phase 1 configuration sections and show Phase 2 items as disabled placeholders, or should later-phase items be hidden? → A: The page includes the readiness summary, all four Phase 1 configuration sections, and Phase 2 items as visible "coming soon" placeholders.
- Q: When optional capabilities are unavailable, should their controls be hidden, disabled with explanation, or left active until save? → A: Unavailable optional controls remain visible in a disabled state with an explanation.
- Q: After a scan completes, should the page update the readiness summary immediately or require a later reload? → A: The page updates the readiness summary in place as soon as the new scan result is available.
- Q: Should Phase 1 settings save through one page-level action or separate per-section actions? → A: One page-level save action applies all configuration changes together.
- Q: Should running a scan save pending configuration changes, stay independent, or be blocked until save? → A: Running a scan is a separate action and does not save unsaved configuration changes.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Review Site Readiness (Priority: P1)

As a site administrator, I want to open one page and immediately understand my
site's current agent-readiness status so I can decide what to fix first.

**Why this priority**: The page has no value if it does not first explain the
current readiness state and guide the administrator toward the highest-impact
actions.

**Independent Test**: Open the page with and without prior scan results and
confirm the administrator can identify current status, score, category-level
results, and the next action to take.

**Acceptance Scenarios**:

1. **Given** the administrator has never run a scan, **When** they open the
   Foundation and Architecture page, **Then** they see a clear empty state that
   explains no scan is available yet and offers a prominent action to run one.
2. **Given** a prior scan result exists, **When** the administrator opens the
   page, **Then** they see the latest score, readiness level, scan timestamp,
   and grouped results that make it clear which capability areas are passing,
   failing, or partially satisfied.
3. **Given** a scan result contains failed or partial checks, **When** the
   administrator reviews the results, **Then** each result clearly points them
   toward the relevant configuration area on the same page.
4. **Given** the administrator runs a new scan from the page, **When** the scan
   completes successfully, **Then** the readiness summary updates on the same
   page without requiring a manual reload.

---

### User Story 2 - Configure Phase 1 Capabilities (Priority: P2)

As a site administrator, I want to enable, disable, and adjust the Phase 1
capabilities from one place so I can control how my site exposes agent-ready
signals without editing code.

**Why this priority**: After understanding site readiness, administrators need a
single place to configure the Phase 1 capabilities that affect their score and
site behavior.

**Independent Test**: Update settings for each Phase 1 capability, save the
page, reload it, and confirm the selected preferences persist and are presented
clearly.

**Acceptance Scenarios**:

1. **Given** the administrator is viewing the page, **When** they review the
   available Phase 1 capability areas, **Then** they can see a separate master
   toggle and a distinct configuration area for each capability included in
   Phase 1.
2. **Given** the administrator changes one or more capability settings,
   **When** they save the page, **Then** the new settings are persisted and
   reflected on the next page load.
3. **Given** the administrator expands a capability's configuration area,
   **When** they review its options, **Then** they can understand what the
   capability controls and which site areas it applies to.
4. **Given** the administrator changes settings in multiple capability
   sections, **When** they use the page save action, **Then** all valid changes
   are saved together through a single page-level save flow.
5. **Given** the administrator has unsaved configuration changes, **When**
   they run a new scan, **Then** the scan runs as a separate action and does
   not implicitly save those pending changes.

---

### User Story 3 - Understand Compatibility and Limitations (Priority: P3)

As a site administrator, I want the page to adapt to my site's environment and
show clear limitations so I can make valid choices without being misled by
options that do not apply.

**Why this priority**: Environment-aware guidance reduces misconfiguration and
prevents administrators from assuming unsupported capabilities are active when
they are not.

**Independent Test**: View the page on sites with and without optional
capabilities, and with known conflicts, and confirm the page exposes only valid
choices while clearly explaining unavailable or limited ones.

**Acceptance Scenarios**:

1. **Given** the site has compatible optional content types or commerce
   features, **When** the administrator opens the page, **Then** relevant
   configuration choices are shown with context about what they affect.
2. **Given** an optional dependency or relevant capability is unavailable,
   **When** the administrator opens the page, **Then** related options are
   shown in a disabled state with an explanation instead of appearing fully
   available.
3. **Given** a known limitation prevents a capability from working
   automatically, **When** the administrator opens the page, **Then** the page
   shows a warning and explains the manual action needed without blocking other
   settings.
4. **Given** future-phase capabilities are listed, **When** the administrator
   reviews them, **Then** they are clearly marked as unavailable in this phase
   and cannot be configured as active features.

### Edge Cases

- No scan has been run yet, so the page must communicate readiness without
  showing stale or fabricated results.
- A new scan cannot be completed, so the page must preserve the last known state
  and explain that the refresh failed.
- An optional capability such as commerce support was previously configured but
  is no longer available, so the page must preserve context without presenting
  invalid active controls and must show those controls as disabled with an
  explanation.
- A site-level limitation prevents a capability from being applied
  automatically, so the page must explain the limitation and any manual next
  step.
- The administrator saves a partially updated configuration, so unchanged
  capability settings must remain intact.
- The administrator has unsaved configuration changes and runs a scan, so the
  page must keep those pending changes separate from the refreshed readiness
  result.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide one admin-facing Foundation and
  Architecture page as the single entry point for Phase 1 configuration and
  readiness review.
- **FR-002**: The page MUST show the most recent readiness summary, including
  overall score, maturity or level label, scan timestamp, and grouped category
  outcomes when scan data exists.
- **FR-003**: The page MUST provide a clear empty state when no scan data exists
  and offer an obvious action to generate the first scan.
- **FR-004**: The page MUST allow the administrator to trigger a new scan from
  the page and receive feedback about whether the refresh succeeded or failed.
- **FR-004a**: When a new scan completes successfully, the page MUST refresh the
  displayed readiness summary on the same page without requiring the
  administrator to reload manually.
- **FR-005**: The page MUST present a separate configurable section for each
  of the four Phase 1 capabilities included in scope for this page.
- **FR-006**: Each Phase 1 capability section MUST include a master enable or
  disable control and any scoped configuration choices needed for that
  capability.
- **FR-007**: The page MUST persist administrator-selected Phase 1 settings and
  reload them accurately on subsequent visits.
- **FR-007a**: The page MUST provide one page-level save action that applies all
  valid Phase 1 configuration changes together rather than requiring separate
  saves per capability section.
- **FR-007b**: The page MUST keep scan execution separate from configuration
  saving and MUST NOT implicitly save pending settings changes when a scan is
  run.
- **FR-008**: The page MUST help the administrator connect failing or partial
  readiness results to the relevant configuration section on the page.
- **FR-009**: The page MUST adapt the available configuration choices to the
  site's environment, including supported content types and optional commerce
  functionality.
- **FR-009a**: When an optional capability is unavailable, its related controls
  MUST remain visible in a disabled state with explanatory text rather than
  being hidden or presented as active.
- **FR-010**: When a capability cannot function automatically because of a known
  site limitation or conflict, the page MUST display a clear warning and any
  required manual action.
- **FR-011**: The page MUST restrict access to authorized site administrators
  and prevent unauthorized users from changing settings or initiating scans.
- **FR-012**: The page MUST preserve default site behavior for any Phase 1
  capability that is disabled.
- **FR-013**: The page MUST present future-phase capabilities as informational
  "coming soon" placeholders and MUST NOT allow them to be configured as active
  in Phase 1.
- **FR-014**: The page MUST allow administrators to review a readable preview of
  any user-configurable output that affects published site signals.

### Key Entities *(include if feature involves data)*

- **Readiness Summary**: The latest site assessment shown to administrators,
  including overall score, level label, scan timestamp, grouped result counts,
  and per-capability status indicators.
- **Phase 1 Capability Setting**: A persisted administrator preference for a
  specific Phase 1 capability, including whether the capability is enabled and
  any scoped choices that control where or how it applies.
- **Compatibility State**: The detected site context that influences which
  controls are available, disabled, or annotated with warnings.
- **Capability Preview**: A human-readable representation of an externally
  visible output that helps the administrator verify their chosen settings
  before leaving the page.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In usability review, at least 90% of target administrators can
  identify the site's current readiness status and the next configuration area
  to inspect within 1 minute of landing on the page.
- **SC-002**: At least 90% of target administrators can update and save a valid
  Phase 1 configuration for their site from this page in under 3 minutes
  without needing code changes or external documentation.
- **SC-003**: Saved Phase 1 settings are retained and accurately reflected on
  page reload in 100% of validation scenarios.
- **SC-004**: In all validation scenarios involving unavailable dependencies or
  known conflicts, the page explains the limitation without blocking unrelated
  settings from being reviewed or saved.
- **SC-005**: In 100% of successful scan validation scenarios, the updated
  readiness summary is visible on the same page immediately after the scan
  completes.

## Assumptions

- The scope of this feature is limited to the single Phase 1 admin page and its
  user-facing configuration experience, not the full implementation details of
  each underlying capability.
- The plugin targets WordPress site administrators managing a single site during
  the MVP phase.
- The readiness page covers the agreed Phase 1 capability set for the MVP and
  treats later-phase capabilities as visible "coming soon" placeholders only.
- A previously saved readiness result may be shown until a newer scan is
  requested successfully.
