# Data Model: Foundation and Architecture Page

## Entity: SettingsPageState

Represents the full server-rendered state required to display the admin page.

### Fields

- `readiness_summary`: `ReadinessSummary|null`
- `capability_panels`: `CapabilityPanel[]`
- `compatibility_state`: `CompatibilityState`
- `phase_two_placeholders`: `PhasePlaceholder[]`
- `pending_messages`: `AdminMessage[]`
- `can_run_scan`: `bool`
- `can_save_settings`: `bool`

### Relationships

- Composed from persisted `PluginSettings`, cached `ReadinessSummary`, and
  runtime `CompatibilityState`.
- Used to build the admin page view and the in-place refreshed scan panel.

## Entity: PluginSettings

Represents the persisted `agent_ready_wp_settings` option array.

### Fields

- `enabled`: `bool`
- `markdown`: `MarkdownSettings`
- `content_signals`: `ContentSignalsSettings`
- `api_catalog`: `ApiCatalogSettings`
- `webmcp`: `WebMcpSettings`
- `mcp_server_card`: `PhaseTwoPlaceholderSettings`
- `oauth`: `PhaseTwoPlaceholderSettings`

### Validation Rules

- Missing keys are normalized to defaults during read/write.
- Boolean toggles must be coerced to `true` or `false`.
- Unsupported capability settings remain stored but do not become active when
  compatibility requirements are missing.

## Entity: MarkdownSettings

### Fields

- `enabled`: `bool`
- `post_types`: `string[]`
- `include_woo`: `bool`

### Validation Rules

- `post_types` may contain `post`, `page`, and detected public CPT slugs.
- `include_woo` can only affect behavior when WooCommerce is active.

## Entity: ContentSignalsSettings

### Fields

- `enabled`: `bool`
- `ai_train`: `''|'yes'|'no'`
- `search`: `''|'yes'|'no'`
- `ai_input`: `''|'yes'|'no'`
- `preview_line`: `string`

### Validation Rules

- Signal values must be one of the allowed tri-state values.
- `preview_line` is derived, not persisted as authoritative input.

## Entity: ApiCatalogSettings

### Fields

- `enabled`: `bool`
- `include_wp_rest`: `bool`
- `include_woo_rest`: `bool`
- `custom_entries`: `ApiCatalogEntry[]`

### Validation Rules

- `include_woo_rest` may only become active when WooCommerce is available.
- `custom_entries` must contain valid display names and URLs.
- Saving changes that affect rewrite behavior marks the API Catalog registration
  as requiring rewrite refresh.

## Entity: ApiCatalogEntry

### Fields

- `name`: `string`
- `anchor`: `string`
- `service_desc`: `string`

### Validation Rules

- `name` must be non-empty after sanitization.
- `anchor` and `service_desc` must be valid URLs.

## Entity: WebMcpSettings

### Fields

- `enabled`: `bool`
- `tools.search`: `bool`
- `tools.get_posts`: `bool`
- `tools.get_page`: `bool`
- `tools.get_products`: `bool`

### Validation Rules

- `get_products` may only become active when WooCommerce is available.
- Disabled parent state prevents child tool settings from taking effect.

## Entity: ReadinessSummary

Represents the cached scan result shown on the page and returned by the scan
action.

### Fields

- `url`: `string`
- `score`: `int`
- `level`: `int`
- `level_name`: `string`
- `checks`: `CheckResult[]`
- `groups`: `CheckGroupSummary[]`
- `scanned_at`: `datetime`
- `status`: `'empty'|'fresh'|'refresh_failed'`
- `message`: `string|null`

### Validation Rules

- `score` must be an integer in the API-supported range.
- `scanned_at` is required for successful results.
- `status='empty'` is used when no scan has ever been cached.
- `status='refresh_failed'` keeps the previous successful summary, if any, plus
  an error message.

## Entity: CheckGroupSummary

### Fields

- `label`: `string`
- `passed`: `int`
- `total`: `int`
- `state`: `'pass'|'partial'|'fail'`
- `primary_linked_panel`: `string|null`

### Purpose

- Drives grouped summary rows such as discoverability, content, and bot access.
- Supports scroll-to-panel linking from the summary area.

## Entity: CheckResult

### Fields

- `key`: `string`
- `label`: `string`
- `state`: `'pass'|'partial'|'fail'`
- `mapped_panel`: `string|null`

### Purpose

- Preserves granular scan output needed to associate failed checks with the
  correct configuration section.

## Entity: CapabilityPanel

Represents one visible page section for a Phase 1 capability.

### Fields

- `panel_key`: `'markdown'|'content_signals'|'api_catalog'|'webmcp'`
- `title`: `string`
- `enabled`: `bool`
- `available`: `bool`
- `expanded`: `bool`
- `status_note`: `string|null`
- `controls`: `PanelControl[]`
- `preview`: `CapabilityPreview|null`

### State Transitions

- `available + enabled`
- `available + disabled`
- `unavailable + disabled with explanation`

## Entity: PanelControl

### Fields

- `control_key`: `string`
- `label`: `string`
- `value`: `scalar|array|null`
- `disabled`: `bool`
- `help_text`: `string|null`
- `validation_error`: `string|null`

### Purpose

- Normalizes individual capability inputs for rendering and validation feedback.

## Entity: CapabilityPreview

### Fields

- `preview_type`: `'content_signal_line'|'api_catalog_entries'|'tool_list'|'post_type_selection'`
- `display_value`: `string|array`

### Purpose

- Gives administrators a readable preview of externally visible outputs before
  leaving the page.

## Entity: CompatibilityState

Represents environment checks that affect page rendering.

### Fields

- `woocommerce_active`: `bool`
- `public_cpts`: `string[]`
- `physical_robots_txt_present`: `bool`
- `api_catalog_file_conflict`: `bool`
- `wp_head_supported`: `bool`
- `warnings`: `CompatibilityWarning[]`

### Purpose

- Controls disabled states, notices, and feature availability messaging.

## Entity: CompatibilityWarning

### Fields

- `warning_key`: `string`
- `severity`: `'info'|'warning'`
- `message`: `string`
- `affected_panel`: `string|null`
- `manual_action`: `string|null`

## Entity: PhasePlaceholder

### Fields

- `placeholder_key`: `'mcp_server_card'|'oauth_discovery'`
- `label`: `string`
- `status_text`: `string`
- `interactive`: `bool`

### Validation Rules

- Always rendered as non-interactive in Phase 1.

## Entity: ScanActionResponse

Represents the AJAX response returned when the administrator triggers a scan.

### Fields

- `success`: `bool`
- `message`: `string`
- `summary`: `ReadinessSummary|null`
- `errors`: `string[]`

### Validation Rules

- Failure responses must not erase the previously cached successful summary.
- Successful responses must contain a refreshable summary payload.
