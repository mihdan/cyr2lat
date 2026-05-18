# Task 04.04: Support explicit Cyrillic `post_name` normalization

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Normalize an explicitly supplied Cyrillic `post_name` through the post slug service.

## Scope

- Detect a non-empty explicit slug.
- Normalize Cyrillic input through WordPress-compatible slug cleanup.
- Keep Latin/manual slugs stable.
- Add direct service tests for explicit Cyrillic slug input.

## Acceptance criteria

- Explicit Cyrillic slug becomes Latin.
- Existing REST explicit Cyrillic slug coverage still passes.
- Manual Latin slugs are not unexpectedly changed.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php --filter explicit
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugRestIntegrationTest.php --filter explicit
composer phpcs
```
