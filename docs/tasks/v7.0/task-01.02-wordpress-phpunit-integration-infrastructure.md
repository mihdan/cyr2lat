# Task 01.02: Add WordPress PHPUnit integration test infrastructure

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 – Behavior capture before refactor.

## Goal

Add the baseline infrastructure needed to run backend integration tests through the standard WordPress PHPUnit test suite.

This task establishes the integration lane only. It does not yet add full post, term, REST, filename, or WooCommerce behavior coverage.

## Decisions

- Use standard WordPress PHPUnit integration tests through `wp-phpunit/wp-phpunit`.
- Do not use `install-wp-tests.sh`.
- Do not add Codeception.
- Do not add acceptance/browser infrastructure.
- Use tracked WordPress PHPUnit config files modeled after the existing hCaptcha Codeception params split.
- Use `tests/integration/_config/params.local.php` for local defaults and `tests/integration/_config/params.github-actions.php` for CI defaults.
- Use a project-specific local test database, not a shared `wp-tests` database from another project.

## Implemented Files

- `.gitignore`
- `composer.json`
- `.github/workflows/ci.yml`
- `phpunit.integration.xml`
- `tests/integration/bootstrap.php`
- `tests/integration/wp-tests-config.php`
- `tests/integration/_config/params.php`
- `tests/integration/_config/params.local.php`
- `tests/integration/_config/params.github-actions.php`
- `tests/integration/_config/params.example.php`
- `tests/integration/PluginLoadedTest.php`
- `tests/integration/README.md`

## Local Environment Rules

- Keep local integration defaults in `tests/integration/_config/params.local.php`.
- Keep CI integration defaults in `tests/integration/_config/params.github-actions.php`.
- Override with `CYR2LAT_TEST_PARAMS` or `WP_PHPUNIT__TESTS_CONFIG` only when a machine needs a private config.

## Acceptance Criteria

- `composer integration` exists.
- `phpunit.integration.xml` is tracked and delegates WordPress PHPUnit config discovery to the integration bootstrap.
- `wp-phpunit/wp-phpunit` and `yoast/phpunit-polyfills` are available as dev dependencies.
- The repository includes local and CI integration test params.
- GitHub Actions installs WordPress and WooCommerce before running `composer integration`.
- The integration bootstrap loads the plugin through the WordPress test-suite lifecycle.
- A first smoke integration test verifies that `cyr_to_lat()` is available and returns `CyrToLat\Main`.
- Unit tests and coding standards still pass.

## Verification

Run when a local WordPress PHPUnit test suite and project-specific test DB are configured:

```bash
composer integration
```

Always run:

```bash
composer unit
composer phpcs
```
