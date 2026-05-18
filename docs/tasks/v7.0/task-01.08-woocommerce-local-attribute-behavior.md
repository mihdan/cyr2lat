# Task 01.08: Capture current WooCommerce local attribute behavior

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add behavior coverage for the current WooCommerce local product attribute save behavior before extracting explicit WooCommerce attribute services.

The test uses a real WooCommerce product object and a real `WC_Product_Attribute`. WooCommerce indexes product attributes through `sanitize_title()`, so the coverage verifies the plugin through WooCommerce's product CRUD path rather than calling `Main::sanitize_title()` directly.

## Scope

- Verify that a simple product with local attribute `Цвет` is saved and reloaded with the current transliterated internal key `czvet`.
- Verify that the local attribute display name and options stay Cyrillic and human-readable.
- Verify that a Latin local attribute key is preserved.
- Verify that WooCommerce reaches WordPress' `sanitize_title` filter while indexing the local attribute.
- Do not cover frontend variation add-to-cart or cart/session loading; those are separate follow-up tasks.

## Implemented Files

- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`

## Acceptance Criteria

- Tests use real WooCommerce product CRUD classes.
- Tests load WooCommerce through the reusable plugin integration test case layer.
- Tests skip when WooCommerce is not available in the integration test environment.
- Tests do not add Codeception, Playwright, or acceptance/browser infrastructure.
- Unit tests, integration tests, and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
