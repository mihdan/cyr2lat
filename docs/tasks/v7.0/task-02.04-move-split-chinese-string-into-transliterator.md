# Task 02.04: Move `split_chinese_string()` into transliteration core

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Move Chinese string splitting from `Main` into the transliteration core while preserving the current output for Chinese locale transliteration.

The current helper adds hyphen boundaries around mapped Chinese characters before table conversion. That behavior is part of the core transliteration pipeline and should be shared by later slug services through `Transliterator`.

## Files to inspect

- `src/php/Main.php`
- `src/php/Settings/Settings.php`
- `src/php/Transliteration/Transliterator.php`
- `tests/unit/MainTest.php`
- `tests/unit/CyrToLatTestCase.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

## Primary implementation area

- `src/php/Transliteration/Transliterator.php`
- `src/php/Main.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

## Scope

- Move the behavior of `Main::split_chinese_string()` into the transliteration core.
- Preserve the `Settings::is_chinese_locale()` condition.
- Preserve the current minimum string-length behavior.
- Preserve the current handling of mixed Chinese and Latin text.
- Ensure Chinese splitting runs after macOS string fixing and before final table conversion, matching the legacy order.
- Keep `Main::transliterate()` behavior unchanged through delegation.
- Move the existing unit coverage for Chinese splitting into the transliteration core test layer or add equivalent coverage there.

## Out of scope

- Do not change Chinese conversion tables.
- Do not change locale detection.
- Do not change `ctl_locale` or `ctl_table` behavior.
- Do not change slug cleanup, hyphen trimming, or WordPress `sanitize_title` behavior.
- Do not add browser or acceptance tests.

## Acceptance criteria

- The transliteration core owns the Chinese string splitting behavior.
- Current `MainTest` Chinese splitting/transliteration behavior remains covered either directly or through the new service tests.
- Tests cover:
  - Chinese locale with a long Chinese string.
  - Chinese locale with a string shorter than the current threshold.
  - Chinese locale with mixed Chinese and Latin text.
  - Non-Chinese locale bypass behavior where practical.
- `Main::transliterate()` still returns the same output as before for Chinese and non-Chinese examples.
- No visible behavior changes are introduced outside the extraction.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration/TransliteratorTest.php --filter chinese
vendor/bin/phpunit tests/unit/MainTest.php --filter "split_chinese_string|transliterate"
```

## Notes for implementer

- Keep the legacy order of operations intact: macOS fix, Chinese splitting, then `strtr()` table conversion.
- If the old protected method remains only for compatibility, make it delegate to the service and mark it as transitional in a short comment.
