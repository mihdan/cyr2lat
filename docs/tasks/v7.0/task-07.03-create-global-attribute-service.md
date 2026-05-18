# Task 07.03: Create `GlobalAttributeService`

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 7 - WooCommerce global attributes.

## Goal

Create a dedicated `GlobalAttributeService` for WooCommerce global attribute taxonomy slug handling while preserving existing behavior.

## Scope

- Add `CyrToLat\Slugs\GlobalAttributeService`.
- Move global attribute taxonomy detection and attribute slug normalization decisions out of `Main` where practical.
- Register explicit WooCommerce attribute create/update handling when WooCommerce provides a stable integration point.
- Keep existing `Main` callback methods available as compatibility wrappers if needed.

## Implementation summary

- Added `CyrToLat\Slugs\GlobalAttributeService`.
- Delegated global attribute taxonomy checks from `Main` to the service.
- Kept existing protected `Main` methods as compatibility wrappers around the new service.
- Added unit tests for registered global attribute detection and WooCommerce attribute preservation decisions.

## Acceptance criteria

- Global attribute creation from Cyrillic names still stores transliterated attribute slugs.
- Explicit Cyrillic global attribute slugs are normalized to transliterated slugs.
- Existing registered `pa_*` taxonomy names remain protected from accidental transliteration.
- Unit tests and coding standards pass.

## Implemented Files

- `src/php/Slugs/GlobalAttributeService.php`
- `src/php/Main.php`
- `tests/unit/Slugs/GlobalAttributeServiceTest.php`
- `docs/tasks/v7.0/task-07.03-create-global-attribute-service.md`

## Verification

```bash
vendor/bin/phpunit tests/unit/Slugs/GlobalAttributeServiceTest.php
composer phpcs
```
