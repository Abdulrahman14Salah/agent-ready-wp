# Agent Ready WP

Agent Ready WP is a WordPress plugin that adds an admin settings page for reviewing agent-readiness status and configuring Phase 1 and Phase 2 discovery/runtime capabilities.

The plugin is designed to help WordPress sites expose machine-readable signals for agents while keeping all runtime behavior controlled from `Settings > Agent Ready`.

Built by [ArqamWeb](https://arqamweb.com).

## Features

- Review the latest agent-readiness scan summary.
- Run a fresh readiness scan from `Settings > Agent Ready`.
- Configure Phase 1 capability panels.
- Serve Markdown responses for qualified `Accept: text/markdown` singular requests.
- Publish `Content-Signal` directives in generated `robots.txt`.
- Publish `/.well-known/api-catalog`.
- Expose a frontend WebMCP runtime asset.
- Publish Phase 2 discovery endpoints:
  - `/.well-known/mcp/server-card.json`
  - `/.well-known/openid-configuration`
  - `/.well-known/oauth-protected-resource`

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Installation

1. Copy this plugin into `wp-content/plugins/agent-ready-wp`.
2. Activate **Agent Ready WP** from the WordPress admin plugins screen.
3. Open `Settings > Agent Ready`.

## Repository Structure

- `agent-ready-wp.php` - main WordPress plugin bootstrap file.
- `src/` - plugin application, admin, runtime, integration, and WordPress infrastructure code.
- `assets/` - admin CSS/JS and frontend WebMCP runtime asset.
- `tests/` - unit and integration tests with WordPress function stubs.
- `specs/` - feature specifications, plans, contracts, and implementation tasks.
- `readme.txt` - WordPress.org-style plugin metadata and changelog.

## Development

```bash
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

Check PHP syntax:

```bash
find . -path ./vendor -prune -o -name '*.php' -exec php -l {} \;
```

## License

GPLv2 or later. See `readme.txt` for WordPress plugin metadata.
