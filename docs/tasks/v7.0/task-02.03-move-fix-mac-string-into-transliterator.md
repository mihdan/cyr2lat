# Task 02.03: Move `fix_mac_string()` into transliteration core

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Move the macOS composed/decomposed character normalization helper from `Main` into the transliteration core without changing transliteration output.

The helper currently prepares strings before table conversion so macOS-style Unicode sequences transliterate consistently. That behavior belongs beside the table conversion logic and should be available to every future slug service through `Transliterator`.

## Files to inspect

- `src/php/Main.php`
- `src/php/ConversionTables.php`
- `src/php/Transliteration/Transliterator.php`
- `tests/unit/MainTest.php`
- `tests/unit/ConversionTablesTest.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

## Primary implementation area

- `src/php/Transliteration/Transliterator.php`
- `src/php/Main.php`
- `tests/unit/Transliteration/TransliteratorTest.php`

## Scope

- Move the behavior of `Main::fix_mac_string()` into the transliteration core.
- Keep the conversion based on `ConversionTables::get_fix_table_for_mac()`.
- Preserve the existing logic that only maps fix-table entries present in the active conversion table.
- Ensure `Transliterator` applies macOS normalization before `strtr()` table conversion.
- Keep or replace the old `Main::fix_mac_string()` only as needed for compatibility with existing tests.
- Move or duplicate characterization coverage so future changes to the core catch macOS normalization regressions.
- Keep `Main::transliterate()` behavior unchanged through delegation.

## Out of scope

- Do not change the macOS fix table.
- Do not alter conversion table contents.
- Do not change filename-specific lowercasing behavior.
- Do not change `sanitize_title()` branching.
- Do not change Chinese splitting behavior; that is a separate task.

## Acceptance criteria

- The transliteration core performs macOS string fixing before table conversion.
- Existing direct transliteration expectations still pass.
- Tests cover at least one macOS decomposed character sequence that currently relies on the fix table.
- `Main` no longer owns the primary implementation of the macOS fix behavior, or it delegates to the core if a compatibility wrapper remains.
- No visible behavior changes are introduced for slugs, filenames, terms, posts, or WooCommerce attributes.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration/TransliteratorTest.php --filter mac
vendor/bin/phpunit tests/unit/MainTest.php --filter transliterate
```

## Notes for implementer

- Treat this as a move, not a rewrite.
- Use the existing comments in `ConversionTables::get_fix_table_for_mac()` to choose realistic test input.
- Be careful with fixture strings: decomposed Unicode sequences can look identical in an editor while having different bytes.
