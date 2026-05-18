# Task 11.06: Revisit optional Playwright smoke tests later

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Document that optional Playwright smoke tests should be revisited only after a real UI-only bug is identified.

## Scope

- Define the trigger for reconsidering optional Playwright smoke tests.
- Keep Playwright outside the required 7.0 release criteria.
- Preserve backend-first integration coverage as the current required strategy.
- Do not add optional or required browser-test infrastructure in this task.

## Acceptance criteria

- The task documents the UI-only bug trigger for future Playwright smoke tests.
- The parent plan records this decision.
- 7.0 remains shippable without browser-test dependencies.
- No Playwright configuration, packages, or CI jobs are added.

## Verification

- Passed with the implementation commit:
  - Documentation-only task; no build or PHPUnit run required.
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-11.06-revisit-playwright-smoke-tests-later.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Optional Playwright smoke tests are deferred until there is a real UI-only bug that cannot be reproduced through backend entry points.
- Potential future smoke cases remain non-release-blocking examples rather than required 7.0 work.
- Backend-first coverage remains the required strategy for 7.0.
- No Playwright package, configuration, browser download, or CI job was added.
