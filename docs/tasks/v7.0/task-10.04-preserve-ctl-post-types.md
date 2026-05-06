# Task 10.04: Preserve `ctl_post_types`

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Ensure background conversion and WP-CLI regeneration continue to honor the public `ctl_post_types` compatibility filter.

## Scope

- Review how converter post type selection is built.
- Keep the `ctl_post_types` filter in the conversion path.
- Preserve intersection with configured background post types.
- Add or update targeted tests documenting the filter behavior.

## Acceptance criteria

- `ctl_post_types` still controls the list of convertible post types.
- Settings-defined `background_post_types` continue to limit queued conversion work.
- Attachments keep their existing opt-in behavior.
- WP-CLI regeneration and admin-triggered conversion share the same post type filtering behavior.

## Verification

- Passed with the implementation commit:
  - `vendor\bin\phpunit tests\unit\ConverterTest.php tests\unit\Settings\ConverterTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\Converter.php tests\unit\ConverterTest.php docs\tasks\v7.0\task-10.04-preserve-ctl-post-types.md`

## Implementation notes

- Added explicit unit coverage for the converter path that starts from `wp cyr2lat regenerate` or admin conversion.
- The test verifies that `ctl_post_types` receives the settings/convertible post type intersection.
- The filtered post type list is then passed into SQL query preparation.
- No runtime behavior was changed.
