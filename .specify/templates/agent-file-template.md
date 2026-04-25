# [PROJECT NAME] Development Guidelines

Auto-generated from all feature plans. Last updated: [DATE]

## Active Technologies

[EXTRACTED FROM ALL PLAN.MD FILES]

## Project Structure

```text
[ACTUAL STRUCTURE FROM PLANS]
```

## WordPress Plugin Constraints

- Prefer WordPress-native APIs, hooks, filters, options, transients, rewrite
  rules, and lifecycle hooks before introducing custom infrastructure
- Sanitize input, escape output, enforce capabilities and nonces on privileged
  actions, and use the WordPress HTTP API for remote requests
- Preserve default site behavior when a feature is disabled or prerequisites are
  unavailable
- Keep plugin-owned identifiers prefixed and localize user-facing strings with
  the configured text domain

## Commands

[ONLY COMMANDS FOR ACTIVE TECHNOLOGIES]

## Code Style

[LANGUAGE-SPECIFIC, ONLY FOR LANGUAGES IN USE, including WordPress coding
standards when PHP/WordPress is active]

## Recent Changes

[LAST 3 FEATURES AND WHAT THEY ADDED]

<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
