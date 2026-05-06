# Task 12.01: Update readme feature list

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Update the WordPress.org-facing feature list so it reflects the 7.0 architecture and covered slug flows.

## Scope

- Refresh the `readme.txt` feature list for posts, pages, terms, filenames, background conversion, WP-CLI, REST/Gutenberg, and WooCommerce attribute flows.
- Keep the list release-facing and concise.
- Do not document unsupported automatic WooCommerce attribute migrations as features.
- Do not change runtime code in this task.

## Acceptance criteria

- `readme.txt` describes the important 7.0 feature coverage at a high level.
- WooCommerce support is described without promising destructive or automatic migration of existing attributes.
- The parent plan marks the task complete after implementation.
- Documentation style remains consistent with the existing readme.

## Verification

- `vendor\bin\phpcs --standard=phpcs.xml readme.txt docs\tasks\v7.0\task-12.01-update-readme-feature-list.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Updated the `readme.txt` feature list to mention explicit WordPress save, REST/Gutenberg, background, WP-CLI, filename, and WooCommerce slug flows.
- Kept WooCommerce wording conservative: 7.0 supports current explicit flows but does not automatically migrate existing attributes.
