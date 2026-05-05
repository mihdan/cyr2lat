# Task 04.08: Add REST tests for posts/pages/CPTs

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 4 - Extract post slug handling.

## Goal

Complete REST integration coverage for post slug behavior used by Gutenberg and block editor save paths.

## Scope

- Review `PostSlugRestIntegrationTest`.
- Cover post creation with Cyrillic title.
- Cover page creation with Cyrillic title.
- Cover REST-enabled custom post type creation.
- Cover explicit Cyrillic slug updates.
- Cover manual Latin slug updates.
- Cover autosave/revision behavior where practical.

## Acceptance criteria

- REST tests document Gutenberg/backend behavior without Playwright.
- Tests pass after `PostSlugService` extraction.
- Manual slugs and generated slugs keep current behavior.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/PostSlugRestIntegrationTest.php
composer phpcs
```
