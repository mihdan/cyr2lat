# Task 03.04: Complete filename integration tests

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 3 - Extract filename handling.

## Goal

Review and complete filename integration coverage after moving filename logic into `FilenameService`.

The repository already has behavior-capture coverage for `sanitize_file_name()`. This task should keep that coverage green and add missing cases only where the extraction created new risk.

## Scope

- Review `tests/integration/FilenameIntegrationTest.php`.
- Ensure the plugin still registers the `sanitize_file_name` filter.
- Ensure Cyrillic filenames transliterate through WordPress' real `sanitize_file_name()` path.
- Ensure UTF-8 lowercasing is still covered.
- Ensure multi-extension filenames keep their current shape.
- Ensure `ctl_pre_sanitize_filename` still short-circuits through the real WordPress filter path.
- Add a macOS decomposed filename case if it is not already covered elsewhere at integration level.

## Out of scope

- Do not add browser tests.
- Do not add Codeception.
- Do not require a physical media upload unless the integration environment already supports it reliably.
- Do not change upload directory configuration.

## Acceptance criteria

- Filename integration coverage passes after extraction.
- Any added test uses WordPress' `sanitize_file_name()` function, not direct service calls.
- The test suite documents both plugin hook registration and real filename output.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/FilenameIntegrationTest.php
composer unit
composer phpcs
```
