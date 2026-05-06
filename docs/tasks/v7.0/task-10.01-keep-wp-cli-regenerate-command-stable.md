# Task 10.01: Keep `wp cyr2lat regenerate` command stable

## Status

Planned.

## Parent plan

`docs/tasks/v7.0/cyr2lat-7.0-development-plan-updated.md`

## Epic

Epic 10 - Converter and WP-CLI review.

## Goal

Ensure the public WP-CLI command remains compatible while converter internals move to explicit slug services.

## Scope

- Keep the command name `wp cyr2lat regenerate` unchanged.
- Preserve supported arguments and defaults for existing users.
- Verify that command execution still delegates to the existing converter flow.
- Add or update tests around WP-CLI argument parsing where practical.

## Acceptance criteria

- `wp cyr2lat regenerate` remains registered under the same command name.
- The command keeps passing parsed options to `Converter::convert_existing_slugs()`.
- Existing CLI behavior is covered by targeted unit tests.
- No migration-only WooCommerce attribute behavior is introduced by this task.

## Verification

- To be run with the implementation commit:
  - `vendor\bin\phpunit tests\unit\WPCLITest.php tests\unit\ConverterTest.php`
  - `vendor\bin\phpcs --standard=phpcs.xml src\php\WPCLI.php tests\unit\WPCLITest.php docs\tasks\v7.0\task-10.01-keep-wp-cli-regenerate-command-stable.md`
