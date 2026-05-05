# Task 04.05: Preserve manual Latin slugs

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Ensure the post slug service does not overwrite a manually supplied Latin slug.

## Scope

- Add unit tests for manual Latin slugs.
- Preserve existing integration coverage.
- Avoid replacing user-provided slugs with title-derived slugs.

## Acceptance criteria

- A non-empty Latin `post_name` remains unchanged.
- REST/manual slug tests remain green.
- Service tests fail if manual slugs are overwritten.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/PostSlugServiceTest.php --filter manual
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugIntegrationTest.php --filter manual
composer phpcs
```
