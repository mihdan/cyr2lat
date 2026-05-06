# Task 09.04: Add unknown bridge call logging

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 9 - Legacy bridge reduction.

## Goal

Add development-only diagnostics for unknown broad `sanitize_title` bridge calls so future explicit hook gaps can be identified without affecting production behavior.

## Scope

- Add logging only when development/debug conditions are enabled.
- Include enough context to identify unknown bridge calls without logging sensitive payloads unnecessarily.
- Avoid logging for known explicit contexts already handled by dedicated services.
- Add tests for the logger decision logic where practical.

## Acceptance criteria

- Production behavior and output are unchanged.
- Logging is disabled unless development/debug conditions are active.
- Unknown broad bridge calls can be diagnosed from the log context.
- Tests cover the logging gate or pure decision logic.
