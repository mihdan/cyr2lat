# Task 05.02: Move `check_for_changed_slugs()` logic

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 5 - Extract old slug redirect handling.

## Goal

Move the old slug redirect protection logic from `Main::check_for_changed_slugs()` into `OldSlugRedirectService`.

## Scope

- Keep the `post_updated` hook callback name unchanged.
- Make `Main::check_for_changed_slugs()` delegate to the service.
- Preserve published post and attachment behavior.
- Preserve hierarchical post type skip behavior.
- Preserve the Cyrillic title rawurlencode behavior.

## Acceptance criteria

- Existing `MainTest` old slug behavior still passes.
- Direct service tests cover the moved logic.
- No database conversion behavior changes are introduced.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/OldSlugRedirectServiceTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter check_for_changed_slugs
composer phpcs
```
