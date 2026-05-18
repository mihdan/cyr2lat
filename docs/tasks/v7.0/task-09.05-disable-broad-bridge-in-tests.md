# Task 09.05: Disable broad bridge in tests

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Run relevant test paths with the broad legacy `sanitize_title` bridge disabled and fix uncovered behavior by relying on explicit services instead of the fallback.

## Scope

- Disable `ctl_enable_legacy_sanitize_title_bridge` in targeted tests where broad fallback should no longer be required.
- Identify post, term, WooCommerce local/global/variation paths still depending on broad fallback.
- Fix uncovered paths through explicit service hooks only.
- Preserve backward compatibility by keeping the bridge enabled by default outside these tests.

## Acceptance criteria

- Relevant integration tests pass with the broad legacy bridge disabled.
- Any uncovered slug paths are handled by explicit services.
- Existing compatibility tests for the bridge-enabled default still pass.
- The Epic 9 completion state is documented in the plan.

## Implemented Files

- `src/php/Main.php`
- `src/php/Slugs/LegacySanitizeTitleBridge.php`
- `src/php/Slugs/PostSlugService.php`
- `src/php/Slugs/TermSlugService.php`
- `tests/unit/Slugs/LegacySanitizeTitleBridgeTest.php`
- `tests/unit/Slugs/PostSlugServiceTest.php`
- `tests/unit/Slugs/TermSlugServiceTest.php`
- `tests/integration/PostSlugIntegrationTest.php`
- `tests/integration/TermSlugIntegrationTest.php`
- `tests/integration/WooCommerceGlobalAttributeIntegrationTest.php`
- `tests/integration/WooCommerceLocalAttributeIntegrationTest.php`
- `tests/integration/WooCommerceVariationAddToCartIntegrationTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\Slugs\PostSlugServiceTest.php tests\unit\Slugs\TermSlugServiceTest.php tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php`
  - `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\TermSlugIntegrationTest.php`
  - `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceLocalAttributeIntegrationTest.php`
  - `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceVariationAddToCartIntegrationTest.php`
  - `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\WooCommerceGlobalAttributeIntegrationTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php src\php\Slugs\LegacySanitizeTitleBridge.php src\php\Slugs\PostSlugService.php src\php\Slugs\TermSlugService.php tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php tests\unit\Slugs\PostSlugServiceTest.php tests\unit\Slugs\TermSlugServiceTest.php tests\integration\PostSlugIntegrationTest.php tests\integration\TermSlugIntegrationTest.php tests\integration\WooCommerceGlobalAttributeIntegrationTest.php tests\integration\WooCommerceLocalAttributeIntegrationTest.php tests\integration\WooCommerceVariationAddToCartIntegrationTest.php docs\tasks\v7.0\task-09.05-disable-broad-bridge-in-tests.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`
