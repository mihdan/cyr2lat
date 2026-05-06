# Task 07.05: Add global attribute term tests

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Add integration coverage for terms created under WooCommerce global attribute taxonomies.

## Scope

- Create a global attribute taxonomy with a transliterated slug.
- Register the taxonomy in the test request when needed.
- Verify Cyrillic attribute term names receive transliterated term slugs.
- Verify explicit Cyrillic term slugs are normalized.
- Verify explicit Latin term slugs are preserved.

## Implementation summary

- Added integration coverage for terms under a real WooCommerce global attribute taxonomy.
- Covered Cyrillic term name transliteration under `pa_*` taxonomy.
- Covered explicit Cyrillic term slug normalization under `pa_*` taxonomy.
- Covered explicit Latin term slug preservation under `pa_*` taxonomy.

## Acceptance criteria

- Tests run against real WordPress term APIs and WooCommerce attribute taxonomies.
- Attribute term behavior is covered without converting existing `pa_*` taxonomies.
- The existing non-attribute term slug coverage remains green.

## Implemented Files

- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/task-07.05-global-attribute-term-tests.md`

## Verification

```bash
vendor/bin/phpunit tests/integration/WooCommerceGlobalAttributeIntegrationTest.php
vendor/bin/phpunit tests/integration/TermSlugIntegrationTest.php
composer phpcs
```
