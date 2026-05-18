# Task 04.07: Add post/page/CPT/product slug tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Complete backend integration coverage for standard post object slug generation after extracting `PostSlugService`.

## Scope

- Review existing post slug integration tests.
- Cover posts, pages, and a REST-enabled custom post type.
- Cover WooCommerce products where WooCommerce is available.
- Skip WooCommerce-specific assertions when WooCommerce is unavailable.

## Acceptance criteria

- Integration coverage proves post/page/CPT slug generation through WordPress save paths.
- Product slug coverage exists or is explicitly skipped when WooCommerce is unavailable.
- Tests do not require browser automation.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugIntegrationTest.php
composer phpcs
```
