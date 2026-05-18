# Task 06.05: Add explicit term slug tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Ensure explicit term slug arguments keep current behavior after `TermSlugService` extraction.

## Scope

- Cover explicit Cyrillic term slug transliteration.
- Cover explicit Latin term slug preservation.
- Cover unique suffix behavior for encoded existing Cyrillic slugs.

## Acceptance criteria

- Existing explicit slug integration tests pass.
- Additional service or integration tests are added where extraction creates risk.
- No behavior changes are introduced for current term APIs.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/TermSlugIntegrationTest.php --filter slug
vendor/bin/phpunit tests/unit/Slugs/TermSlugServiceTest.php
composer phpcs
```
