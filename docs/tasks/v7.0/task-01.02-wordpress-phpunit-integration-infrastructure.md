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
- Keep local DB details out of tracked files.
- Use a project-specific local test database, not a shared `wp-tests` database from another project.

## Implemented Files

- `.gitignore`
- `composer.json`
- `phpunit.integration.xml`
- `tests/integration/bootstrap.php`
- `tests/integration/PluginLoadedTest.php`
- `tests/integration/README.md`
- `tests/integration/wp-tests-config.example.php`

## Local Environment Rules

- Do not commit the test database name.
- Do not commit the database user.
- Do not commit the database password.
- Do not commit the local database host.
- Keep DB settings in a local `wp-tests-config.php` or local environment variables.
- Point `WP_PHPUNIT__TESTS_CONFIG` to the local `wp-tests-config.php`.

## Acceptance Criteria

- `composer integration` exists.
- `phpunit.integration.xml` is tracked and contains no DB credentials.
- `wp-phpunit/wp-phpunit` and `yoast/phpunit-polyfills` are available as dev dependencies.
- The repository may include placeholder-only config examples, but no real local DB values.
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
