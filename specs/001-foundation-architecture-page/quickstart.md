# Quickstart: Foundation and Architecture Page

## Goal

Verify that the Phase 1 admin page lets an administrator review readiness,
configure the four Phase 1 capabilities, understand compatibility limits, and
refresh scan results in place.

## Prerequisites

- WordPress 6.0+ running locally
- PHP 8.0+
- Plugin installed and activated
- Administrator account
- Optional: WooCommerce installed for compatibility-state checks

## Setup

1. Activate the plugin.
2. Sign in as an administrator.
3. Navigate to `Settings > Agent Ready`.

## Validation Scenarios

### 1. Empty-state readiness summary

1. Ensure no cached scan result exists.
2. Open the page.
3. Confirm the summary area explains that no scan has been run yet.
4. Confirm a scan action is visible.

### 2. Page structure and placeholders

1. Confirm the page shows four Phase 1 capability panels:
   - Markdown Negotiation
   - Content Signals
   - API Catalog
   - WebMCP
2. Confirm Phase 2 placeholder items are visible but not interactive.
3. Confirm a single page-level save action is present.

### 3. Settings persistence

1. Change settings in at least two capability panels.
2. Save the page.
3. Reload the page.
4. Confirm all saved values remain selected.

### 4. Separate save and scan actions

1. Make a visible settings change but do not save.
2. Trigger a new scan.
3. Confirm the scan runs.
4. Confirm the readiness summary refreshes in place.
5. Confirm the unsaved settings change was not implicitly committed.

### 5. Compatibility-aware controls

1. Open the page without WooCommerce active.
2. Confirm WooCommerce-dependent controls remain visible but disabled with an
   explanation.
3. Activate WooCommerce and reload the page.
4. Confirm the related controls become available.

### 6. Conflict and warning behavior

1. Simulate a physical `robots.txt` conflict.
2. Confirm the page shows a warning and any required manual action.
3. Confirm unrelated settings can still be reviewed and saved.

### 7. Scan failure behavior

1. Simulate a failed scan request.
2. Trigger a scan.
3. Confirm the page shows an error message.
4. Confirm the last known successful readiness summary remains visible if one
   exists.

## Expected Outcome

The page behaves as a single, administrator-focused control center for the
Phase 1 feature set, with one save flow, one separate scan flow, visible
compatibility guidance, and non-interactive Phase 2 placeholders.

## Usage Notes

- Saving settings and running a scan are intentionally separate actions.
- WooCommerce-dependent controls stay visible but disabled when WooCommerce is
  not active.
- Phase 2 items are present as placeholders only and are not interactive in
  this release.

## Validation Notes

- PHP lint passed for all plugin and test files in this repository.
- Lightweight PHP smoke checks confirmed settings persistence, admin page
  rendering, and AJAX scan response shaping through the local test bootstrap.
- Full WordPress admin validation and PHPUnit execution remain pending because
  this workspace does not include a WordPress runtime or a `phpunit` binary.
