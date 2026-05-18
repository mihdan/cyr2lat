# Task 11.04: Document Gutenberg coverage through REST integration tests

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Document that Gutenberg/block-editor slug coverage for 7.0 is provided by REST integration tests.

## Scope

- Connect Gutenberg slug behavior to the REST API paths exercised by the integration suite.
- Document why this is sufficient for the current 7.0 scope.
- Keep browser tests optional unless a real UI-only regression is found.
- Do not add Playwright or Codeception coverage for Gutenberg in this task.

## Acceptance criteria

- The task documents REST integration coverage as the required Gutenberg coverage path.
- The parent plan records this decision.
- The decision does not weaken existing REST integration coverage.
- No browser-test dependency is introduced for Gutenberg coverage.

## Verification

- Passed with the implementation commit:
  - Documentation-only task; no build or PHPUnit run required.
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-11.04-document-gutenberg-rest-coverage.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Gutenberg/block-editor saves are covered through REST integration tests that exercise WordPress REST requests.
- This keeps the required 7.0 coverage on backend entry points where slug generation is persisted.
- Browser-only Gutenberg smoke coverage remains optional future work, not a 7.0 dependency.
- No Playwright or Codeception coverage was added for Gutenberg.
