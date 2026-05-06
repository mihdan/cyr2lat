# Task 08.06: Support full product save flow

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Ensure full WooCommerce product save requests normalize local and variation attribute keys through explicit services.

## Scope

- Cover classic product edit save payloads for local attributes.
- Cover variable product save payloads with variation attributes.
- Preserve manual Latin attribute keys and registered global `pa_*` keys.
- Avoid relying on generic `sanitize_title` calls outside the intended WooCommerce context.

## Acceptance criteria

- Full product saves store normalized local product attribute keys.
- Full product saves store normalized local variation attribute keys.
- Global attributes keep their registered taxonomy names.
- Integration tests cover the full product save path.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/WooCommerceLocalAttributeIntegrationTest.php tests/integration/WooCommerceVariationAddToCartIntegrationTest.php
composer phpcs
```
