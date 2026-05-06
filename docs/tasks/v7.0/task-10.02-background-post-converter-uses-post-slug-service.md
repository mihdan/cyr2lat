# Task 10.02: Ensure background post converter uses `PostSlugService`

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Route existing post slug conversion through the explicit post slug service boundary instead of duplicating direct transliteration logic in the background process.

## Scope

- Update `PostConversionProcess` to use `PostSlugService` for slug normalization.
- Preserve old slug meta writes and attachment filename handling.
- Preserve locale scoping for WPML/Polylang post language resolution.
- Add unit coverage proving the background converter goes through the service boundary.

## Acceptance criteria

- Background post slug conversion uses `PostSlugService` for the post slug value.
- Converted slugs remain stored encoded in the database as before.
- Attachment rename/metadata behavior is unchanged.
- Existing post conversion tests continue to pass with the broad legacy bridge disabled.

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\BackgroundProcesses\PostConversionProcessTest.php tests\unit\Slugs\PostSlugServiceTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\BackgroundProcesses\PostConversionProcess.php tests\unit\BackgroundProcesses\PostConversionProcessTest.php docs\tasks\v7.0\task-10.02-background-post-converter-uses-post-slug-service.md`
