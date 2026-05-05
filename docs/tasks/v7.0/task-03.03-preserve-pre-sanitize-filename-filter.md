# Task 03.03: Preserve `ctl_pre_sanitize_filename`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 3 - Extract filename handling.

## Goal

Ensure the extracted filename service preserves the existing `ctl_pre_sanitize_filename` short-circuit filter exactly.

## Scope

- Keep the filter name `ctl_pre_sanitize_filename`.
- Keep the current default value of `false`.
- Keep the current filename argument passed to the filter.
- Ensure a non-`false` filter result bypasses lowercasing and transliteration.
- Add direct service-level tests for the short-circuit behavior.
- Keep existing integration coverage through WordPress' `sanitize_file_name()`.

## Out of scope

- Do not add new filename filters in this task.
- Do not change `ctl_table` or `ctl_locale` behavior.
- Do not change WordPress filename cleanup behavior outside Cyr-To-Lat.

## Acceptance criteria

- Unit tests fail if `ctl_pre_sanitize_filename` stops short-circuiting.
- Integration tests still prove the filter works through `sanitize_file_name()`.
- Existing public behavior remains unchanged.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/FilenameServiceTest.php --filter pre_sanitize
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/FilenameIntegrationTest.php --filter pre_sanitize
composer phpcs
```
