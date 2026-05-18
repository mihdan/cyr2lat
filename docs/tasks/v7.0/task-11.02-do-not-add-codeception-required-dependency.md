# Task 11.02: Do not add Codeception as a required dependency

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Keep Codeception out of the required 7.0 dependency set and avoid adding an acceptance-test requirement for the release.

## Scope

- Review dependency and CI files for required Codeception usage.
- Preserve the standard PHPUnit-based WordPress integration workflow.
- Document the decision in this task and the parent plan.
- Do not add Codeception packages, suites, CI jobs, or bootstrap configuration.

## Acceptance criteria

- Codeception is not listed as a required dependency for 7.0.
- CI does not require Codeception setup or execution.
- The parent plan records that Codeception is not required for the 7.0 release.
- Existing integration coverage remains based on WordPress PHPUnit and targeted WooCommerce integration tests.

## Verification

- Passed with the implementation commit:
  - Documentation-only task; no build or PHPUnit run required.
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-11.02-do-not-add-codeception-required-dependency.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- `composer.json` does not list Codeception as a project dependency.
- No Codeception suite, bootstrap, command, or CI job was added for 7.0.
- Existing Codeception references come from installed vendor package metadata/tests, not from Cyr-To-Lat required release infrastructure.
- Required 7.0 integration coverage remains on WordPress PHPUnit and WooCommerce integration tests.
