# Task 07.07: Document existing `pa_*` attribute migration policy

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Decide and document the 7.0 policy for existing WooCommerce global attribute taxonomies under `pa_*`.

## Scope

- Document whether 7.0 automatically migrates existing global attribute taxonomy slugs.
- Explain the risks of changing registered `pa_*` taxonomy names after products already use them.
- Clarify how new and edited attributes are normalized after Epic 7.
- Leave any destructive migration tool proposal for a later, explicit dry-run workflow.

## Migration policy

- Cyr-To-Lat 7.0 does **not** automatically migrate existing WooCommerce global attribute taxonomy slugs under `pa_*`.
- Existing registered `pa_*` taxonomy names are treated as store data and must be preserved at runtime.
- Changing an existing WooCommerce global attribute slug can affect:
  - `woocommerce_attribute_taxonomies.attribute_name`;
  - registered taxonomy names such as `pa_color`;
  - `wp_term_taxonomy.taxonomy` rows;
  - `_product_attributes` product meta keys and `name` values;
  - variation attribute meta keys such as `attribute_pa_color`;
  - frontend layered navigation query parameters and saved links.
- Automatic migration without a preview could break product filters, variation matching, cached URLs, and third-party integrations that store attribute taxonomy names.

## 7.0 behavior

- New WooCommerce global attributes created from Cyrillic names are normalized through the existing WooCommerce `wc_sanitize_taxonomy_name()` / WordPress `sanitize_title` path.
- New WooCommerce global attributes created with explicit Cyrillic slugs are normalized to transliterated slugs.
- Existing registered `pa_*` taxonomy names are not transliterated during runtime lookup, rendering, term handling, or layered-navigation parsing.
- Existing global attribute terms under registered `pa_*` taxonomies continue to use term slug handling for newly created or explicitly updated terms.

## Future migration tool requirements

- Any migration of existing `pa_*` global attributes must be a separate explicit workflow after 7.0 services are stable.
- The workflow must include a dry-run/report mode before writes.
- The workflow must update all WooCommerce-owned references consistently and document rollback requirements.
- The workflow must not run automatically on plugin upgrade.

## Acceptance criteria

- The migration policy is documented in the task and release-facing documentation where appropriate.
- The policy matches the main 7.0 plan recommendation not to include automatic destructive WooCommerce attribute migration.
- Tests continue to prove that registered `pa_*` taxonomy names are preserved in runtime contexts.

## Implemented Files

- `docs/tasks/v7.0/task-07.07-document-pa-attribute-migration-policy.md`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

Documentation-only task; no build or test run required.
