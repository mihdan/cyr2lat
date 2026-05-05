# Task 01: Capture current `Main::sanitize_title()` behavior

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 - Behavior capture before refactor.

## Goal

Add or complete regression tests that document the current behavior of `CyrToLat\Main::sanitize_title()` before any production refactoring starts.

This task must not change visible plugin behavior. Its purpose is to create a safety net for later extraction of `Transliterator`, `SlugContext`, and `LegacySanitizeTitleBridge`.

This is not meant to make the whole 7.0 test strategy unit-only. The first safety net should include fast characterization tests for the legacy method and must also define the integration coverage that proves the same behavior through real WordPress entry points.

## Why this is the first

Version 7.0 is an architecture migration. The broad `sanitize_title` hook currently protects many unrelated flows, including posts, terms, WooCommerce attributes, old slug redirects, and query contexts. Before moving logic into explicit services, the project needs tests that describe what the legacy entry point does today.

## Files to inspect

- `src/php/Main.php`
- `tests/unit/MainTest.php`
- `tests/unit/CyrToLatTestCase.php`
- `tests/unit/bootstrap.php`
- `composer.json`

## Primary implementation area

- `tests/unit/MainTest.php`

Create a separate test file only if `MainTest.php` becomes harder to navigate. If a new file is created, keep it under `tests/unit` and use the existing WP_Mock/Mockery style.

## Test layers

### Layer 1 - fast characterization tests

Use the existing unit test infrastructure to document the direct behavior of `Main::sanitize_title()`.

These tests are intentionally close to the method because the first refactor risk is moving logic out of `Main` while preserving return values, filter calls, guards, and current branching.

### Layer 2 - WordPress integration coverage

Use the standard WordPress PHPUnit integration test suite for integration coverage. Do not introduce Codeception for the 7.0 integration layer.

Rationale:

- The project already uses PHPUnit.
- The 7.0 plan needs backend integration tests, not acceptance tests.
- WordPress PHPUnit integration tests are enough for posts, terms, REST/Gutenberg paths, filenames, and plugin hook behavior.
- Avoid adding a second runner and another test abstraction unless a later task proves a concrete need.

Add integration coverage for behavior that only makes sense through WordPress hooks or data flows. If the repository does not yet have WordPress integration-test infrastructure, this task should record the required integration scenarios and either add the minimal bootstrap in a separate task or create the next task for it.

Integration scenarios required by the 7.0 plan:

- Post slug generation through `wp_insert_post_data`.
- REST post creation/update paths used by Gutenberg/block editor.
- Term creation through `pre_insert_term` and the subsequent slug sanitization flow.
- Filename transliteration through `sanitize_file_name`.
- WooCommerce global/local attribute flows where a WooCommerce test environment is available.

Do not replace these with only direct method tests. Direct method tests are the first fast safety net; integration tests are the proof that WordPress and WooCommerce still call the plugin correctly.

Recommended test layout:

- `tests/unit` - existing PHPUnit + WP_Mock tests.
- `tests/integration` - WordPress PHPUnit integration tests.
- `phpunit.xml` - existing unit suite unless the project decides to split it.
- `phpunit.integration.xml` - dedicated WordPress integration suite configuration.

Integration environment rules:

- Use a project-specific test database for this repository; do not reuse a shared `wp-tests` database from another project.
- Do not hardcode or commit the test database name, database user, database password, or local DB host.
- Read DB settings from local environment variables or an ignored local env file.
- If a sample configuration is added later, it must contain placeholders only.

## Scope

Audit the existing `sanitize_title()` tests and add missing cases for the current behavior.

Required behavior groups:

- Empty input returns unchanged.
- `query` context returns unchanged.
- `pre_term_slug` behavior remains unchanged in non-multilingual contexts.
- `ctl_pre_sanitize_title` can short-circuit the result.
- Normal Cyrillic title transliteration still produces the current expected slug.
- URL-encoded Cyrillic input is decoded before filter and conversion logic.
- Term context behavior keeps existing encoded term slugs when the database lookup says the slug already exists.
- Term context behavior transliterates when the value is not an existing encoded term slug.
- WooCommerce global attribute taxonomy names are preserved when current guard logic says they are attributes.
- WooCommerce local attribute/request-key cases are preserved where they currently bypass transliteration.
- The method keeps using the active table/locale behavior through existing settings/filter expectations.

## Suggested test scenarios

Use named data provider cases where practical.

- `''` stays `''`.
- `'some title'` with context `'query'` stays `'some title'`.
- `'й'` becomes the current expected ISO9-style result for the default mocked locale/table.
- `'%D0%B9'` is treated consistently with decoded Cyrillic input.
- `ctl_pre_sanitize_title` returning `'filtered title'` returns `'filtered title'`.
- `ctl_pre_sanitize_title` returning `false` allows normal processing.
- Existing encoded term slug path preserves the title when `$this->is_term` and `$this->taxonomies` indicate term processing.
- Non-existing term slug path transliterates using current logic.
- WooCommerce attribute taxonomy such as `pa_color` or a mocked registered attribute name is not transliterated when the current WooCommerce guard applies.
- Local product attribute request values covered by the current `is_local_attribute()` logic remain unchanged in the guarded contexts.

## Out of scope

- Do not change `src/php/Main.php`.
- Do not extract `Transliterator`, `SlugContext`, or bridge classes.
- Do not change hook registration.
- Do not change WooCommerce behavior.
- Do not add Playwright or browser tests.
- Do not add Codeception or acceptance-test infrastructure in this task.
- Do not build a full integration-test framework in this task if it is not already available; create or reference a follow-up task for that work.

## Acceptance criteria

- Existing tests still pass.
- New/updated tests fail if `Main::sanitize_title()` stops preserving any documented legacy behavior.
- The test names clearly describe the behavior being protected.
- The tests avoid relying on real WordPress, WooCommerce, WPML, or Polylang installs; use the repository's existing mocks and stubs.
- Integration-test scenarios required for this behavior are either implemented where infrastructure already exists or explicitly captured as follow-up tasks with the needed bootstrap/environment notes.
- Any integration bootstrap notes keep local DB credentials and the local test database name out of tracked files.
- Production source files are unchanged unless a test-only compatibility adjustment is absolutely required and justified in the PR/task notes.

## Verification

Run:

```bash
composer unit
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/MainTest.php --filter sanitize_title
```

## Notes for implementer

- Start by marking which behavior groups are already covered in `tests/unit/MainTest.php`.
- Prefer extending existing data providers when the setup is the same.
- Prefer separate explicit tests when setup involves globals, request state, database mocks, or WooCommerce guards.
- Clean up globals such as `$wpdb`, `$wp_query`, `$_POST`, and `$_GET` after each test path, following existing patterns in `MainTest`.
- Keep the task behavior-capture only. Any surprising current behavior should be documented by a test first, not corrected here.
