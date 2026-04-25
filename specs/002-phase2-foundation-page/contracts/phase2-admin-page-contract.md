# Phase 2 Admin Page Contract

## Scope

This contract defines how the existing Agent Ready settings page behaves once
the Phase 2 extension is added.

## Shared Page Contract

- Phase 2 appears on the existing `Settings > Agent Ready` page.
- Phase 1 and Phase 2 content share one page-level save action.
- The Phase 2 extension must not replace or hide the existing Phase 1 content.

## Protected API Applicability Contract

- The page includes an explicit applicability control for protected APIs.
- When applicability is off:
  - OAuth Discovery and Protected Resource sections remain visible.
  - Those sections are disabled.
  - The page explains why they are disabled.

## MCP Server Card Contract

- The page exposes editable MCP Server Card fields for:
  - server name
  - version
  - transport endpoint
- Saved values persist and re-render on the next page load.
- The page provides a readable draft preview before save.

## OAuth Discovery Contract

- The page exposes grouped OAuth/OpenID discovery fields when protected APIs are applicable.
- When not applicable, the section remains visible but disabled.
- Validation errors are shown in context and do not redirect the user away from the page.

## Protected Resource Contract

- The page exposes grouped protected-resource fields when protected APIs are applicable.
- When not applicable, the section remains visible but disabled.
- Validation errors are shown in context and preserve entered values.

## Save and Validation Contract

- One shared page-level save action applies to both existing settings and new
  Phase 2 settings.
- Invalid Phase 2 values reject the save in place.
- Entered values are preserved for correction.
- No partial Phase 2 save is allowed.

## Preview Contract

- The page provides a readable Phase 2 metadata preview before save.
- The preview is explicitly a draft representation.
- Published values change only after a successful save.

## Accessibility Contract

- Disabled Phase 2 sections remain readable and understandable.
- Validation messages identify the affected section or field.
- Explanatory state does not rely on color alone.
