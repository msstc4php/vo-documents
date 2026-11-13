# CLAUDE.md

Guidance for Claude Code in this repository. Deep references live under `.claude/docs/`.

## What this is

`msstc4php/vo-documents`: value objects for identity documents and personal names, with ICAO 9303 Latin names.
PHP >= 8.4. Namespace `MSSTC4PHP\ValueObject\Documents\`. Framework-agnostic; Doctrine storage is the separate
`vo-documents-doctrine` bridge. Requirements and plans live under `docs/`.

## Common commands

Local install against the sibling `../vo-base`: `COMPOSER=composer-local.json composer update
--ignore-platform-req=php`. In CI the install is `composer-ci.json`.

`composer-local.json` is gitignored. Create it from `composer-ci.json`: copy the file and add a path repository with
`{"type": "path", "url": "../vo-base", "options": {"symlink": true}}` before the VCS entry, and require
`msstc4php/vo-base` as `dev-main as 1.1.0`.

- `COMPOSER=composer-local.json make check`, `make test`, `make infection`, `make fix`.

`composer-ci.lock` (written by the `composer-ci.json` install) is intentionally untracked and not ignored, as in
`vo-base-spiral`; never commit it.

## Standard

Tool configuration is byte-identical to the `php84` templates of `msstc4symfony/bundle-standard`; CI's `templates` job
enforces it. Do not edit `.php-cs-fixer.dist.php`, `rector.php`, `phpstan*.neon`, `phpunit.xml.dist`,
`infection.json5`, `Makefile` or `.gitignore` here. The hex layout of that standard does not apply.

Exception messages never contain the input or any part of it.
