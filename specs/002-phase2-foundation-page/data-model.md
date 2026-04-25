# Data Model: Phase 2 Foundation and Architecture Page

## Entity: Phase2SettingsPageState

Represents the Phase 2 extension of the existing Agent Ready page.

### Fields

- `phase1_sections_present`: `bool`
- `mcp_server_card_section`: `McpServerCardSection`
- `protected_api_applicability`: `ProtectedApiApplicabilitySetting`
- `oauth_discovery_section`: `OAuthDiscoverySection`
- `protected_resource_section`: `ProtectedResourceSection`
- `phase2_preview_summary`: `Phase2PreviewSummary`
- `validation_messages`: `ValidationMessage[]`

### Relationships

- Extends the existing page-level state rather than replacing it.
- Built from persisted settings, derived applicability state, and draft preview
  data.

## Entity: ProtectedApiApplicabilitySetting

Determines whether protected API metadata applies to the site.

### Fields

- `enabled`: `bool`
- `label`: `string`
- `help_text`: `string`

### Validation Rules

- Drives whether OAuth and protected-resource sections are active or disabled.
- Must always be visible on the page.

## Entity: McpServerCardSettings

Represents the administrator-provided Phase 2 MCP Server Card data.

### Fields

- `enabled`: `bool`
- `name`: `string`
- `version`: `string`
- `transport`: `string`

### Validation Rules

- `name` must be non-empty when the section is enabled.
- `version` must be non-empty when the section is enabled.
- `transport` must be a valid URL when the section is enabled.

## Entity: McpServerCardSection

### Fields

- `title`: `string`
- `enabled`: `bool`
- `controls`: `Phase2Control[]`
- `preview`: `Phase2PreviewSummaryItem`

## Entity: OAuthDiscoverySettings

Represents the administrator-provided OAuth/OpenID discovery values.

### Fields

- `enabled`: `bool`
- `issuer`: `string`
- `authorization_endpoint`: `string`
- `token_endpoint`: `string`
- `jwks_uri`: `string`

### Validation Rules

- Fields are only editable when protected APIs are applicable.
- Each endpoint field must be a valid URL when the section is enabled.
- `issuer` must be non-empty when the section is enabled.

## Entity: OAuthDiscoverySection

### Fields

- `title`: `string`
- `active`: `bool`
- `disabled_reason`: `string|null`
- `controls`: `Phase2Control[]`
- `preview`: `Phase2PreviewSummaryItem`

## Entity: ProtectedResourceSettings

Represents the administrator-provided protected-resource metadata values.

### Fields

- `enabled`: `bool`
- `resource`: `string`
- `authorization_servers`: `string[]`

### Validation Rules

- Section is only active when protected APIs are applicable.
- `resource` must be a valid URL when enabled.
- Each authorization server must be a valid URL.

## Entity: ProtectedResourceSection

### Fields

- `title`: `string`
- `active`: `bool`
- `disabled_reason`: `string|null`
- `controls`: `Phase2Control[]`
- `preview`: `Phase2PreviewSummaryItem`

## Entity: Phase2Control

Represents an individual field or toggle within a Phase 2 section.

### Fields

- `control_key`: `string`
- `label`: `string`
- `value`: `scalar|array|null`
- `disabled`: `bool`
- `help_text`: `string|null`
- `validation_error`: `string|null`

## Entity: Phase2PreviewSummary

Represents the readable draft preview shown before save.

### Fields

- `is_draft`: `bool`
- `items`: `Phase2PreviewSummaryItem[]`

### Validation Rules

- Must clearly represent pending values as draft until save succeeds.

## Entity: Phase2PreviewSummaryItem

### Fields

- `section_key`: `string`
- `label`: `string`
- `display_value`: `string|array`

## Entity: ValidationMessage

Represents targeted on-page validation feedback for Phase 2.

### Fields

- `message_key`: `string`
- `severity`: `'error'|'warning'|'info'`
- `message`: `string`
- `target_section`: `string|null`
- `target_control`: `string|null`

## Entity: ExtendedPluginSettings

Represents the existing `agent_ready_wp_settings` option array after Phase 2
extension.

### Added Fields

- `protected_apis`: `ProtectedApiApplicabilitySetting`
- `mcp_server_card`: `McpServerCardSettings`
- `oauth`: `OAuthDiscoverySettings`
- `protected_resource`: `ProtectedResourceSettings`

### Validation Rules

- Existing Phase 1 keys remain backward compatible.
- New Phase 2 keys default to disabled/incomplete states until configured.
