# Task 03.01: Create `FilenameService`

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 3 - Extract filename handling.

## Goal

Create a dedicated `FilenameService` for attachment filename normalization while keeping the current `Main::sanitize_filename()` public wrapper and `sanitize_file_name` hook behavior unchanged.

## Scope

- Add `CyrToLat\Slugs\FilenameService`.
- Give the service access to the transliteration core introduced in Epic 2.
- Add direct unit coverage for the service.
- Keep `Main::sanitize_filename()` in place for compatibility.
- Do not change the registered WordPress hook in this task.

## Out of scope

- Do not remove `Main::sanitize_filename()`.
- Do not change `sanitize_file_name` registration.
- Do not change `ctl_pre_sanitize_filename` behavior.
- Do not change upload directory behavior or media upload flow.

## Acceptance criteria

- `FilenameService` exists and is autoloaded.
- The service can transliterate a filename through `Transliterator`.
- Existing filename behavior remains unchanged.
- Direct unit tests cover the new service entry point.

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/FilenameServiceTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter sanitize_filename
composer phpcs
```
