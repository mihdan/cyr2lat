# Cyr-To-Lat Integration Tests

Integration tests use the standard WordPress PHPUnit test suite through `wp-phpunit/wp-phpunit`.

No `install-wp-tests.sh` script is required.

## Local Environment Rules

- Do not use a shared `wp-tests` database from another project.
- Do not commit a test database name, database user, database password, or local database host.
- Keep local DB settings in a local `wp-tests-config.php` file or local environment variables.

## Required Local Inputs

- `WP_PHPUNIT__TESTS_CONFIG` should point to a local `wp-tests-config.php` file.
- `WP_TESTS_DIR` is optional and only needed when using an external WordPress test library instead of the Composer package.
- `WP_TESTS_CONFIG_FILE_PATH` is supported as a compatibility alias for `WP_PHPUNIT__TESTS_CONFIG`.

The local `wp-tests-config.php` should use a project-specific database for this repository.
Use `tests/integration/wp-tests-config.example.php` as a placeholder-only starting point, but keep the real file outside the repository.

## Run

```bash
composer integration
```

If you use an ignored local `phpunit.integration.xml`, run:

```bash
vendor/bin/phpunit -c phpunit.integration.xml
```
