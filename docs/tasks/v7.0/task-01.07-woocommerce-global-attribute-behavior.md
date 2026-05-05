# Task 01.07: Capture current WooCommerce global attribute behavior

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add behavior coverage for the current WooCommerce global attribute guard in `Main::sanitize_title()`.

The local WordPress integration environment does not currently include WooCommerce, so this task uses isolated minimal WooCommerce function stubs instead of adding WooCommerce as a required dependency. Full WooCommerce CRUD/API coverage remains a later Epic 7 task.

## Scope

- Verify that a registered global attribute taxonomy key such as `pa_цвет` is not transliterated by Cyr-To-Lat.
- Verify that a registered global attribute name such as `цвет` is not transliterated by Cyr-To-Lat.
- Verify that an unregistered Cyrillic value still transliterates normally.
- Verify that an already-Latin registered global attribute taxonomy key is preserved.

Because the tests call WordPress' real `sanitize_title()`, WordPress core still applies its own percent-encoding after Cyr-To-Lat declines to transliterate a registered Cyrillic attribute.

## Implemented Files

- `tests/integration/fixtures/woocommerce-global-functions.php`
- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`

## Acceptance Criteria

- Tests use WordPress' `sanitize_title()` filter path instead of calling `Main::sanitize_title()` directly.
- WooCommerce stubs are isolated from the rest of the integration suite.
- Tests do not add Codeception, Playwright, or WooCommerce as required dependencies.
- Unit tests and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
