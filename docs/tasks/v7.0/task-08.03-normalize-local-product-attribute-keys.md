# Task 08.03: Normalize saved local product attribute keys explicitly

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Normalize saved WooCommerce local product attribute keys explicitly before product attribute metadata is persisted.

## Scope

- Identify the canonical WooCommerce local attribute key format in `_product_attributes`.
- Normalize Cyrillic local attribute names to transliterated keys.
- Preserve already-Latin local attribute keys.
- Preserve global `pa_*` attribute keys and registered taxonomy names.

## Acceptance criteria

- Saved local product attribute keys are transliterated consistently.
- Existing Latin keys are unchanged.
- Global attribute keys are not treated as local keys.
- Integration tests cover saved `_product_attributes` metadata.

## Implemented Files

- `src/php/Main.php`
- `src/php/Slugs/LocalAttributeService.php`
- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
- `vendor\bin\phpunit tests\unit\Slugs\LocalAttributeServiceTest.php`
- `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php src\php\Slugs\LocalAttributeService.php tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
