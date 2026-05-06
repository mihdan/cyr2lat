# Task 07.07: Document existing `pa_*` attribute migration policy

## Status

Draft for review.

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

## Acceptance criteria

- The migration policy is documented in the task and release-facing documentation where appropriate.
- The policy matches the main 7.0 plan recommendation not to include automatic destructive WooCommerce attribute migration.
- Tests continue to prove that registered `pa_*` taxonomy names are preserved in runtime contexts.

## Verification

```bash
composer phpcs
```
