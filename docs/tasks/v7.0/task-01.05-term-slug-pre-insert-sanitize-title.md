# Task 01.05: Capture term slug generation through pre_insert_term and sanitize_title

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add integration coverage for the current term slug behavior that runs through WordPress' real `wp_insert_term()` flow, including the `pre_insert_term` marker and subsequent `sanitize_title` processing.

This task captures existing behavior only. It does not introduce `TermSlugService` and does not change the current broad `sanitize_title` bridge.

## Scope

- Verify that term-related filters are registered.
- Verify category term creation with a Cyrillic name.
- Verify post tag creation with a Cyrillic name.
- Verify REST-independent custom taxonomy term creation with a Cyrillic name.
- Verify an explicit Cyrillic slug passed to `wp_insert_term()` is transliterated.
- Verify an explicit Latin/manual slug is preserved.
- Verify the legacy behavior when a URL-encoded Cyrillic slug already exists in the same taxonomy.

Term update and REST term endpoints are left for later term-service tasks.

## Implemented Files

- `tests/integration/TermSlugIntegrationTest.php`

## Acceptance Criteria

- Tests use `wp_insert_term()` instead of calling `Main::sanitize_title()` directly.
- Tests run through the standard WordPress PHPUnit integration suite.
- Unit tests and coding standards still pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
