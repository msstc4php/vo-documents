# Conventions

- Exception messages name the document and the length or reason, never the input.
- Tool configuration is byte-identical to the bundle-standard `php84` templates.
- Tests: `tests/Unit` (what Infection runs), fixtures in `tests/Fixture`.
- Use the global `\Normalizer` (ext-intl or symfony/polyfill-intl-normalizer); never import `Symfony\Polyfill\...` (deptrac forbids the Symfony namespace).
- `Internal\*` helpers are `final` classes with static methods marked `@internal`; `Contract\Subdivision` is an interface extending `\BackedEnum` (value = ISO 3166-2 code).
