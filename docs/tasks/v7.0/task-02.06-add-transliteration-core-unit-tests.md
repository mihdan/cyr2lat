# Task 02.06: Add unit tests for transliteration core

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Complete focused unit coverage for the extracted transliteration core so later Epic 3-8 services can depend on it safely.

This task should consolidate the behavior protected during Tasks 02.01-02.05 and make sure the new core is tested directly, not only through `Main`.

## Files to inspect

- `src/php/Transliteration/Transliterator.php`
- `src/php/Transliteration/SlugContext.php`
- `src/php/Main.php`
- `src/php/ConversionTables.php`
- `tests/unit/MainTest.php`
- `tests/unit/ConversionTablesTest.php`
- `tests/unit/CyrToLatTestCase.php`
- `tests/unit/Transliteration/TransliteratorTest.php`
- `tests/unit/Transliteration/SlugContextTest.php`

## Primary implementation area

- `tests/unit/Transliteration/TransliteratorTest.php`
- `tests/unit/Transliteration/SlugContextTest.php`
- `tests/unit/MainTest.php`

## Scope

- Add or complete direct tests for `Transliterator`.
- Add or complete direct tests for `SlugContext` if Task 02.02 introduced a value object.
- Keep legacy wrapper tests in `MainTest` where they prove compatibility.
- Cover default ISO9-style transliteration behavior.
- Cover empty string behavior.
- Cover bad multibyte content behavior.
- Cover macOS normalization behavior.
- Cover Chinese locale splitting behavior.
- Cover custom `ctl_table` behavior.
- Cover `ctl_locale`-driven table selection through the existing settings path.
- Cover at least one context example if the core accepts `SlugContext`.
- Avoid relying on real WordPress, WooCommerce, WPML, or Polylang installations.

## Out of scope

- Do not add WordPress integration tests in this task.
- Do not add WooCommerce integration tests in this task.
- Do not test post, term, filename, REST, or cart/session behavior except through existing wrapper compatibility tests.
- Do not change production behavior to make tests easier.
- Do not add Codeception or Playwright.

## Acceptance criteria

- The transliteration core has direct unit tests for all behavior moved in Epic 2.
- `Main::transliterate()` remains covered as a compatibility wrapper.
- The tests would fail if:
  - `ctl_table` stopped being applied.
  - macOS normalization stopped running.
  - Chinese splitting stopped running for Chinese locales.
  - default Cyrillic transliteration output changed unexpectedly.
  - the context structure lost required type/source support.
- Existing unit tests still pass.
- Coding standards still pass.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration
vendor/bin/phpunit tests/unit/MainTest.php --filter transliterate
```

## Notes for implementer

- Prefer service-level tests for new behavior ownership and keep `MainTest` focused on wrapper compatibility.
- Avoid over-mocking the core. It should be possible to test most of it with a mocked settings object and ordinary strings.
- If Tasks 02.01-02.05 already added some of this coverage, use this task to fill gaps and remove duplicate assertions that no longer add signal.
