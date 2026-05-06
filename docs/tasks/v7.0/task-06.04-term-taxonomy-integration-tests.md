# Task 06.04: Add category/tag/custom taxonomy/product taxonomy tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Ensure term slug extraction remains covered through real WordPress term APIs.

## Scope

- Review `TermSlugIntegrationTest`.
- Cover categories, tags, and custom taxonomies.
- Cover WooCommerce product taxonomies where available.
- Keep WooCommerce-specific assertions skipped when WooCommerce is unavailable.

## Acceptance criteria

- WordPress integration tests pass after extraction.
- Product taxonomy coverage exists or is explicitly skipped.
- Tests use `wp_insert_term()`, not direct service calls.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/TermSlugIntegrationTest.php
composer phpcs
```
