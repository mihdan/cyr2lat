# Task 11.05: Document WooCommerce integration coverage

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 11 - Browser tests decision.

## Goal

Document that WooCommerce coverage for 7.0 is provided by CRUD, API, admin-handler, and frontend-cart integration paths.

## Scope

- Summarize the WooCommerce backend entry points already covered by integration tests.
- Document why these paths are the required 7.0 coverage instead of browser tests.
- Keep the decision aligned with global, local, and variation attribute work from earlier epics.
- Do not add new required browser-test infrastructure.

## Acceptance criteria

- The task documents the required WooCommerce integration coverage paths.
- The parent plan records this decision.
- Existing WooCommerce integration suites remain the required coverage for 7.0.
- No Playwright or Codeception dependency is added for WooCommerce coverage.

## Verification

- Passed with the implementation commit:
  - Documentation-only task; no build or PHPUnit run required.
  - `vendor\bin\phpcs --standard=phpcs.xml docs\tasks\v7.0\task-11.05-document-woocommerce-integration-coverage.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- WooCommerce global attribute coverage is provided by integration tests for attribute create/update, REST/API paths, terms, and backend layered-navigation filtering.
- WooCommerce local and variation coverage is provided by CRUD/admin-handler, REST/API, frontend add-to-cart, and cart session integration paths.
- These backend entry points cover the slug and attribute persistence behavior required for 7.0 without browser infrastructure.
- No Playwright or Codeception dependency was added for WooCommerce coverage.
