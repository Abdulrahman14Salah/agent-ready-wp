# WebMCP Runtime Contract: Phase 1 Discovery Runtime

## Scope

Defines the public frontend runtime behavior for F5 WebMCP exposure only.

## Emission Contract

- WebMCP output is emitted only on public frontend page requests.
- When enabled, the plugin enqueues one plugin-owned frontend runtime asset plus
  the resolved tool-registration payload.
- When disabled, the plugin emits no WebMCP asset or registration payload.

## Tool Exposure Contract

- Default runtime exposure includes `search`, `get_posts`, and `get_page` when
  those saved toggles remain enabled.
- `get_products` is exposed only when WooCommerce is active and the saved tool
  toggle is enabled.
- Disabled or incompatible tools are omitted rather than emitted in a disabled
  state.

## Browser Compatibility Contract

- The runtime asset performs browser capability detection before attempting any
  tool registration.
- If the required browser capability is unavailable, the asset exits without
  registration and without throwing unhandled runtime errors.
- The script may still be delivered to unsupported browsers; compatibility is
  enforced client-side as a no-op.

## Fallback Contract

- Admin screens, feeds, REST requests, and other non-public contexts must not
  receive WebMCP runtime output.
- WooCommerce absence removes only WooCommerce-dependent tools; non-Woo tools
  remain eligible.
- No frontend discovery output should require remote calls during page render.

## Runtime Notes

- Runtime decisions should expose stable reasons such as `feature_disabled`,
  `unsupported_context`, and `no_tools_enabled` for automated verification.
- The plugin localizes runtime payload data on the `arwpWebMcpRuntime` browser
  object before enqueueing the `arwp-webmcp-runtime` asset.
- Payload data must be limited to discovery metadata required for registration
  and must not leak privileged or admin-only configuration.
