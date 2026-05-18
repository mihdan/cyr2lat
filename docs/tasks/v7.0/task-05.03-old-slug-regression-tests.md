# Task 05.03: Add `_wp_old_slug` regression tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 5 - Extract old slug redirect handling.

## Goal

Strengthen regression coverage for old slug redirect behavior after extracting `OldSlugRedirectService`.

## Scope

- Cover unchanged slugs.
- Cover non-published statuses.
- Cover hierarchical post types.
- Cover automatically generated transliterated slugs from an empty previous slug.
- Cover attachments with `inherit` status.

## Acceptance criteria

- Tests fail if automatic transliteration creates a broken `_wp_old_slug` value.
- Tests fail if hierarchical post types start being processed unexpectedly.
- Tests pass through the service and the legacy `Main` wrapper.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/OldSlugRedirectServiceTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter check_for_changed_slugs
composer unit
composer phpcs
```
