# Task 07.02: Research WooCommerce REST/API product attribute paths

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Verify how WooCommerce REST and programmatic API paths create or update global product attributes and whether they use the same attribute slug storage hooks as the admin flow.

## Scope

- Inspect WooCommerce REST product attribute controller behavior.
- Inspect direct `wc_create_attribute()` and `wc_update_attribute()` behavior.
- Confirm whether explicit Cyrillic slugs and name-derived slugs pass through the same normalization point.
- Do not add REST tests or runtime changes in this task.

## Research findings

- WooCommerce REST API v1 creates and updates product attributes through `WC_REST_Product_Attributes_V1_Controller`.
- REST API v2 extends the v1 controller without changing product attribute create/update behavior.
- REST API v3 overrides `create_item()` only to support `generate_slug`, then still calls `wc_create_attribute()`.
- REST create/update requests sanitize submitted `slug` values with `wc_sanitize_taxonomy_name()` before passing them to `wc_create_attribute()` or `wc_update_attribute()`.
- REST v3 `generate_unique_slug()` also uses `wc_sanitize_taxonomy_name()` on the submitted attribute name.
- Direct API usage of `wc_create_attribute()` / `wc_update_attribute()` and REST usage therefore share the same `wc_sanitize_taxonomy_name()` / `sanitize_title` normalization point.

## Implementation decision

- A dedicated Cyr-To-Lat global attribute service attached to the existing `sanitize_title` bridge covers admin, direct API, and REST API paths.
- REST-specific mutation hooks such as `woocommerce_rest_insert_product_attribute` fire after WooCommerce has already created or updated the attribute and are not selected for pre-storage normalization.

## Acceptance criteria

- REST/API paths are documented with the WooCommerce functions they call.
- The chosen implementation point is confirmed to cover admin, REST, and direct API usage where practical.
- Any unsupported or duplicate paths are documented for later tests.

## Implemented Files

- `docs/tasks/v7.0/task-07.02-research-woocommerce-rest-api-attributes.md`

## Verification

Documentation-only task; no build or test run required.
