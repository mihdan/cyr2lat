# Cyr-To-Lat Integration Tests

Integration tests use the standard WordPress PHPUnit test suite through `wp-phpunit/wp-phpunit`.

No `install-wp-tests.sh` script is required.

## Local Environment Rules

- Do not use a shared `wp-tests` database from another project.
- Local defaults live in `tests/integration/_config/params.local.php`.
- CI defaults live in `tests/integration/_config/params.github-actions.php`.
- The default local database is `cyr2lat-7-tests`.

## Required Local Inputs

- `WP_PHPUNIT__TESTS_CONFIG` is optional. By default, the bootstrap uses `tests/integration/wp-tests-config.php`.
- `WP_TESTS_DIR` is optional and only needed when using an external WordPress test library instead of the Composer package.
- `WP_TESTS_CONFIG_FILE_PATH` is supported as a compatibility alias for `WP_PHPUNIT__TESTS_CONFIG`.
- WooCommerce-dependent tests expect WooCommerce to be installed as `wp-content/plugins/woocommerce/woocommerce.php` inside the WordPress installation used by the integration test config.

For local runs, set `WP_ROOT_PATH` in `tests/integration/_config/params.local.php` to the WordPress installation that contains the required plugins.
To use a private config file without editing tracked params, set `CYR2LAT_TEST_PARAMS` to that file.

## Run

```bash
composer integration
```

If you use an ignored local `phpunit.integration.xml`, run:

```bash
vendor/bin/phpunit -c phpunit.integration.xml
```
