# Task 11.02: Do not add Codeception as a required dependency

## Status

Planned.

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

- Pending implementation.

## Implementation notes

- Pending implementation.
