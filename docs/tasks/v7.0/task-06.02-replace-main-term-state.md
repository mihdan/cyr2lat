# Task 06.02: Replace `$is_term` / `$taxonomies` state

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Move term-processing state out of `Main` and into `TermSlugService`.

## Scope

- Move the current `$is_term` and `$taxonomies` state into `TermSlugService`.
- Keep `Main::pre_insert_term_filter()` and `Main::get_terms_args_filter()` as delegating wrappers.
- Keep `Main::sanitize_title()` behavior unchanged while consuming term context from the service.

## Acceptance criteria

- Existing term-related `MainTest` coverage passes.
- Service tests cover context capture and one-shot consumption.
- `Main` no longer owns mutable term context fields.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/TermSlugServiceTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter "pre_insert_term|terms_args|term_slug|sanitize_title"
composer phpcs
```
