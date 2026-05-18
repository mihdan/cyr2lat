# Task 02.01: Create `Transliterator` service

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Create a dedicated internal `Transliterator` service responsible for converting text with the active Cyr-To-Lat conversion table, while keeping the current public plugin behavior unchanged.

This task starts the extraction of low-level transliteration behavior from `CyrToLat\Main`. It should introduce the service and route a narrow, behavior-preserving path through it, but it should not move context-specific slug, filename, term, post, or WooCommerce decisions yet.

## Why now

Epic 1 captured the current behavior before refactoring. Epic 2 should now isolate the pure transliteration step so later post, term, filename, and WooCommerce services can share one implementation instead of continuing to call broad `Main` behavior directly.

## Files to inspect

- `src/php/Main.php`
- `src/php/ConversionTables.php`
- `src/php/Settings/Settings.php`
- `tests/unit/MainTest.php`
- `tests/unit/CyrToLatTestCase.php`
- `composer.json`

## Primary implementation area

- `src/php/Transliteration/Transliterator.php`
- `src/php/Main.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

Create the `src/php/Transliteration` and `tests/unit/Transliteration` directories if they do not exist.

## Scope

- Add a `CyrToLat\Transliteration\Transliterator` class.
- Inject or pass the current settings/table dependency in the same style used by the existing codebase.
- Move only the top-level table-based string conversion contract into the service in this task.
- Keep `Main::transliterate()` as a compatibility wrapper.
- Make `Main::transliterate()` delegate to the new service.
- Preserve the existing `ctl_table` filter application point.
- Preserve current behavior for empty strings, ordinary Cyrillic strings, bad multibyte content, and mixed strings.
- Keep method visibility conservative and compatible with the existing unit-test approach.
- Add or move focused tests proving the service returns the same output as the current `Main::transliterate()` path.

## Out of scope

- Do not remove `Main::transliterate()`.
- Do not change `Main::sanitize_title()`.
- Do not move `sanitize_filename()` logic.
- Do not extract `LegacySanitizeTitleBridge`.
- Do not introduce post, term, filename, or WooCommerce-specific context handling.
- Do not change conversion tables.
- Do not change public filters except preserving their current behavior.

## Acceptance criteria

- `CyrToLat\Transliteration\Transliterator` exists and is autoloaded through the existing PSR-4 setup.
- `Main::transliterate()` still exists and delegates to the new service.
- Existing tests for `Main::transliterate()` still pass.
- New service-level tests cover the current direct transliteration behavior.
- `ctl_table` is still applied exactly once for a normal transliteration call.
- No visible slug, filename, WooCommerce, WPML, or Polylang behavior changes are introduced.
- No browser, Playwright, or Codeception infrastructure is added.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration/TransliteratorTest.php
vendor/bin/phpunit tests/unit/MainTest.php --filter transliterate
```

## Notes for implementer

- Keep this first extraction deliberately small. The goal is to introduce a stable service boundary, not to redesign every caller.
- If constructor wiring in `Main` becomes awkward, prefer a small private factory/helper over changing public initialization behavior.
- Keep any new namespace and directory names aligned with the 7.0 target architecture from the parent plan.
