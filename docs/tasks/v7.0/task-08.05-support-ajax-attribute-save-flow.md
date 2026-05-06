# Task 08.05: Support AJAX attribute save flow

## Status

Draft for review.

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

## Acceptance criteria

- AJAX-saved local attributes are stored under transliterated keys.
- AJAX-saved global attributes keep their registered taxonomy keys.
- The task documents the exact WooCommerce AJAX path covered.
- Tests cover the practical backend save path or document why direct AJAX execution is not practical.

## Verification

```bash
vendor/bin/phpunit -c phpunit.integration.xml tests/integration/WooCommerceLocalAttributeIntegrationTest.php
composer phpcs
```
