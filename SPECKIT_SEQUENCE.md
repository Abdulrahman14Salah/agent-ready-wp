# Speckit Runtime Sequence

## Purpose

This repository already has two completed admin-page specs:

- `001-foundation-architecture-page`
- `002-phase2-foundation-page`

The missing work is now the runtime and public-output behavior. To keep
Speckit numbering clean, continue with `003`, `004`, and `005` in order.

## Rules

- Keep `001` and `002` unchanged as admin-page specs.
- Do not use "Foundation and Architecture page" in `003+`.
- Do not pre-create numbered directories under `specs/`.
- Run each `/speckit.specify` command only after the previous spec is created.
- Keep `003+` runtime-only and exclude admin UI already covered by `001` and
  `002`.

## Ordered Commands

### 1. `003` - Phase 1 Runtime Foundation

Use this first:

```text
/speckit.specify Read PLAN.md and create a specification for phase 3: Phase 1 Runtime Foundation ONLY. Scope includes F2 Markdown Negotiation and F3 Content Signals runtime behavior, public outputs, hook registration, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 001 and exclude F4 through F8.
```

Expected focus:

- `Accept: text/markdown` behavior
- normal HTML fallback
- `robots.txt` Content-Signal output
- disabled-feature behavior
- WooCommerce and CPT applicability where needed

Recommended short name if you need to override naming manually:

```text
phase1-runtime-foundation
```

### 2. `004` - Phase 1 Discovery Runtime

Run this after `003` is created:

```text
/speckit.specify Read PLAN.md and create a specification for phase 4: Phase 1 Discovery Runtime ONLY. Scope includes F4 API Catalog and F5 WebMCP public runtime behavior, endpoint or script output, settings integration, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 001 and exclude F2-F3 and F6-F8.
```

Expected focus:

- `/.well-known/api-catalog`
- rewrite and endpoint availability
- custom entries plus WordPress and WooCommerce integration
- WebMCP script presence and absence
- disabled-feature behavior

Recommended short name if you need to override naming manually:

```text
phase1-discovery-runtime
```

### 3. `005` - Phase 2 Public Discovery Endpoints

Run this after `004` is created:

```text
/speckit.specify Read PLAN.md and create a specification for phase 5: Phase 2 Public Discovery Endpoints ONLY. Scope includes F6 MCP Server Card, F7 OAuth or OIDC Discovery, and F8 OAuth Protected Resource runtime behavior, public endpoints, validation dependencies on saved settings, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 002.
```

Expected focus:

- `/.well-known/mcp/server-card.json`
- `/.well-known/openid-configuration`
- `/.well-known/oauth-protected-resource`
- required-settings presence versus incomplete configuration
- protected-API applicability scenarios

Recommended short name if you need to override naming manually:

```text
phase2-public-discovery-endpoints
```

## After Each Spec

Run the normal Speckit flow in order:

1. `/speckit.specify`
2. `/speckit.clarify` if the generated spec leaves important open questions
3. `/speckit.plan`
4. `/speckit.tasks`

Do not jump to the next numbered runtime spec until the current one has at
least a completed `spec.md`.

## Boundary Notes

- `003` covers only `F2` and `F3`.
- `004` covers only `F4` and `F5`.
- `005` covers only `F6`, `F7`, and `F8`.
- Open quickstart validation tasks from `001` and `002` can be executed during
  runtime implementation, but they do not need separate numbered specs.
