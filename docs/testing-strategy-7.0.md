# Cyr-To-Lat 7.0 testing strategy

Version 7.0 uses a backend-first testing strategy. The plugin normalizes stored WordPress and WooCommerce data, so release confidence must come from direct unit and backend integration coverage rather than from required browser automation.

## Required release layers

1. Unit tests for pure transliteration logic, slug services, bridge behavior, background conversion, converter settings, and WP-CLI command handling.
2. WordPress PHPUnit integration tests for post, page, custom post type, term, explicit slug, background conversion, WP-CLI, and multilingual slug paths where practical.
3. REST integration tests for Gutenberg/block-editor coverage, because the relevant plugin-facing behavior is the backend REST save path.
4. WooCommerce integration tests for products, product taxonomies, global attributes, local attributes, variation attributes, frontend add-to-cart, cart session loading, CRUD/API saves, and admin-handler saves.
5. PHP_CodeSniffer checks through the project PHPCS configuration.

## Non-required layers for 7.0

Codeception, Playwright, acceptance tests, and browser-based end-to-end tests are not required release dependencies for 7.0. They must not replace the required unit, WordPress PHPUnit integration, REST integration, and WooCommerce integration coverage.

Playwright may be revisited later as optional smoke coverage only after a concrete UI-only regression is identified that cannot be reproduced through backend entry points.

## Release verification command groups

Run all relevant test groups after code changes that affect release behavior:

```bash
vendor/bin/phpunit tests/unit
vendor/bin/phpunit -c phpunit.integration.xml tests/integration
composer phpcs
```

Targeted development runs may use narrower files or filters, but release readiness requires the relevant downstream unit and integration suites for the changed behavior.
