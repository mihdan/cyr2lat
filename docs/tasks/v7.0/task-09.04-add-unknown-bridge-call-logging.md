# Task 09.04: Add unknown bridge call logging

## Status

Implemented.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Add development-only diagnostics for unknown broad `sanitize_title` bridge calls so future explicit hook gaps can be identified without affecting production behavior.

## Scope

- Add logging only when development/debug conditions are enabled.
- Include enough context to identify unknown bridge calls without logging sensitive payloads unnecessarily.
- Avoid logging for known explicit contexts already handled by dedicated services.
- Add tests for the logger decision logic where practical.

## Acceptance criteria

- Production behavior and output are unchanged.
- Logging is disabled unless development/debug conditions are active.
- Unknown broad bridge calls can be diagnosed from the log context.
- Tests cover the logging gate or pure decision logic.

## Implemented Files

- `src/php/Slugs/LegacySanitizeTitleBridge.php`
- `tests/unit/Slugs/LegacySanitizeTitleBridgeTest.php`

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\Slugs\LegacySanitizeTitleBridge.php tests\unit\Slugs\LegacySanitizeTitleBridgeTest.php docs\tasks\v7.0\task-09.04-add-unknown-bridge-call-logging.md`
