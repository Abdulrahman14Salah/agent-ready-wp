# Quickstart: Phase 1 Runtime Foundation

## Goal

Validate runtime-only Phase 1 behavior for markdown negotiation (F2) and content
signals (F3), including response contracts and graceful fallbacks.

## Prerequisites

- WordPress 6.0+ local environment
- PHP 8.0+
- Plugin branch `003-phase1-runtime-foundation` active
- Administrator account to configure settings
- Ability to run HTTP requests (browser + curl/Postman)

## Setup

1. Activate plugin build containing runtime F2/F3 implementation.
2. In `Settings > Agent Ready`, confirm markdown and content-signal settings are
   available from previous admin-page phases.
3. Configure at least one supported post/page and publish test content.
4. Enable markdown negotiation and content signals.
5. Set at least one content signal value (for example `search=yes`).

## Validation Scenarios

### 1. Markdown positive path

1. Request a supported singular URL with `Accept: text/markdown`.
2. Confirm response `Content-Type` is markdown.
3. Confirm `x-markdown-tokens` header is present with integer value.
4. Confirm `Vary: Accept` is present.
5. Confirm response body is markdown, not HTML.

### 2. Mixed Accept negotiation behavior

1. Request the same URL with mixed `Accept` values where HTML is preferred.
2. Confirm markdown does not apply and default handling is preserved.
3. Request with `text/markdown` highest/tied-highest.
4. Confirm markdown contract applies.

### 3. Unsupported-content fallback

1. Request a non-qualifying route (for example archive/home/search) with
   `Accept: text/markdown`.
2. Confirm plugin falls back to default handling (no markdown headers/body).

### 4. Excluded request fallback

1. Request an excluded route or request class (for example `wp-admin`,
   `wp-login.php`, REST API, AJAX, feed, sitemap, or an asset URL) with
   `Accept: text/markdown`.
2. Confirm plugin does not emit markdown-specific headers.
3. Confirm the original HTML or native default response behavior is preserved.

### 5. Arabic and UTF-8 preservation

1. Publish supported singular content containing Arabic text and other UTF-8
   characters.
2. Request that content with `Accept: text/markdown`.
3. Confirm the markdown response preserves the original text without mojibake or
   dropped characters.

### 6. Access-control safety

1. Request protected/private content as an unauthorized requester with markdown
   `Accept`.
2. Confirm markdown does not bypass normal WordPress visibility rules.

### 7. Content-signal positive path

1. Request `/robots.txt` when content signals are enabled and configured.
2. Confirm exactly one canonical `Content-Signal` line exists.
3. Confirm only configured keys appear; unset keys are omitted.

### 8. Content-signal disabled/unset behavior

1. Disable content signals (or unset all signal values).
2. Request `/robots.txt`.
3. Confirm no `Content-Signal` directive is emitted.

### 9. Physical robots conflict behavior

1. Simulate or detect physical `robots.txt` conflict scenario.
2. Confirm plugin does not attempt direct file mutation.
3. Confirm default host/WordPress behavior remains stable.

## Expected Outcome

Runtime F2/F3 contracts are stable and predictable: markdown is emitted only for
qualified requests with required headers, `robots.txt` emits one canonical
`Content-Signal` directive only when configured, and all incompatible or
unsupported paths fail safely without breaking default WordPress behavior.

## Validation Outcomes (2026-04-24)

- Executed runtime smoke harness via `php /tmp/arwp_runtime_smoke.php` before
  cleanup and confirmed:
  - markdown positive-path headers/body contract,
  - HTML-preferred and unsupported-context markdown fallback behavior,
  - access-control-safe markdown fallback behavior,
  - canonical `Content-Signal` output,
  - disabled/unset content-signal short-circuit behavior,
  - physical `robots.txt` conflict no-mutation fallback.
- Executed syntax checks with `php -l` across runtime source and runtime test
  files with no syntax errors.
- PHPUnit runtime suite execution is pending in this environment because
  `phpunit`/`vendor/bin/phpunit` is not available.
