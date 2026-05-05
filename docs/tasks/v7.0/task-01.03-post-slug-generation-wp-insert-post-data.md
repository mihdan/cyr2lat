# Task 01.03: Capture post slug generation through wp_insert_post_data

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 1 – Behavior capture before refactor.

## Goal

Add integration coverage for the current post slug generation behavior that runs through WordPress' real `wp_insert_post_data` filter stack.

This task captures existing behavior only. It does not change the filter signature to 4 accepted args and does not move the logic into a dedicated service yet.

## Scope

- Verify that the plugin registers `wp_insert_post_data` for `Main::sanitize_post_name()`.
- Verify that an empty `post_name` is generated from a Cyrillic `post_title` on the post edit screen.
- Verify that an existing manual `post_name` is preserved.
- Verify that `auto-draft` and `revision` statuses do not receive generated slugs.

## Implemented Files

- `tests/integration/PostSlugIntegrationTest.php`

## Acceptance Criteria

- Integration tests exercise `apply_filters( 'wp_insert_post_data', ... )` instead of calling `Main::sanitize_post_name()` directly.
- Existing unit tests remain unchanged and continue to pass.
- Coding standards continue to pass.

## Verification

```bash
composer integration
composer unit
composer phpcs
```
