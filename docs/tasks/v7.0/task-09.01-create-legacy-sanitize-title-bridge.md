# Task 09.01: Create `LegacySanitizeTitleBridge`

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Introduce a dedicated bridge boundary for the remaining broad `sanitize_title` fallback so legacy behavior can be isolated and reduced without mixing it with explicit slug services.

## Scope

- Add `CyrToLat\Slugs\LegacySanitizeTitleBridge`.
- Keep initial behavior compatible with the current `Main::sanitize_title()` entry point.
- Provide constructor dependencies or callbacks needed by the bridge without duplicating transliteration state.
- Add unit tests for the bridge boundary where practical.

## Acceptance criteria

- `LegacySanitizeTitleBridge` exists and is autoloaded.
- `Main` can delegate broad `sanitize_title` handling through the bridge.
- Existing broad `sanitize_title` behavior remains unchanged before later Epic 9 reductions.
- The service boundary is covered by targeted tests.

## Implemented Files

- `src/php/Slugs/LegacySanitizeTitleBridge.php`
- `tests/unit/Slugs/LegacySanitizeTitleBridgeTest.php`

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\Slugs\LegacySanitizeTitleBridge.php tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php docs\tasks\v7.0\task-09.01-create-legacy-sanitize-title-bridge.md`
