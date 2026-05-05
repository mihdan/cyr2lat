# Task 01.04: Capture REST Gutenberg save paths

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add REST integration coverage for the backend save paths used by Gutenberg/block editor.

This task captures current REST behavior through real WordPress REST requests. It does not add browser, Playwright, Codeception, or acceptance coverage.

## Scope

- Create a post through `/wp/v2/posts` with a Cyrillic title and empty slug.
- Create a page through `/wp/v2/pages` with a Cyrillic title and empty slug.
- Create a REST-enabled custom post type item with a Cyrillic title and empty slug.
- Create a draft through REST, verify the generated slug exposed by WordPress, then publish the draft and verify the final stored slug.
- Update a post through REST with an explicit Cyrillic slug.
- Update a post through REST with an explicit Latin/manual slug.
- Verify a manual Latin slug is not overwritten when the title changes.
- Verify duplicate Cyrillic titles receive unique Latin slugs.

Autosave/revision REST endpoints are intentionally left for a follow-up test task because they use a separate controller and lifecycle.

## Implemented Files

- `tests/integration/PostSlugRestIntegrationTest.php`

## Acceptance Criteria

- Tests use `WP_REST_Request` and `rest_do_request()`.
- Tests run through the standard WordPress PHPUnit integration suite.
- Tests do not require browser automation or Codeception.
- Unit tests and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
