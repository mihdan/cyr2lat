# Task 03.02: Move `sanitize_filename()` logic from `Main`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 3 - Extract filename handling.

## Goal

Move the filename sanitization logic currently implemented in `Main::sanitize_filename()` into `FilenameService`, with `Main` delegating to the service.

## Scope

- Move UTF-8 detection and lowercasing behavior into `FilenameService`.
- Move final transliteration through `Transliterator` into `FilenameService`.
- Keep `Main::sanitize_filename()` as a compatibility wrapper.
- Keep the `sanitize_file_name` hook callback unchanged.
- Preserve behavior across supported WordPress versions, including the `wp_is_valid_utf8` switch for WordPress 6.9+.

## Out of scope

- Do not change filename output.
- Do not change physical file rename behavior in background conversion.
- Do not change media upload storage paths.
- Do not introduce new public filters.

## Acceptance criteria

- `Main::sanitize_filename()` delegates to `FilenameService`.
- Existing `MainTest` filename tests still pass.
- `FilenameServiceTest` covers direct service behavior.
- Existing filename integration tests still pass.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/FilenameServiceTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter sanitize_filename
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/FilenameIntegrationTest.php
composer phpcs
```
