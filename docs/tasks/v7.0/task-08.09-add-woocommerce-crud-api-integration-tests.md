# Task 08.09: Add WooCommerce CRUD/API integration tests

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Consolidate WooCommerce CRUD/API integration coverage for local and variation attribute key normalization introduced in Epic 8.

## Scope

- Add or extend WooCommerce integration tests for local product attributes.
- Add or extend WooCommerce integration tests for variation attribute keys.
- Cover negative cases for Latin keys and global `pa_*` keys.
- Keep tests backend-first without adding Codeception or Playwright.

## Acceptance criteria

- Integration tests cover WooCommerce CRUD/API local attribute saves.
- Integration tests cover WooCommerce variation attribute key saves.
- Regression tests prove Latin and global attribute keys are preserved.
- The Epic 8 test suite is documented in the task verification section.

## Covered Tests

- `WooCommerceLocalAttributeIntegrationTest` covers CRUD saves for Cyrillic local keys, Latin local keys, and explicit persistence without the broad `sanitize_title` bridge.
- `WooCommerceVariationAddToCartIntegrationTest` covers variable product/variation CRUD saves, normalized local variation meta keys, frontend add-to-cart, and cart session reload.
- `WooCommerceGlobalAttributeIntegrationTest` remains the regression suite for preserving registered `pa_*` global attribute identifiers from Epic 7.

## Implemented Files

- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`
- `tests/integration/WooCommerceVariationAddToCartIntegrationTest.php`
- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceGlobalAttributeIntegrationTest.php`
- `composer phpcs`