# Task 07.04: Add global attribute create/edit tests

## Status

Implemented.

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

## Implementation summary

- Reused existing `wc_create_attribute()` coverage for Cyrillic names, explicit Cyrillic slugs, and explicit Latin slugs.
- Added `wc_update_attribute()` coverage for changing only the attribute label while preserving the existing slug.
- Added `wc_update_attribute()` coverage for explicit Cyrillic slug normalization.
- Added `wc_update_attribute()` coverage for explicit Latin slug preservation.

## Acceptance criteria

- Tests use real WooCommerce functions and skip only when WooCommerce is unavailable.
- Create and update paths assert stored WooCommerce attribute slug values.
- Tests do not introduce browser or Codeception dependencies.

## Implemented Files

- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/task-07.04-global-attribute-create-edit-tests.md`

## Verification

```bash
vendor/bin/phpunit tests/integration/WooCommerceGlobalAttributeIntegrationTest.php
composer phpcs
```
