# Task 04.02: Register `wp_insert_post_data` with 4 args

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Update post slug hook registration to accept all four modern `wp_insert_post_data` arguments.

## Scope

- Register `wp_insert_post_data` with accepted args `4`.
- Update `Main::sanitize_post_name()` wrapper signature.
- Pass `$data`, `$postarr`, `$unsanitized_postarr`, and `$update` to `PostSlugService`.
- Preserve existing callback name for compatibility.

## Acceptance criteria

- Hook registration uses priority `10` and accepted args `4`.
- Existing tests are updated to expect the new registration shape.
- Existing post slug tests still pass.

## Verification

```bash
vendor/bin/phpunit tests/unit/MainTest.php --filter init_hooks
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php
composer phpcs
```
