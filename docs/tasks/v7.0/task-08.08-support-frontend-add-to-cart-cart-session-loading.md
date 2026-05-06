# Task 08.08: Support frontend add-to-cart and cart session loading

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Ensure WooCommerce frontend add-to-cart requests and cart session loading work with Cyrillic local variation attributes after explicit key normalization.

## Scope

- Preserve frontend variation matching for local attributes with Cyrillic source names.
- Preserve cart item attribute data after add-to-cart.
- Preserve cart session reload behavior.
- Avoid reintroducing broad frontend `sanitize_title` dependency for variation request keys.

## Acceptance criteria

- Frontend add-to-cart succeeds for products with Cyrillic local variation attributes.
- Cart item variation data uses normalized keys consistently.
- Cart session reload restores the item and variation attributes.
- Existing global attribute frontend behavior remains covered by Epic 7 tests.

## Implemented Files

- `src/php/Main.php`
- `tests/integration/WooCommerceVariationAddToCartIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Notes

- `sanitize_title()` now returns lowercase transliterated slugs for non-preserved values, matching WooCommerce's normalized saved attribute keys.
- `woocommerce_after_template_part_filter()` no longer removes the global `sanitize_title` filter when the current request is already allowed, so the rendered variation key remains compatible with the subsequent add-to-cart request.

## Verification

- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
- `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
