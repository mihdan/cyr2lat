# Task 01.10: Capture current WooCommerce cart session behavior

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add behavior coverage for WooCommerce cart/session loading with a Cyrillic local product attribute.

The test creates a real WooCommerce variable product with local attribute `Цвет`, stores a cart item with the current local attribute variation key `attribute_czvet` in WooCommerce's session, then reloads the cart through `WC_Cart_Session::get_cart_from_session()`.

This documents the backend session reload behavior separately from the frontend add-to-cart mismatch captured in Task 01.09.

## Scope

- Verify that WooCommerce's real `woocommerce_load_cart_from_session` action runs during cart reload.
- Verify that a session cart item for a Cyrillic local attribute variation is restored into the cart.
- Verify that the restored cart item preserves `attribute_czvet => Красный`.
- Verify that the restored cart item keeps the expected parent product ID, variation ID, and quantity.
- Do not cover browser/acceptance flows or frontend form submission; Task 01.09 already captures that behavior.

## Implemented Files

- `tests/integration/WooCommerceCartSessionIntegrationTest.php`

## Acceptance Criteria

- Tests use real WooCommerce product, variation, cart, and session APIs.
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
