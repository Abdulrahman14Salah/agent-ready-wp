# Content Signals Contract: Phase 1 Runtime Foundation

## Scope

Defines the runtime `robots.txt` output behavior for F3 content signals only.

## Directive Contract

- When content signals are enabled and at least one signal is configured,
  runtime generated `robots.txt` output includes one canonical line:
  `Content-Signal: <key>=<value>, ...`
- Only configured non-empty signal values are emitted.
- Signal keys use canonical names: `ai-train`, `search`, `ai-input`.

## Canonicalization Contract

- Generated output contains at most one `Content-Signal` directive.
- Existing generated `Content-Signal` lines are replaced before writing the
  canonical configured line.

## Disabled/Unset Contract

- If the feature is disabled, no `Content-Signal` line is emitted.
- If all signal values are unset, no `Content-Signal` line is emitted.

## Compatibility/Fallback Contract

- Runtime implementation must not mutate physical `robots.txt` files.
- When host/physical-file behavior overrides generated output, plugin runtime
  preserves default behavior and degrades gracefully.

## Runtime Notes

- Disabled or unset content-signal configuration short-circuits and preserves
  incoming generated `robots.txt` output.
- Canonical replacement is applied only when the feature is enabled and at
  least one valid directive pair is configured.
