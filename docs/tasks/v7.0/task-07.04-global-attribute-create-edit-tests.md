# Task 07.04: Add global attribute create/edit tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Add integration coverage for creating and editing WooCommerce global attributes through the supported WooCommerce API surface.

## Scope

- Cover `wc_create_attribute()` with Cyrillic names.
- Cover `wc_create_attribute()` with explicit Cyrillic slugs.
- Cover `wc_update_attribute()` when changing names without changing slugs.
- Cover `wc_update_attribute()` when changing explicit Cyrillic slugs.
- Preserve tests for manual Latin slugs.

## Acceptance criteria

- Tests use real WooCommerce functions and skip only when WooCommerce is unavailable.
- Create and update paths assert stored WooCommerce attribute slug values.
- Tests do not introduce browser or Codeception dependencies.

## Verification

```bash
vendor/bin/phpunit tests/integration/WooCommerceGlobalAttributeIntegrationTest.php
composer phpcs
```
