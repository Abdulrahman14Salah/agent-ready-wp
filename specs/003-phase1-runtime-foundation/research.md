# Research: Phase 1 Runtime Foundation

## Decision 1: Apply markdown only when `text/markdown` is highest or tied-highest in `Accept`

**Decision**: Treat markdown negotiation as applicable only when `text/markdown`
represents the highest client preference (or is tied at highest quality).

**Rationale**: This aligns with HTTP content negotiation semantics while
preventing accidental markdown responses when a client primarily prefers HTML.

**Alternatives considered**:

- Any occurrence of `text/markdown`: rejected because it can override stronger
  non-markdown preferences.
- Exact-only `Accept: text/markdown`: rejected because it is too strict and
  ignores valid weighted header usage.

## Decision 2: Emit `Vary: Accept` only on markdown responses

**Decision**: Add `Vary: Accept` for runtime markdown responses; do not require
it for non-markdown fallback responses in this phase.

**Rationale**: This provides cache safety for representation changes while
keeping header behavior minimal and explicit for the alternate response path.

**Alternatives considered**:

- Emit `Vary: Accept` on all related responses: rejected for extra response
  overhead without additional functional value in this scope.
- Omit `Vary`: rejected due to cache confusion risk between HTML/markdown.

## Decision 3: Unsupported or excluded markdown requests fall back to normal WordPress handling

**Decision**: For unsupported contexts or explicitly excluded request classes
(for example non-singular routes, admin/system/auth/feed/sitemap requests, or
non-selected content types), allow normal request handling to continue.

**Rationale**: This preserves default site behavior and avoids introducing
markdown-specific error contracts where no qualifying representation exists.

**Alternatives considered**:

- Return `406 Not Acceptable`: rejected because it is stricter than needed and
  may break existing agent/browser interoperability.
- Return empty markdown payloads: rejected because it obscures real content
  behavior and complicates debugging.

## Decision 4: Limit MVP markdown negotiation to eligible frontend document requests

**Decision**: Restrict markdown negotiation in this phase to frontend document
requests that resolve to supported singular WordPress content, and explicitly
exclude `wp-admin`, REST API, AJAX, cron, feeds, sitemaps, login/register
pages, and asset requests.

**Rationale**: This matches the narrow MVP already reflected in current runtime
eligibility, keeps hook behavior predictable, and reduces the risk of changing
non-document system responses unexpectedly.

**Alternatives considered**:

- Negotiate markdown for every frontend request with `Accept: text/markdown`:
  rejected because archives, feeds, auth, API, and asset paths have different
  response contracts and should not be implicitly rewritten in this phase.
- Expand support to additional frontend route classes immediately: rejected
  because it widens public-contract surface without clear acceptance coverage.

## Decision 5: Always honor WordPress access and visibility rules for markdown

**Decision**: Markdown output follows the same visibility/access constraints as
normal content rendering, including protected/private content restrictions.

**Rationale**: Prevents accidental exposure and aligns security behavior across
representations for the same content resource.

**Alternatives considered**:

- Ignore access checks for markdown: rejected as a security violation.
- Disable markdown for all protected content globally: rejected because it is
  overly restrictive and not required if normal permission checks are honored.

## Decision 6: Preserve UTF-8 and Arabic text in markdown output

**Decision**: Markdown rendering must preserve UTF-8 text fidelity, including
Arabic content, exactly as provided by the source WordPress content after normal
entity decoding.

**Rationale**: Agent-facing markdown is only useful if multilingual content
survives conversion without corruption or lossy transliteration.

**Alternatives considered**:

- Treat encoding fidelity as an implementation detail only: rejected because it
  is a user-visible response contract.
- Add heavy external conversion dependencies for language support: rejected for
  MVP complexity and WordPress-plugin portability reasons.

## Decision 7: Emit exactly one canonical `Content-Signal` line

**Decision**: During generated robots output shaping, replace existing generated
`Content-Signal` directives and emit only one canonical line when configured.

**Rationale**: Avoids conflicting or duplicated directives and provides
predictable output for scanners and agents.

**Alternatives considered**:

- Always append: rejected because duplicates create ambiguous policy output.
- Skip output when any existing directive is present: rejected because it can
  leave stale directives in place and ignore current settings.

## Decision 8: Keep physical `robots.txt` handling as graceful non-mutation fallback

**Decision**: Runtime implementation must not write physical files; where host
or physical-file behavior overrides generated robots output, preserve default
behavior and surface compatibility state through existing plugin signaling.

**Rationale**: Meets constitution constraints and keeps operational risk low on
shared hosting environments.

**Alternatives considered**:

- Attempt file writes: rejected as out-of-scope and non-compliant.
- Fail plugin runtime when conflict exists: rejected because unrelated features
  should continue to work.

## Decision 9: Validate runtime contracts with unit + integration coverage

**Decision**: Combine unit tests for negotiation/directive shaping with
integration tests for headers, response type, and robots output fallback.

**Rationale**: Ensures public runtime behavior is verified at hook boundaries,
not just pure function level.

**Alternatives considered**:

- Unit tests only: rejected because hook-level contracts are integration
  concerns.
- Manual-only checks: rejected by constitution requirements for automated
  verification.
