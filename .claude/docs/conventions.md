# Conventions

- Exception messages name the document and the length or reason, never the input.
- Tool configuration is byte-identical to the bundle-standard `php84` templates.
- Tests: `tests/Unit` (what Infection runs), fixtures in `tests/Fixture`.
- Use the global `\Normalizer` (ext-intl or symfony/polyfill-intl-normalizer); never import `Symfony\Polyfill\...`
  (deptrac forbids the Symfony namespace).
- `Internal\*` helpers are `final` classes with static methods marked `@internal`; `Contract\Subdivision` is an
  interface extending `\BackedEnum` (value = ISO 3166-2 code).
- Name sources live in `docs/sources/<AREA>.md` with verbatim quotes and section numbers; code tables are copied from
  there and the class docblock cites the PDF (`@see`). ICAO "except …" (language-specific) spellings are not variants.
- Official sources are cited with publication.pravo.gov.ru (RU) or the gazette when the portal has no entry;
  aggregator copies (garant, legalacts) only as "quoted from" in the source note.
- A national name part extends `Name\AbstractNamePart` (hooks: `letters()`, `word()`, `innerSeparators()`, `label()`);
  message `Invalid <label> (<n> characters).`; allowed separators per part come from the country's source note (RU:
  given names and patronymics have no apostrophe).
- A composite name is a `final readonly` class with public part properties, its codec stores only an explicit Latin
  override (`latin: null` otherwise) and is listed in `Codecs::all()`. A Latin-script family + given name extends
  `Name\Latin\AbstractFamilyGivenPersonName<FamilyName, GivenName>` (or `<…, GivenName|null>`), keeps only its
  constructor (typed parts, calls the parent) and `overridePolicy()`; its codec extends
  `AbstractFamilyGivenPersonNameCodec<PersonName, string>` (`string|null` for an optional given name) with
  `valueObjectClass()`, `shape()`, `create()` and `givenValue()` (a required given name: `$given ?? throw
  $this->malformed()`). Another layout (ES) extends `AbstractLatinPersonName`. The derived form uses `toLatin()` (never
  `variants()`); the override goes through a `Name\LatinOverridePolicy` subclass chosen per country: `AnyIcaoSpelling`
  when the passport authority writes a documented spelling (RU, GB), `TransliterationVariant` with a lazy `Closure` of
  the spellings when only table spellings are acceptable (AU, DE, FR, IT, ES, NL, CH, TR); the choice and its quote go
  in the country's source note. `resolve()` drops an override equal to the derived form in MRZ terms (hyphen ≡ `<`), so
  equality and the codec payload agree.
- `Icao\Transliterator::variants()` caps at 4096 combinations (untrusted input); `toLatin()` has no cap. The builder
  calls it per ICAO word (split at spaces and hyphens), so the cap is per word.
- Name tests use `Tests\Fixture\RefusesNameWithoutEchoingInput::assertNameRefusedWithoutEcho()`: exact message and no
  input word of three or more letters in it (as typed or upper-cased).
- A document number VO parses with `Internal\Digits::exactly($input, $length, $label)` (`exactlyStripped()` when the
  separators are already removed; strips `Digits::SEPARATORS`: space, `-`, tab, CR/LF, NBSP, U+2007, U+202F; message
  `Invalid <label> (<n> characters).`, or `(too long)` above `Digits::MAX_REPORTED_LENGTH` = 40; right length with a
  non-digit: `(invalid character)`), then its checksum where an official algorithm exists (`Invalid <label> checksum.`).
  The label is a `private const string DOCUMENT` per class, used in every message. `masked()` keeps the last 4
  characters (`Mask::keepLastDigits(formatted(), 4)`) unless the source note records an official rule; a number of 8
  characters or fewer keeps 3 (CH identity document, GB Northern Ireland licence), and the AU classes keep 3 (numbers of
  at most 10 characters, AU.md "Masking rule"). A field without a published check digit is shape-only, said on the class
  and in the source note.
- `AbstractStringValueObject::$value` is a plain `string`; where a getter must return `non-empty-string` without a
  literal in a `sprintf()` format, wrap it in `Internal\Text::nonEmpty()` (the only such helper); readonly helper
  properties assigned in `parse()` fail PHPStan `property.readOnlyAssignNotInConstructor`.
- Document tests use `Tests\Fixture\RefusesWithoutEchoingInput::assertRefusedWithoutEcho()`: exact message and no three
  consecutive input digits in it (as typed and with separators removed).
- Every enum implementing `Contract\Subdivision` is string-backed with `value === isoCode()`;
  `tests/Unit/Contract/SubdivisionTest.php` scans `src/` and checks new enums automatically.
- Source lines stay within 120 characters. php-cs-fixer re-joins a call with a single wrapped argument and an arrow
  function body moved to its own line; extract a `$message` variable, a constant or a first-class callable instead.
- Masking layout: when the source publishes a spaced display form, `formatted()` gives it and `masked()` keeps that
  layout (`Nir`: `* ** ** ** *** *32 - 50`, FR TIN `** ** *** **0 053`, DE VSNR); otherwise `masked()` masks the
  compact stored value (`*****2345`). Letters are masked with `Mask::keepLastCharacters()` before the layout is
  applied; digit-only layouts use `Mask::keepLastDigits(formatted(), n)`.
