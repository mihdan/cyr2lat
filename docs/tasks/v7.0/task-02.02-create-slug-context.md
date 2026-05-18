# Task 02.02: Create `SlugContext` structure

## Status

Draft for review.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 2 - Extract transliteration core.

## Goal

Create a lightweight internal context structure that can describe why transliteration is being performed without relying on WordPress request globals or mutable state in `Main`.

The context should be usable by the transliteration core and by later services for posts, terms, filenames, WooCommerce attributes, REST, AJAX, CLI, admin, and frontend flows.

## Why this exists

The current `sanitize_title` path has to infer intent from broad WordPress filters, request variables, action state, and flags such as `$is_term` and `$taxonomies`. Version 7.0 needs explicit data-type-oriented handlers. A context object or associative structure gives those handlers a common language without forcing every service to inspect globals.

## Files to inspect

- `src/php/Main.php`
- `src/php/Request.php`
- `tests/unit/MainTest.php`
- `docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Primary implementation area

- `src/php/Transliteration/SlugContext.php`
- `tests/unit/Transliteration/SlugContextTest.php`

If the project decides an associative array is preferable to a value object, document the decision in this task's implementation notes and keep the shape explicit and tested.

## Scope

- Add a small internal `SlugContext` value object or equivalent explicit context structure.
- Support data type values needed by the parent plan:
  - `post`
  - `term`
  - `filename`
  - `wc_global_attribute`
  - `wc_local_attribute`
  - `wc_variation_attribute`
- Support source values needed by the parent plan:
  - `admin`
  - `frontend`
  - `ajax`
  - `rest`
  - `cli`
  - `unknown`
- Allow optional object metadata such as object ID, object type, taxonomy, post type, locale, and raw source label where useful.
- Provide conservative defaults so existing callers can adopt the context incrementally.
- Keep the object immutable if practical.
- Add tests for construction, defaults, and common contexts used by later tasks.

## Out of scope

- Do not require every existing caller to use `SlugContext` in this task.
- Do not remove `$is_term`, `$taxonomies`, or WooCommerce request guards from `Main`.
- Do not change slug generation behavior.
- Do not change REST, CLI, post, term, filename, or WooCommerce hooks.
- Do not introduce PHP language features beyond the project's configured compatibility target.

## Acceptance criteria

- A documented context structure exists under the transliteration core namespace or another clearly justified internal namespace.
- The context can represent all data types and sources listed in the 7.0 plan.
- Default construction is safe for legacy callers that do not yet know the precise context.
- Tests cover the default context and at least post, term, filename, REST, AJAX, CLI, and WooCommerce attribute examples.
- No production behavior changes are introduced beyond adding the structure.
- The implementation keeps future service extraction simpler rather than coupling the core to WordPress globals.

## Verification

Run:

```bash
composer unit
composer phpcs
```

For a faster local loop, run:

```bash
vendor/bin/phpunit tests/unit/Transliteration/SlugContextTest.php
```

## Notes for implementer

- Prefer boring, explicit field names over clever inference.
- Avoid enums unless the project's supported PHP versions make them safe.
- If constants are used for types and sources, test their values because future services will depend on them.
