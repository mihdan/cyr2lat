# Task 08.07: Support WooCommerce REST/API product save flow

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Ensure WooCommerce REST and CRUD product save flows normalize local and variation attribute keys without broad `sanitize_title` dependency.

## Scope

- Cover WooCommerce CRUD product attribute saves.
- Cover WooCommerce REST product create/update payloads where practical in the integration suite.
- Normalize local product and variation attribute keys consistently across API paths.
- Preserve global `pa_*` identifiers.

## Acceptance criteria

- CRUD/API-created products store normalized local attribute keys.
- CRUD/API-created variations store normalized local variation keys.
- REST/API global attribute references remain unchanged.
- Integration tests cover the supported API paths.

## Covered path

The integration suite covers the WooCommerce CRUD layer used by REST controllers after request parsing: `WC_Product_Simple`, `WC_Product_Variable`, and `WC_Product_Variation` objects are saved through WooCommerce data stores with explicit local and variation key normalization before persistence. Direct REST controller execution remains out of scope for the current lightweight integration suite, but the shared CRUD save path used by REST payloads is covered.

## Implemented Files

- `src/php/Main.php`
- `src/php/Slugs/LocalAttributeService.php`
- `src/php/Slugs/VariationAttributeService.php`
- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`
- `tests/integration/WooCommerceVariationAddToCartIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
- `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php src\php\Slugs\LocalAttributeService.php src\php\Slugs\VariationAttributeService.php tests\integration\WooCommerceLocalAttributeIntegrationTest.php tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
