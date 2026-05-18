# Task 02.05: Preserve `ctl_table` and `ctl_locale` behavior

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Ensure the extracted transliteration core preserves the existing `ctl_table` and `ctl_locale` behavior exactly.

The service extraction must not bypass multilingual table selection, custom table filters, WPML locale handling, Polylang locale handling, or settings-based conversion table selection.

## Why this matters

The 7.0 architecture moves logic away from a broad `sanitize_title` filter, but existing users and integrations can customize transliteration through public filters. Breaking those filters would be a user-facing regression even if the default transliteration output stayed the same.

## Files to inspect

- `src/php/Main.php`
- `src/php/Settings/Settings.php`
- `src/php/Settings/Tables.php`
- `src/php/Transliteration/Transliterator.php`
- `tests/unit/MainTest.php`
- `tests/unit/Settings/SettingsTest.php`
- `tests/unit/Settings/TablesTest.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

## Primary implementation area

- `src/php/Transliteration/Transliterator.php`
- `src/php/Main.php`
- `tests/unit/Transliteration/TransliteratorTest.php`
- `tests/unit/MainTest.php`

## Scope

- Confirm the extracted transliteration path still obtains the active table through `Settings::get_table()`.
- Confirm `Settings::get_table()` still applies `ctl_locale` as before.
- Confirm the transliteration call still applies `ctl_table` with the same default table value as before.
- Add tests that fail if `ctl_table` is no longer called or is called with the wrong table.
- Add tests that fail if a custom `ctl_table` result is ignored by `Transliterator`.
- Preserve existing WPML and Polylang locale-related tests in `MainTest`.
- Document any intentionally unchanged legacy filter order in implementation notes.

## Out of scope

- Do not add new public filters in this task.
- Do not rename `ctl_table` or `ctl_locale`.
- Do not change `Settings::get_table()` selection rules.
- Do not move WPML or Polylang integration into a new service.
- Do not change `ctl_pre_sanitize_title` or `ctl_pre_sanitize_filename`; those belong to slug and filename handlers.

## Acceptance criteria

- `ctl_table` behavior is preserved for direct transliteration.
- `ctl_locale` behavior is preserved through settings/table selection.
- A custom table returned by `ctl_table` changes transliteration output in the same way it does before extraction.
- Existing multilingual tests still pass.
- No new filter is required for existing integrations to keep working.
- Production behavior remains unchanged for default and filtered transliteration paths.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration/TransliteratorTest.php --filter "table|locale"
vendor/bin/phpunit tests/unit/MainTest.php --filter "locale|transliterate|wpml|pll"
vendor/bin/phpunit tests/unit/Settings/SettingsTest.php --filter table
vendor/bin/phpunit tests/unit/Settings/TablesTest.php --filter locale
```

## Notes for implementer

- This task is a guardrail around the extraction. If Task 02.01 already preserved the filters, this task should strengthen tests and documentation rather than churn the implementation.
- Keep filter expectations explicit, because later services will use the same transliteration core from more entry points.
