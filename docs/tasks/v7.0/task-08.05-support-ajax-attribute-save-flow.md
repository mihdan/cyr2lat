# Task 08.05: Support AJAX attribute save flow

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Ensure WooCommerce admin AJAX attribute save requests normalize local attribute keys explicitly without corrupting global attribute identifiers.

## Scope

- Inspect WooCommerce admin AJAX handlers for product attribute save/update actions.
- Normalize local attribute keys from AJAX request payloads before persistence.
- Preserve global `pa_*` and already-normalized identifiers.
- Add integration coverage for the backend handler path where practical.

## Covered path

The practical backend path is WooCommerce's `woocommerce_save_attributes` request shape. `LocalAttributeService` parses the serialized `data` payload to preserve local attribute detection during AJAX sanitization, while `woocommerce_before_product_object_save` normalizes persisted local attribute keys before `_product_attributes` is written.

## Acceptance criteria

- AJAX-saved local attributes are stored under transliterated keys.
- AJAX-saved global attributes keep their registered taxonomy keys.
- The task documents the exact WooCommerce AJAX path covered.
- Tests cover the practical backend save path or document why direct AJAX execution is not practical.

## Implemented Files

- `src/php/Slugs/LocalAttributeService.php`
- `src/php/Main.php`
- `tests/unit/Slugs/LocalAttributeServiceTest.php`
- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

- `vendor\bin\phpunit tests\unit\Slugs\LocalAttributeServiceTest.php`
- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
- `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php src\php\Slugs\LocalAttributeService.php tests\unit\Slugs\LocalAttributeServiceTest.php tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
