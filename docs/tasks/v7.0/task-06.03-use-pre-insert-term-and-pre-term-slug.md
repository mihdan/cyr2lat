# Task 06.03: Use `pre_insert_term` and `pre_term_slug` explicitly

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Keep term slug behavior tied to explicit WordPress term hooks rather than broad request state.

## Scope

- Keep `pre_insert_term` context capture explicit.
- Keep `pre_term_slug` guard behavior compatible.
- Move or wrap the `pre_term_slug` decision in term slug service where practical.

## Acceptance criteria

- `pre_insert_term` and `pre_term_slug` tests pass.
- Existing encoded term slug preservation still works.
- Non-term `sanitize_title` behavior is unchanged.

## Verification

```bash
vendor/bin/phpunit tests/unit/MainTest.php --filter "pre_term_slug|pre_insert_term"
vendor/bin/phpunit tests/unit/Slugs/TermSlugServiceTest.php
composer phpcs
```
