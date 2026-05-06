# Task 07.06: Add frontend global attribute filtering tests where practical

## Status

Draft for review.

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

## Acceptance criteria

- Practical filtering coverage is added with WordPress PHPUnit integration tests.
- No Playwright or Codeception requirement is introduced.
- Deferred UI-only limitations are documented if the flow cannot be covered reliably in backend tests.

## Verification

```bash
vendor/bin/phpunit tests/integration/WooCommerceGlobalAttributeIntegrationTest.php
composer phpcs
```
