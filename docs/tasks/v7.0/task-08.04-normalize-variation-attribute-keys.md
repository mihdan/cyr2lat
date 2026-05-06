# Task 08.04: Normalize variation attribute keys explicitly

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Normalize WooCommerce variation attribute meta keys explicitly so variation matching does not depend on broad `sanitize_title` behavior.

## Scope

- Normalize local variation meta keys such as `attribute_color` from Cyrillic attribute names.
- Preserve global variation meta keys such as `attribute_pa_color`.
- Preserve variation attribute values and term slugs according to their existing services.
- Cover create/update variation flows where keys are saved through WooCommerce CRUD.

## Acceptance criteria

- Variation attribute meta keys are transliterated for local attributes.
- Registered global attribute meta keys are preserved.
- Variation matching remains compatible with normalized parent product attribute keys.
- Integration tests cover variation metadata after save.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/WooCommerceVariationAddToCartIntegrationTest.php
composer phpcs
```
