# Task 04.06: Skip autosaves, revisions, and auto-drafts

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Ensure the post slug service skips transient save paths such as autosaves, revisions, and auto-drafts.

## Scope

- Keep `auto-draft` skipped.
- Keep revision saves skipped.
- Add autosave/revision guards where the available hook arguments make that safe.
- Add direct service coverage for skipped statuses/types.

## Acceptance criteria

- Auto-drafts do not receive generated slugs.
- Revisions do not receive generated slugs.
- Existing REST autosave/revision coverage remains green.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php --filter skip
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugRestIntegrationTest.php --filter autosave
composer phpcs
```
