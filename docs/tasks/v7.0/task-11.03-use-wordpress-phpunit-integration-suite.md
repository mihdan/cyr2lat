# Task 11.03: Use the WordPress PHPUnit integration suite

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Make the standard WordPress PHPUnit integration suite the required integration-test path for 7.0.

## Scope

- Document that integration coverage should run through `phpunit.integration.xml` and the WordPress test bootstrap.
- Confirm that current post, term, REST, and WooCommerce coverage uses this suite.
- Keep test strategy compatible with local and GitHub Actions execution.
- Do not introduce a separate required browser or acceptance test runner.

## Acceptance criteria

- The required 7.0 integration strategy points to WordPress PHPUnit integration tests.
- The parent plan records this decision.
- Existing REST and WooCommerce integration suites remain part of the required backend-first strategy.
- No new required acceptance-test framework is introduced.

## Verification

- Pending implementation.

## Implementation notes

- Pending implementation.
