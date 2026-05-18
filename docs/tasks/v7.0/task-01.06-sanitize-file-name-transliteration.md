# Task 01.06: Capture sanitize_file_name transliteration

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add integration coverage for the current filename transliteration behavior that runs through WordPress' real `sanitize_file_name()` function and the plugin's `sanitize_file_name` filter.

This task captures existing behavior only. It does not introduce `FilenameService` and does not change the current filter registration.

## Scope

- Verify that the plugin registers `sanitize_file_name` for `Main::sanitize_filename()`.
- Verify Cyrillic filename transliteration.
- Verify UTF-8 lowercasing before transliteration.
- Verify WordPress whitespace normalization is preserved before transliteration.
- Verify multi-extension filenames keep their current shape.
- Verify `ctl_pre_sanitize_filename` can short-circuit the plugin result.

The physical media upload pipeline is left for later filename-service tasks because it depends on the local WordPress uploads directory being writable in the test environment.

## Implemented Files

- `tests/integration/FilenameIntegrationTest.php`

## Acceptance Criteria

- Tests use WordPress' `sanitize_file_name()` instead of calling `Main::sanitize_filename()` directly.
- Tests run through the standard WordPress PHPUnit integration suite.
- Unit tests and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
