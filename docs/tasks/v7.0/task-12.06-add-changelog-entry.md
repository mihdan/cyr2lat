# Task 12.06: Add changelog entry

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Add a changelog entry that summarizes the 7.0 release changes.

## Scope

- Add a release changelog entry in `readme.txt` and/or `changelog.txt` following existing project style.
- Mention architecture changes, explicit slug handling, WooCommerce attribute fixes, legacy bridge policy, and test coverage.
- Keep the entry concise and release-facing.
- Do not change runtime code in this task.

## Acceptance criteria

- The changelog includes a 7.0 entry or updates the current pending release entry with 7.0 details.
- The entry does not promise unsupported automatic WooCommerce attribute migrations.
- The parent plan marks the task complete after implementation.
- Documentation style remains consistent with the existing changelog.

## Verification

- `vendor\bin\phpcs --standard=phpcs.xml readme.txt docs\tasks\v7.0\task-12.06-add-changelog-entry.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Added the pending `7.0.0` changelog entry to `readme.txt`.
- Summarized explicit slug services, REST/Gutenberg coverage, WooCommerce coverage, legacy bridge filter, migration policy, and backend-first testing strategy.
- Kept the entry release-facing and avoided promising automatic WooCommerce attribute migration.
