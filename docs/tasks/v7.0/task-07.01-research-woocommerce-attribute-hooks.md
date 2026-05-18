# Task 07.01: Research WooCommerce attribute taxonomy hooks

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Identify the exact WooCommerce hook and storage paths used when global product attributes are created or updated, so Cyr-To-Lat can move global attribute slug handling out of the broad `sanitize_title` bridge.

## Scope

- Inspect WooCommerce attribute create/update functions and admin handlers used by the current supported WooCommerce version.
- Identify hooks/actions/filters available before the attribute slug is stored.
- Document whether the hooks receive the raw name, explicit slug, attribute ID, and update/create context.
- Do not change runtime behavior in this task.

## Research findings

- WooCommerce stores global attributes through `wc_create_attribute()` and `wc_update_attribute()` in `includes/wc-attribute-functions.php`.
- Both create and update paths derive the stored `attribute_name` before database writes with `wc_sanitize_taxonomy_name()`:
  - when `slug` is empty, the source is `name`;
  - when `slug` is provided, WooCommerce strips a leading `pa_` after sanitization.
- `wc_create_attribute()` fires `woocommerce_attribute_added` after the database insert with `($id, $data)`.
- `wc_create_attribute()` update mode fires `woocommerce_attribute_updated` after the database update with `($id, $data, $old_slug)`.
- These action hooks expose already-sanitized `attribute_name`; they are useful for observing completed writes, but they are too late to normalize the slug before storage without an additional update.
- The stable pre-storage integration point remains the WordPress `sanitize_title` filter called by `wc_sanitize_taxonomy_name()`.

## Implementation decision

- Epic 7 should keep normalization at the `sanitize_title` call used by `wc_sanitize_taxonomy_name()`, but route the WooCommerce global attribute decision through a dedicated service instead of keeping the logic inside `Main`.
- Post-write hooks are documented for future observability only; they are not used for automatic mutation in 7.0.

## Acceptance criteria

- The selected create/update integration point is documented with hook names and callback arguments.
- Any limitations of the hook payload are documented.
- Follow-up implementation tasks can rely on the documented hook contract.

## Implemented Files

- `docs/tasks/v7.0/task-07.01-research-woocommerce-attribute-hooks.md`

## Verification

Documentation-only task; no build or test run required.
