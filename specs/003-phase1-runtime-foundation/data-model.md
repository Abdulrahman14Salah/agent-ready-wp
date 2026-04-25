# Data Model: Phase 1 Runtime Foundation

## Entity: RuntimeFeatureSettings

Represents the subset of persisted plugin settings consumed by runtime F2/F3
behavior.

### Fields

- `markdown.enabled`: `bool`
- `markdown.post_types`: `string[]`
- `markdown.include_woo`: `bool`
- `content_signals.enabled`: `bool`
- `content_signals.ai_train`: `''|'yes'|'no'`
- `content_signals.search`: `''|'yes'|'no'`
- `content_signals.ai_input`: `''|'yes'|'no'`

### Validation Rules

- Missing keys resolve to safe defaults before runtime evaluation.
- Tri-state signal fields accept only `''`, `yes`, or `no`.
- WooCommerce markdown scope is effective only when WooCommerce is active.

## Entity: MarkdownRequestContext

Represents runtime request state used to decide markdown eligibility.

### Fields

- `accept_header`: `string`
- `is_eligible_frontend_document_request`: `bool`
- `is_singular`: `bool`
- `post_type`: `string|null`
- `post_id`: `int|null`
- `requester_can_view`: `bool`
- `feature_enabled`: `bool`

### Validation Rules

- Markdown negotiation is considered only when feature is enabled.
- Excluded request classes such as admin, REST, AJAX, cron, feeds, sitemaps,
  login/register, and assets resolve `is_eligible_frontend_document_request`
  to `false` in this MVP.
- `text/markdown` must be highest or tied-highest preference in `Accept`.
- Context must refer to a supported singular content item visible to requester.

## Entity: MarkdownNegotiationDecision

Represents the evaluated outcome of markdown negotiation.

### Fields

- `applies`: `bool`
- `reason`: `'eligible'|'feature_disabled'|'unsupported_context'|'accept_not_preferred'|'access_denied'`
- `selected_representation`: `'markdown'|'default'`

### State Transitions

- Initial request state → decision evaluation
- Eligible decision → markdown response emission
- Non-eligible decision → default WordPress handling

## Entity: MarkdownResponseContract

Represents runtime markdown output when negotiation applies.

### Fields

- `content_type`: `text/markdown; charset=utf-8`
- `vary`: `Accept`
- `token_count_header`: `int`
- `body`: `string`

### Validation Rules

- Response body must be non-empty for eligible content.
- Token-count header must be an integer derived from body content.
- UTF-8 content, including Arabic text, must remain semantically unchanged after
  conversion.
- Contract is emitted only when `MarkdownNegotiationDecision.applies=true`.

## Entity: ContentSignalDirectiveState

Represents the canonical runtime `Content-Signal` directive derived from
settings.

### Fields

- `enabled`: `bool`
- `pairs`: `array<string,'yes'|'no'>`
- `directive_line`: `string|null`

### Validation Rules

- Only non-empty signal settings contribute pairs.
- `directive_line` is null when feature disabled or all signal values unset.
- Runtime generated robots output may contain at most one canonical
  `Content-Signal` line.

## Entity: RuntimeCompatibilityState

Represents compatibility flags affecting F2/F3 runtime behavior.

### Fields

- `woocommerce_active`: `bool`
- `physical_robots_txt_present`: `bool`
- `public_cpts`: `string[]`

### Validation Rules

- Feature behavior must short-circuit safely when prerequisite state is absent.
- Physical `robots.txt` conflicts must not trigger direct file mutation.

## Entity: RuntimeContractTestCase

Represents executable runtime verification scenarios.

### Fields

- `scenario_key`: `string`
- `input_context`: `MarkdownRequestContext|ContentSignalDirectiveState`
- `expected_contract`: `MarkdownResponseContract|string|null`
- `expected_fallback`: `bool`

### Purpose

- Links functional requirements to automated unit and integration coverage.
- Ensures both positive and fallback behavior remain regression-safe.
