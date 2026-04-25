# Quickstart: Phase 2 Foundation and Architecture Page

## Goal

Verify that the existing Agent Ready page can be extended with Phase 2
configuration for MCP Server Card, OAuth discovery, and protected-resource
settings while preserving the shared page save flow.

## Prerequisites

- WordPress 6.0+ running locally
- PHP 8.0+
- Phase 1 Agent Ready page already available
- Administrator account

## Setup

1. Activate the plugin build that includes the Phase 2 page extension.
2. Sign in as an administrator.
3. Navigate to `Settings > Agent Ready`.

## Validation Scenarios

### 1. Shared page continuity

1. Confirm the existing Phase 1 settings are still present.
2. Confirm the new Phase 2 sections appear on the same page.
3. Confirm only one page-level save action is present.

### 2. MCP Server Card configuration

1. Find the MCP Server Card section.
2. Enter valid server name, version, and transport values.
3. Confirm the page shows a readable draft preview before save.
4. Save the page.
5. Reload and confirm the values persist.

### 3. Protected API applicability toggle

1. Turn the protected-API applicability setting off.
2. Confirm OAuth and Protected Resource sections remain visible but disabled.
3. Confirm the page explains why they are disabled.
4. Turn the applicability setting on.
5. Confirm those sections become editable.

### 4. OAuth and protected-resource save behavior

1. Enter valid OAuth discovery and protected-resource values.
2. Save the page.
3. Reload and confirm the values persist.
4. Confirm the same page-level save flow handled both existing and Phase 2 settings.

### 5. Invalid Phase 2 values

1. Enter incomplete or inconsistent Phase 2 values.
2. Save the page.
3. Confirm the page rejects the save in place.
4. Confirm the entered values remain present for correction.
5. Confirm targeted validation feedback identifies the affected fields or sections.

### 6. Draft preview behavior

1. Edit Phase 2 fields without saving.
2. Confirm the preview updates as a draft representation.
3. Confirm the page does not imply that published values changed yet.
4. Save successfully and confirm the persisted values now match the previewed content.

## Expected Outcome

The existing Agent Ready settings page behaves as one continuous workflow for
Phase 1 and Phase 2, with explicit protected-API applicability, disabled-but-
visible non-applicable sections, draft previews, and in-place validation.

## Validation Record (2026-04-23)

- Implemented same-page shared save handling with nonce and capability checks.
- Implemented reject-in-place Phase 2 validation with draft value preservation.
- Added unit and integration coverage for render, applicability state, preview shaping, and invalid-save feedback.
- Ran PHP syntax validation on all changed source and test files (`php -l`); no syntax errors detected.
- Manual WordPress admin walkthrough and `phpunit` execution remain pending in this environment.
