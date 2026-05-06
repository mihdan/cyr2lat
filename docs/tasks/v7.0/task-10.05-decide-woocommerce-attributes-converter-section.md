# Task 10.05: Decide WooCommerce attributes converter section

## Status

Done.

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

- Passed with the implementation commit:
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-10.05-decide-woocommerce-attributes-converter-section.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Decision

Do not add a WooCommerce attributes converter page section to the 7.0 background converter.

## Rationale

- The existing converter is scoped to current post and term slug conversion.
- WooCommerce global attribute taxonomies, local product attributes, variation meta keys, lookup tables, and layered navigation state have different data ownership and rollback risks.
- Adding an attributes section without a full dry-run workflow would look like a safe extension of the current converter, but it would be a separate migration tool in practice.
- 7.0 should keep the converter stable and leave attribute migration to a future explicit workflow.

## Implementation notes

- The 7.0 plan now records that no WooCommerce attributes converter page section is added in this release.
- No runtime converter behavior was changed.
