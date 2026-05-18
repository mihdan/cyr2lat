# Task 07.06: Add frontend global attribute filtering tests where practical

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Cover the backend-reproducible part of WooCommerce frontend filtering by global attributes without adding required browser dependencies.

## Scope

- Identify the WooCommerce query/filtering entry point used for global attribute archive/filter requests.
- Add an integration test for product lookup by a transliterated global attribute taxonomy and term slug where practical.
- Confirm that already registered `pa_*` taxonomy names are not transliterated during frontend query parsing.
- Document any UI-only coverage deferred outside the required 7.0 test suite.

## Implementation summary

- Identified backend frontend-filter entry point as `WC_Query::get_layered_nav_chosen_attributes()`.
- Added integration coverage for `filter_{attribute}` query parsing with a registered `pa_*` global attribute taxonomy.
- Verified the registered `pa_*` taxonomy key remains unchanged through `sanitize_title()`.
- Verified selected transliterated term slugs are kept in WooCommerce layered-navigation chosen attributes.
- Browser/UI rendering is intentionally not added to the required suite; backend query parsing is covered without Playwright or Codeception.

## Acceptance criteria

- Practical filtering coverage is added with WordPress PHPUnit integration tests.
- No Playwright or Codeception requirement is introduced.
- Deferred UI-only limitations are documented if the flow cannot be covered reliably in backend tests.

## Implemented Files

- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/task-07.06-frontend-global-attribute-filtering-tests.md`

## Verification

```bash
vendor/bin/phpunit tests/integration/WooCommerceGlobalAttributeIntegrationTest.php
composer phpcs
```
