# Task 08.07: Support WooCommerce REST/API product save flow

## Status

Draft for review.

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

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/WooCommerceLocalAttributeIntegrationTest.php tests/integration/WooCommerceVariationAddToCartIntegrationTest.php
composer phpcs
```
