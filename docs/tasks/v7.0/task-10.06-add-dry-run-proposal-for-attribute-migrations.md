# Task 10.06: Add dry-run proposal for future attribute migrations

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Define the safe shape of a future WooCommerce attribute migration tool without adding automatic migration behavior to 7.0.

## Scope

- Document a dry-run-first workflow for future global/local/variation attribute migrations.
- List the affected WooCommerce data areas that a future migration must inspect.
- Require explicit user action and backup warnings before any write mode.
- Keep 7.0 implementation limited to current explicit handlers and non-destructive documentation.

## Acceptance criteria

- The plan includes a clear dry-run proposal for future WooCommerce attribute migrations.
- The proposal identifies products, variations, terms, taxonomies, and lookup data as migration targets.
- 7.0 continues to avoid automatic destructive WooCommerce attribute migration.
- The task document records that implementation is proposal/documentation-only for this release.

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-10.06-add-dry-run-proposal-for-attribute-migrations.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`
