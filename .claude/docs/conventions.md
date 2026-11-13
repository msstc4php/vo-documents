# Conventions

- Exception messages name the document and the length or reason, never the input.
- Tool configuration is byte-identical to the bundle-standard `php84` templates.
- Tests: `tests/Unit` (what Infection runs), fixtures in `tests/Fixture`.
- Use the global `\Normalizer` (ext-intl or symfony/polyfill-intl-normalizer); never import `Symfony\Polyfill\...` (deptrac forbids the Symfony namespace).
- `Internal\*` helpers are `final` classes with static methods marked `@internal`; `Contract\Subdivision` is an interface extending `\BackedEnum` (value = ISO 3166-2 code).
- Name sources live in `docs/sources/<AREA>.md` with verbatim quotes and section numbers; code tables are copied from
  there and the class docblock cites the PDF (`@see`). ICAO "except …" (language-specific) spellings are not variants.
- Official sources are cited with publication.pravo.gov.ru (RU) or the gazette when the portal has no entry;
  aggregator copies (garant, legalacts) only as "quoted from" in the source note.
- A national name part extends `Name\AbstractNamePart` (hooks: `letters()`, `word()`, `innerSeparators()`, `label()`); message `Invalid <label> (<n> characters).`; allowed
  separators per part come from the country's source note (RU: given names and patronymics have no apostrophe).
- A composite name is a `final readonly` class with public part properties, its codec stores only an explicit Latin
  override (`latin: null` otherwise) and is listed in `Codecs::all()`.
- `Icao\Transliterator::variants()` caps at 4096 combinations (untrusted input); `toLatin()` has no cap.
