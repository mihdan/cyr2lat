# Task 11.01: Do not add Playwright as a required dependency

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Keep Playwright out of the required 7.0 dependency set unless a UI-only regression proves it is needed.

## Scope

- Review project dependency files and CI configuration for required Playwright usage.
- Preserve the backend-first 7.0 test strategy.
- Document the decision in this task and the parent plan.
- Do not add Playwright packages, browsers, CI jobs, or install steps.

## Acceptance criteria

- Playwright is not listed as a required dependency for 7.0.
- CI does not require Playwright installation or browser downloads.
- The parent plan records that Playwright is not required for the 7.0 release.
- Existing integration coverage remains based on backend test entry points.

## Verification

- Pending implementation.

## Implementation notes

- Pending implementation.
