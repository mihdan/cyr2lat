# Task 07.01: Research WooCommerce attribute taxonomy hooks

## Status

Draft for review.

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

## Acceptance criteria

- The selected create/update integration point is documented with hook names and callback arguments.
- Any limitations of the hook payload are documented.
- Follow-up implementation tasks can rely on the documented hook contract.

## Verification

```bash
composer phpcs
```
