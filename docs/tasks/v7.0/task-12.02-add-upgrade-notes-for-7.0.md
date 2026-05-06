# Task 12.02: Add upgrade notes for 7.0

## Status

To do.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Add release-facing upgrade notes that explain the important 7.0 behavioral expectations and safe upgrade boundaries.

## Scope

- Document that 7.0 is an architecture-focused release with explicit slug services.
- Document the safe-upgrade expectation for existing posts, terms, filenames, and WooCommerce data.
- Explain that existing WooCommerce attributes are not automatically migrated.
- Mention when to use background conversion and WP-CLI regeneration.
- Do not add a runtime migration tool in this task.

## Acceptance criteria

- Upgrade notes are available in release-facing documentation.
- The notes make clear that automatic WooCommerce attribute migration is out of scope for 7.0.
- The parent plan marks the task complete after implementation.
- No runtime behavior changes are introduced.

## Verification

- To be completed with the implementation commit.

## Implementation notes

- To be completed with the implementation commit.
