# Task 08.01: Create `LocalAttributeService`

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 8 - WooCommerce local and variation attributes.

## Goal

Create a dedicated service boundary for WooCommerce local product attribute detection and key normalization so this logic can move out of `Main` and away from broad `sanitize_title` assumptions.

## Scope

- Add `CyrToLat\Slugs\LocalAttributeService`.
- Move local product attribute detection helpers from `Main` into the service where practical.
- Preserve current local attribute behavior while introducing explicit service tests.
- Do not change variation-specific behavior in this task unless required for the service boundary.

## Acceptance criteria

- `LocalAttributeService` exists and is autoloaded.
- `Main` delegates local attribute decisions to the service.
- Existing local attribute behavior is preserved.
- Unit tests cover the service boundary.

## Implemented Files

- `src/php/Slugs/LocalAttributeService.php`
- `src/php/Main.php`
- `tests/unit/Slugs/LocalAttributeServiceTest.php`
- `tests/unit/Slugs/TestLocalAttributeService.php`

## Verification

- `vendor\bin\phpunit tests\unit\Slugs\LocalAttributeServiceTest.php`
- `vendor\bin\phpunit tests\unit\Slugs\GlobalAttributeServiceTest.php`
- `vendor\bin\phpcs --standard=phpcs.xml src\php\Slugs\LocalAttributeService.php src\php\Main.php tests\unit\Slugs\LocalAttributeServiceTest.php tests\unit\Slugs\TestLocalAttributeService.php`
