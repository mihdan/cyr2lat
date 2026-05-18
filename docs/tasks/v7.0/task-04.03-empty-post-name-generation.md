# Task 04.03: Support empty `post_name` generation

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Generate a slug from the raw post title when WordPress saves a post object with an empty `post_name`.

## Scope

- Handle empty `post_name` in `PostSlugService`.
- Use WordPress-compatible slug cleanup.
- Preserve current transliteration output.
- Cover posts, pages, and custom post types where practical.

## Acceptance criteria

- Empty `post_name` plus Cyrillic title produces a Latin slug.
- Empty title or missing title leaves `post_name` unchanged.
- Existing integration tests for post slug generation still pass.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php --filter empty
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugIntegrationTest.php
composer phpcs
```
