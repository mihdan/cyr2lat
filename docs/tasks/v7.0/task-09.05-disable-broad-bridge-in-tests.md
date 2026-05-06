# Task 09.05: Disable broad bridge in tests

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Run relevant test paths with the broad legacy `sanitize_title` bridge disabled and fix uncovered behavior by relying on explicit services instead of the fallback.

## Scope

- Disable `ctl_enable_legacy_sanitize_title_bridge` in targeted tests where broad fallback should no longer be required.
- Identify post, term, WooCommerce local/global/variation paths still depending on broad fallback.
- Fix uncovered paths through explicit service hooks only.
- Preserve backward compatibility by keeping the bridge enabled by default outside these tests.

## Acceptance criteria

- Relevant integration tests pass with the broad legacy bridge disabled.
- Any uncovered slug paths are handled by explicit services.
- Existing compatibility tests for the bridge-enabled default still pass.
- The Epic 9 completion state is documented in the plan.
