# Task 06.06: Add WPML/Polylang term tests if practical

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 6 - Extract term slug handling.

## Goal

Review whether practical WPML/Polylang term-specific tests can be added with the current unit and integration infrastructure.

## Scope

- Preserve existing WPML/Polylang term behavior covered in `MainTest`.
- Add focused tests only if they can run without real WPML/Polylang installations.
- Document any deferred real-plugin coverage.

## Acceptance criteria

- Existing multilingual term tests remain green.
- Any practical mock-based term locale tests are added.
- Real plugin dependencies are not introduced for this task.

## Verification

```bash
vendor/bin/phpunit tests/unit/MainTest.php --filter "pll|wpml|term"
composer unit
composer phpcs
```
