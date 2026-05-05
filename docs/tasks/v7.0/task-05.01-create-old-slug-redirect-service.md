# Task 05.01: Create `OldSlugRedirectService`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 5 - Extract old slug redirect handling.

## Goal

Create a dedicated service for `_wp_old_slug` redirect protection while keeping the existing `Main::check_for_changed_slugs()` callback available.

## Scope

- Add `CyrToLat\Slugs\OldSlugRedirectService`.
- Add a service method shaped like the `post_updated` callback.
- Add initial unit coverage for the service boundary.

## Acceptance criteria

- `OldSlugRedirectService` exists and is autoloaded.
- Initial service tests pass.
- No behavior changes are introduced in this task.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/OldSlugRedirectServiceTest.php
composer phpcs
```
