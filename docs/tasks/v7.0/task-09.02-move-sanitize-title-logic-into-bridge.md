# Task 09.02: Move old `sanitize_title` logic into bridge

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Move the remaining broad `sanitize_title` decision tree out of `Main` and into `LegacySanitizeTitleBridge` while preserving the existing public filter behavior.

## Scope

- Move guards for empty titles, query context, term pre-slug decisions, and `ctl_pre_sanitize_title` into the bridge.
- Keep encoded term slug preservation and WooCommerce attribute preservation behavior intact.
- Keep `Main::sanitize_title()` as a thin compatibility wrapper.
- Avoid changing transliteration output.

## Acceptance criteria

- `Main::sanitize_title()` delegates to the bridge.
- Existing unit and integration tests for post, term, and WooCommerce slug flows still pass.
- The bridge owns broad fallback decisions instead of `Main`.
- No behavior change is introduced for known explicit services.

## Implemented Files

- `src/php/Main.php`
- `src/php/Slugs/LegacySanitizeTitleBridge.php`
- `tests/unit/Slugs/LegacySanitizeTitleBridgeTest.php`

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php tests\unit\Slugs\TermSlugServiceTest.php tests\unit\Slugs\GlobalAttributeServiceTest.php`
  - `vendor\bin\phpunit -c phpunit.integration.xml tests\integration\TermSlugIntegrationTest.php tests\integration\WooCommerceGlobalAttributeIntegrationTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\Main.php src\php\Slugs\LegacySanitizeTitleBridge.php tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php docs\tasks\v7.0\task-09.02-move-sanitize-title-logic-into-bridge.md`
