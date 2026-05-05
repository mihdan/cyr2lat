# Task 01.07: Capture current WooCommerce global attribute behavior

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add behavior coverage for the current WooCommerce global attribute creation behavior and registered global attribute guard in `Main::sanitize_title()`.

The WooCommerce integration test class uses a dedicated `WooCommerceIntegrationTestCase` layer that activates the real WooCommerce plugin for the test class and deactivates it after the class. The layer uses `woocommerce/woocommerce.php` from `WP_PLUGIN_DIR` when present, or an external local WooCommerce checkout from `CYR2LAT_WC_PLUGIN_FILE` / `C:/laragon/www/test/wp-content/plugins/woocommerce/woocommerce.php` for the local integration environment. Full WooCommerce CRUD/API coverage remains a later Epic 7 task.

## Scope

- Verify that WooCommerce is loaded as a real plugin for isolated WooCommerce integration tests when available.
- Verify that `wc_create_attribute()` with a Cyrillic name reaches WordPress' `sanitize_title` filter and currently stores the transliterated global attribute slug.
- Verify that `wc_create_attribute()` with an explicit Cyrillic slug reaches WordPress' `sanitize_title` filter and currently stores the transliterated global attribute slug.
- Verify that `wc_create_attribute()` preserves an explicit Latin/manual slug.
- Verify that a registered global attribute taxonomy key such as `pa_czvet`, produced from a real WooCommerce global attribute, is checked against the WooCommerce attribute registry and preserved by Cyr-To-Lat.

## Implemented Files

- `tests/integration/bootstrap.php`
- `tests/integration/WooCommerceIntegrationTestCase.php`
- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`

## Acceptance Criteria

- Tests use WordPress' `sanitize_title()` filter path instead of calling `Main::sanitize_title()` directly.
- Tests use real WooCommerce `wc_create_attribute()` for global attribute creation.
- Tests load WooCommerce through a reusable integration test case layer instead of process-level isolation or local stubs.
- Tests skip when WooCommerce is not available in the local integration environment.
- Tests do not add Codeception or Playwright.
- Unit tests and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
