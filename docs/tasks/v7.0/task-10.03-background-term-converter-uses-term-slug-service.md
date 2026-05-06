# Task 10.03: Ensure background term converter uses `TermSlugService`

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Route existing term slug conversion through the explicit term slug service boundary instead of duplicating direct transliteration logic in the background process.

## Scope

- Update `TermConversionProcess` to use `TermSlugService` for slug normalization.
- Preserve existing WPML/Polylang locale scoping.
- Preserve the current direct database update behavior for queued existing terms.
- Add unit coverage proving the background converter goes through the service boundary.

## Acceptance criteria

- Background term slug conversion uses `TermSlugService` for the term slug value.
- Converted term slugs remain stored encoded in the database as before.
- `pa_*` WooCommerce attribute taxonomies remain excluded from generic term conversion.
- Existing term conversion tests continue to pass with the broad legacy bridge disabled.

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\BackgroundProcesses\TermConversionProcessTest.php tests\unit\Slugs\TermSlugServiceTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\BackgroundProcesses\TermConversionProcess.php tests\unit\BackgroundProcesses\TermConversionProcessTest.php docs\tasks\v7.0\task-10.03-background-term-converter-uses-term-slug-service.md`
