# Task 10.05: Decide WooCommerce attributes converter section

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Decide whether WooCommerce attribute migration belongs in the existing converter UI or must remain outside the 7.0 background slug converter.

## Scope

- Review risks of automatic WooCommerce global/local/variation attribute migration.
- Document whether a separate converter page section is needed for 7.0.
- Keep existing post/term conversion UX unchanged unless the decision requires copy-only clarification.
- Align the decision with the already documented `pa_*` migration policy.

## Acceptance criteria

- A clear 7.0 decision is documented.
- The decision does not introduce automatic WooCommerce attribute migration.
- Existing post/term converter behavior remains unchanged.
- Future migration work is left to an explicit dry-run workflow if needed.

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-10.05-decide-woocommerce-attributes-converter-section.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`
