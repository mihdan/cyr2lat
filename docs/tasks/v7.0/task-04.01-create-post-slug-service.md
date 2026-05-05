# Task 04.01: Create `PostSlugService`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Create a dedicated `PostSlugService` for post slug handling while keeping `Main::sanitize_post_name()` as the current WordPress hook callback.

## Scope

- Add `CyrToLat\Slugs\PostSlugService`.
- Move no hook registration in this task.
- Add direct unit coverage for the service.
- Keep public behavior unchanged until later Epic 4 tasks switch callers.

## Acceptance criteria

- `PostSlugService` exists and is autoloaded.
- The service exposes a clear method for filtering `wp_insert_post_data`.
- Unit tests cover the initial service boundary.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php
composer phpcs
```
