# Task 12.05: Document testing strategy

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Document the required 7.0 testing strategy for contributors and release preparation.

## Scope

- Document unit tests, WordPress PHPUnit integration tests, REST/Gutenberg integration tests, and WooCommerce integration tests.
- State that Codeception and Playwright are not required release dependencies for 7.0.
- Keep the strategy aligned with Epic 11 decisions.
- Do not add new test frameworks or CI jobs in this task.

## Acceptance criteria

- Release-facing or contributor-facing documentation describes the required testing layers.
- The documentation explicitly excludes required Codeception and Playwright dependencies for 7.0.
- The parent plan marks the task complete after implementation.
- No dependency or CI configuration changes are introduced.

## Verification

- `vendor\bin\phpcs --standard=phpcs.xml docs\testing-strategy-7.0.md docs\tasks\v7.0\task-12.05-document-testing-strategy.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Added `docs/testing-strategy-7.0.md` as contributor/release-facing testing documentation.
- Documented required unit, WordPress PHPUnit integration, REST integration, WooCommerce integration, and PHPCS layers.
- Documented that Codeception and Playwright are not required 7.0 release dependencies.
