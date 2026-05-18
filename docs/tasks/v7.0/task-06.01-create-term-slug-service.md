# Task 06.01: Create `TermSlugService`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Create a dedicated `TermSlugService` for term slug context and preservation logic while keeping the existing `Main` hook callbacks available.

## Scope

- Add `CyrToLat\Slugs\TermSlugService`.
- Add initial unit coverage for the service boundary.
- Do not change hook registration in this task.

## Acceptance criteria

- `TermSlugService` exists and is autoloaded.
- Initial service tests pass.
- No behavior changes are introduced.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/TermSlugServiceTest.php
composer phpcs
```
