# Task 07.02: Research WooCommerce REST/API product attribute paths

## Status

Draft for review.

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

## Acceptance criteria

- REST/API paths are documented with the WooCommerce functions they call.
- The chosen implementation point is confirmed to cover admin, REST, and direct API usage where practical.
- Any unsupported or duplicate paths are documented for later tests.

## Verification

```bash
composer phpcs
```
