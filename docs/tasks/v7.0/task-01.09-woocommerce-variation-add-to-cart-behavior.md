# Task 01.09: Capture current WooCommerce variation add-to-cart behavior

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add behavior coverage for the current frontend WooCommerce variation add-to-cart flow with a Cyrillic local product attribute.

The test creates a real variable product with local attribute `Цвет`, renders WooCommerce's variable add-to-cart form through the `woocommerce_variable_add_to_cart` action, then submits the rendered request key through WooCommerce's frontend add-to-cart handler. The current behavior is a mismatch: the form renders `attribute_czvet`, but the frontend handler currently rejects that rendered key and reports the local attribute as required.

## Scope

- Verify that the rendered variation form uses the current transliterated local attribute request key `attribute_czvet`.
- Verify that the rendered form does not use a URL-encoded Cyrillic request key.
- Verify that WooCommerce's frontend add-to-cart handler currently rejects the rendered key for the Cyrillic local attribute variation.
- Verify that the cart remains empty and WooCommerce reports `Цвет is a required field`.
- Do not cover cart/session reload; that is the next Epic 1 follow-up task.

## Implemented Files

- `tests/integration/WooCommerceVariationAddToCartIntegrationTest.php`

## Acceptance Criteria

- Tests use real WooCommerce product, variation, template, and cart handler APIs.
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
