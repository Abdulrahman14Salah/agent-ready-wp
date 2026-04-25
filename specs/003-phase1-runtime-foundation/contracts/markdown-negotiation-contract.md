# Markdown Negotiation Contract: Phase 1 Runtime Foundation

## Scope

Defines the runtime HTTP behavior for F2 markdown negotiation only.

## Eligibility Contract

Markdown output applies only when all conditions are true:

- Markdown feature is enabled.
- Request is an eligible frontend document request.
- Request targets supported singular content (configured post types and optional
  WooCommerce products when available/enabled).
- `Accept` indicates `text/markdown` as highest or tied-highest preference.
- Requester is permitted to view the requested content under normal WordPress
  visibility/access rules.

If any condition fails, runtime behavior must fall back to normal WordPress
handling.

For this MVP, eligible frontend document requests exclude:

- `wp-admin`
- REST API requests
- AJAX requests
- cron
- feeds
- sitemaps
- login/register pages
- asset requests

This phase does not expand markdown negotiation beyond supported singular
frontend WordPress content.

## Response Contract (when eligible)

- Status remains successful for normal content delivery paths.
- `Content-Type` is `text/markdown; charset=utf-8`.
- `Vary: Accept` is present.
- `x-markdown-tokens` header is present with an integer value.
- Body is markdown representation of the qualifying content.
- UTF-8 content, including Arabic text, is preserved in the emitted markdown
  body.

## Fallback Contract (when not eligible)

- Runtime must not emit markdown-specific headers/body.
- WordPress default HTML handling remains authoritative for unsupported or
  excluded document requests, and native default handling remains authoritative
  for excluded non-document/system requests.
- Unsupported-content markdown requests do not return markdown-specific error
  contracts in this phase.

## Compatibility Contract

- WooCommerce-dependent markdown behavior is active only when WooCommerce is
  available and enabled in settings.
- Protected/private content is never exposed via markdown beyond requester
  permissions.

## Runtime Notes

- Eligibility fallback reasons are mapped to stable runtime decisions:
  `feature_disabled`, `accept_not_preferred`, `unsupported_context`,
  `access_denied`.
- Markdown-specific headers/body are emitted only when the eligibility decision
  is `markdown`; fallback decisions preserve default WordPress output.
- Excluded request classes remain outside markdown negotiation even if they send
  `Accept: text/markdown`.
