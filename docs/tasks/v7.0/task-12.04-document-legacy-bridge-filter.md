# Task 12.04: Document legacy bridge filter

## Status

Done.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 12 - Documentation and release preparation.

## Goal

Document the `ctl_enable_legacy_sanitize_title_bridge` filter and its role in the 7.0 legacy bridge strategy.

## Scope

- Explain why the legacy `sanitize_title` bridge exists.
- Document how to enable or disable the bridge through `ctl_enable_legacy_sanitize_title_bridge`.
- Make clear that explicit slug services are the primary 7.0 behavior.
- Do not change the bridge implementation in this task.

## Acceptance criteria

- Release-facing documentation includes the legacy bridge filter name and usage example.
- The documentation explains that the bridge is a compatibility fallback, not the preferred primary path.
- The parent plan marks the task complete after implementation.
- No runtime behavior changes are introduced.

## Verification

- `vendor\bin\phpcs --standard=phpcs.xml readme.txt docs\tasks\v7.0\task-12.04-document-legacy-bridge-filter.md docs\tasks\v7.0\cyr2lat-7.0-development-plan-updated.md`

## Implementation notes

- Added a `readme.txt` FAQ entry for `ctl_enable_legacy_sanitize_title_bridge`.
- Documented the bridge as a compatibility fallback while explicit 7.0 slug handlers remain the preferred paths.
- Included the disable snippet and documented the filter arguments at a high level.
