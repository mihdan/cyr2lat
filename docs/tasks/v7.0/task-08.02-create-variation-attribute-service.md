# Task 08.02: Create `VariationAttributeService`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Decide whether variation attribute behavior is cleaner as a separate service and create that service if the local attribute service boundary would otherwise become too broad.

## Scope

- Review variation attribute storage and request keys such as `attribute_*` and `attribute_pa_*`.
- Add `CyrToLat\Slugs\VariationAttributeService` when separation improves clarity.
- Keep global attribute taxonomy behavior delegated to `GlobalAttributeService`.
- Preserve existing frontend variation matching behavior.

## Acceptance criteria

- The separation decision is documented in this task.
- If created, `VariationAttributeService` is autoloaded and covered by unit tests.
- Variation-specific behavior is not mixed into global attribute taxonomy handling.
- Existing variation add-to-cart tests keep passing.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/VariationAttributeServiceTest.php tests/unit/Slugs/LocalAttributeServiceTest.php
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/WooCommerceVariationAddToCartIntegrationTest.php
composer phpcs
```
