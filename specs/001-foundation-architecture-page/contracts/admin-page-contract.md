# Admin Page Contract: Foundation and Architecture Page

## Route

- WordPress admin page under `Settings > Agent Ready`
- Visible only to users authorized to manage site-wide plugin settings

## Page Sections

The page contract requires the following top-level sections in order:

1. Header area with page title and primary scan action
2. Readiness summary panel
3. Four Phase 1 capability panels
4. Phase 2 placeholder items
5. One page-level save action

## Readiness Summary Contract

### Empty State

- Shows that no scan has been run yet
- Offers a prominent scan action
- Does not fabricate score or level values

### Cached/Loaded State

- Shows score
- Shows level label
- Shows scan timestamp
- Shows grouped status rows
- Provides a clear association from failed/partial results to the relevant
  capability panel on the same page

### Refresh Behavior

- Triggered by an explicit scan action
- Updates the readiness summary on the same page after successful completion
- Does not implicitly save unsaved page settings
- On failure, preserves the previous successful summary if one exists and shows
  an error message

## Capability Panel Contract

Each of the four Phase 1 capability panels MUST provide:

- A master enable/disable control
- Expandable configuration content
- Contextual help text
- Availability state derived from site compatibility
- Preview content where the capability exposes user-configurable site signals

## Availability Contract

### Available

- Panel controls are interactive
- Saved values can be changed

### Unavailable

- Panel or affected controls remain visible
- Controls are disabled
- Explanation text states why the controls are unavailable
- Previously saved values remain visible for context

## Phase 2 Placeholder Contract

- MCP Server Card and OAuth Discovery appear as visible placeholders
- Placeholders are clearly labeled as not active in Phase 1
- Placeholders are non-interactive

## Save Contract

- A single page-level save action applies all valid settings changes together
- Saving does not trigger a scan automatically
- Unchanged values remain intact

## Error and Warning Contract

- Known compatibility limitations are shown inline or as page notices
- Warnings do not block unrelated settings from being reviewed or saved
- Validation feedback keeps the administrator on the page

## Accessibility Contract

- Section headings and controls are clearly labeled
- Disabled states remain readable
- Status and warning messages are understandable without relying on color alone
