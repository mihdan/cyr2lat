# Task 12.07: Test with WordPress latest and WooCommerce latest

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Run and document the release verification result against the local latest WordPress and WooCommerce test environment.

## Scope

- Identify the WordPress and WooCommerce versions used by the integration-test environment.
- Run the relevant unit and integration test suites for 7.0 release confidence.
- Record the verification result in the task and parent plan.
- Do not change runtime code unless the verification exposes a release-blocking bug.

## Acceptance criteria

- The task records the WordPress and WooCommerce versions used for verification.
- Relevant unit, WordPress integration, REST integration, and WooCommerce integration tests pass.
- The parent plan marks the task complete after implementation.
- Any failures are fixed or explicitly documented before release readiness is claimed.

## Verification

- Local integration environment versions: WordPress `7.0-RC2-62287`, WooCommerce `10.7.0`.
- `vendor\bin\phpunit tests\unit` — 480 tests, 539 assertions.
- `vendor\bin\phpunit -c phpunit.integration.xml tests\integration` — 54 tests, 242 assertions, 2 skipped.
- `composer phpcs` — 85 files checked.

## Implementation notes

- Updated stale unit-test mocks that still assumed pre-service Main behavior: WooCommerce attribute helpers now account for `wc_get_attribute_taxonomies()`, local attribute tests provide integer `did_action()` results, CLI detection tests account for `class_exists( 'WP_CLI', false )`, and post-name tests assert the current explicit slug callback path.
- Recorded latest WordPress/WooCommerce verification results for the release task.
