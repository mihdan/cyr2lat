# Task 12.03: Document WooCommerce attribute limitations

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Document the WooCommerce attribute support boundaries and known limitations for the 7.0 release.

## Scope

- Describe supported global attribute, local attribute, variation attribute, frontend cart, REST/API, and admin save flows.
- Document that existing `pa_*` taxonomies and existing local/variation attribute keys are not automatically migrated.
- Point future migration work to a separate dry-run-first workflow.
- Avoid implying that destructive attribute rewrites run during plugin upgrade.

## Acceptance criteria

- Release-facing documentation clearly states WooCommerce attribute limitations.
- Existing attribute migration policy is consistent with Epic 7 and Epic 10 decisions.
- The parent plan marks the task complete after implementation.
- No WooCommerce runtime behavior is changed in this task.

## Verification

- `vendor\bin\phpcs --standard=phpcs.xml readme.txt docs\tasks\v7.0\task-12.03-document-woocommerce-attribute-limitations.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Added a WooCommerce FAQ entry to `readme.txt`.
- Documented supported new/update flows for product slugs, product taxonomies, global attributes, local attributes, variation attributes, frontend cart handling, REST/API saves, and admin saves.
- Documented that existing global `pa_*`, local product attribute keys, and variation attribute keys are not automatically migrated during plugin upgrade.
