# Task 09.03: Add `ctl_enable_legacy_sanitize_title_bridge` filter

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Add a documented filter gate that allows developers and tests to disable the remaining broad legacy `sanitize_title` bridge while keeping it enabled by default for backward compatibility.

## Scope

- Add `ctl_enable_legacy_sanitize_title_bridge` filter around broad bridge processing.
- Keep the default value enabled.
- Ensure disabling the bridge returns the incoming sanitized title unchanged.
- Add unit or integration coverage for enabled and disabled states.

## Acceptance criteria

- The filter name is implemented exactly as `ctl_enable_legacy_sanitize_title_bridge`.
- The broad bridge remains enabled by default.
- Returning `false` from the filter disables broad fallback transliteration.
- Tests cover both enabled and disabled behavior.
