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
- Official sources are cited with publication.pravo.gov.ru (RU) or the gazette when the portal has no entry; aggregator
  copies (garant, legalacts) only as "quoted from" in the source note.
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
  when the passport authority writes a documented spelling (RU, GB, CA, BR), `TransliterationVariant` with a lazy
  `Closure` of the spellings when only table spellings are acceptable (AU, DE, FR, IT, ES, NL, CH, TR, US, MX); the
  choice and its quote go in the country's source note. `resolve()` drops an override equal to the derived form in MRZ
  terms (hyphen ≡ `<`), so equality and the codec payload agree.
- `Icao\Transliterator::variants()` caps at 4096 combinations (untrusted input); `toLatin()` has no cap. The builder
  calls it per ICAO word (split at spaces and hyphens), so the cap is per word.
- Name tests use `Tests\Fixture\RefusesNameWithoutEchoingInput::assertNameRefusedWithoutEcho()`: exact message and no
  input word of three or more letters in it (as typed or upper-cased).
- A document number VO parses with `Internal\Digits::exactly($input, $length, $label)` (`exactlyStripped()` when the
  separators are already removed; strips `Digits::SEPARATORS`: space, `-`, tab, CR/LF, NBSP, U+2007, U+202F; message
  `Invalid <label> (<n> characters).`, or `(too long)` above `Digits::MAX_REPORTED_LENGTH` = 40; right length with a
  non-digit: `(invalid character)`), then its checksum where an official algorithm exists (`Invalid <label>
  checksum.`). The label is a `private const string DOCUMENT` per class, used in every message. `masked()` keeps the
  last 4 characters (`Mask::keepLastDigits(formatted(), 4)`) unless the source note records an official rule; a number
  of 8 characters or fewer keeps 3 (CH identity document, GB Northern Ireland licence), and the AU classes keep 3
  (numbers of at most 10 characters, AU.md "Masking rule"). A field without a published check digit is shape-only, said
  on the class and in the source note.
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
- A sub-national number with several shapes (US DL/ID) uses `Internal\NumberShapes(shape, …)` (length derived from the
  atoms `A`, `9`, `[...]`, `{n}`; anything else is an `InvalidArgumentException`): wrong length → `(N characters)`, a
  length some shape has but no match → `(invalid character)`. One class per jurisdiction, even where the licence and ID
  card have different shapes (AL, IL): the class accepts the union and its doc says which is which. `fromNumberIn()`
  for a jurisdiction with no published format throws `Invalid US-XX DL/ID number (format not covered).`; the factory's
  document label is `American DL/ID` (as Task 1's `American passport book number`).
- CA provincial documents (plan 3 Task 5b) copy the US DL/ID pattern with a `stored()` hook for rules beyond the shape.
  Labels: `CA-XX driver's licence number` / `CA-XX health card number` per class, `Canadian driver's licence` /
  `Canadian health card` in the factories; an uncovered licence jurisdiction throws `Invalid CA-XX driver's licence
  number (format not covered).` (no health-card equivalent is reachable: all 13 are covered). A derived range check (QC
  month/day, NS day/month) is a regex constant matched with `preg_match()`, not int casts and comparisons: casts of
  digit substrings are equivalent mutants for Infection.
- **Per-subdivision factories with an overlap oracle** (AU, US, CA, MX, BR): one final class per covered jurisdiction
  on an abstract base, a `Jurisdiction` enum of every subdivision (ISO 3166-2 order), and a factory over
  `Internal\SubdivisionResolver` with `fromNumber()` (one match, none, or `AmbiguousJurisdictionException`),
  `fromNumberIn()` and `jurisdictionsMatching()`. `docs/sources/<CC>.md` holds the shape table and its "Overlap" table;
  the factory test has every overlap row (the oracle). `classFor()` lists uncovered cases as `=> null`, so a new enum
  case breaks the `match`. With one covered subdivision (MX, BR) `fromNumber()` reports other states' numbers as that
  state: say so in the docs.
- **"Format not covered" message:** `fromNumberIn()` for a jurisdiction without a published format throws `Invalid
  <CC>-XX <document> (format not covered).`; `fromNumber()` of an unmatched number says "(fits no jurisdiction)" when
  some jurisdiction has the length, otherwise the length.
- **Source notes list every candidate:** each jurisdiction gets a row with its verdict (covered, or not covered with a
  reason). A host that could not be read (timeout, WAF, TLS, no Internet Archive capture) is marked "re-check" with the
  UTC date; a missing format is "no published format" only after the search is recorded. Look at the issuer's online
  form fields (`maxlength`, regex) before concluding that a format is unpublished; a maximum length is not a format.
- **Official = any government body's own publication** (controller ruling): ANS routines, a state validator's field
  rule and the SSP-SP form count; vendor guides, generator sites and other bodies' intake policies (repeated-digit
  refusals) do not. Checksums are verified only when an official text gives the algorithm.
- **Canonical value:** a number that the source says is padded or stripped (BC and SK licences to 8 digits, the BR
  voter registration number to 12) is stored in the padded form, and `from(value())` round-trips; the checks that need
  the raw input run in the `stored()` hook.
- **Equivalent mutants with one covered subdivision:** `ArrayOneItem` on `coveredJurisdictions()` (MX, BR) escapes
  until a second subdivision is covered.

## Asia (plan 4)

- **Digit folding is package-wide for numbers, never for names.** `Internal\Digits::fold()` runs before every number
  parse: fullwidth digits and letters (letters upper-cased) and Arabic-Indic digits (U+0660-0669, U+06F0-06F9) become
  ASCII, and U+3000 is a separator. Devanagari digits are not folded (an Aadhaar typed in them is refused). A name part
  refuses a compatibility character instead (it would be a second stored spelling).
- **Script classes use `\p{sc=...}` only** (`Han`, `Hangul`, `Arabic`, `Devanagari`, `Latin`, `Cyrillic`). Characters of
  a name that are Script=Common or Inherited are listed one by one, never through a wider class: `ー` U+30FC and `〆`
  U+3006 (JP), `·` U+00B7 (CN, KR), the harakat U+064B-0652 and U+0670 (SA), the Devanagari ZWJ/ZWNJ (IN). The
  tatweel U+0640 is removed, U+30FB and U+2022 are stored as U+00B7.
- **Mandatory Latin name** (CN, KR, ID, SA, IN-Devanagari): the passport spelling is a personal choice, so the Latin
  `Icao\PersonName` is a required constructor argument and a required codec key, never derived; nothing compares it with
  the national form. `Name\RequiredLatin\AbstractFamilyGivenPersonName` (CN, KR) takes a nullable `given`: a name with
  no family/given split is stored whole in `family`. ID, SA and IN-Devanagari have their own layouts. JP is the one
  that derives (MOFA Hepburn) and so takes an optional override.
- **A class name claims only what it covers:** `OrdinaryPassportNumber` (CN `E`/`G`, KR `M` series) refuses the other
  series; the KR resident class refuses foreigner digits and the foreigner class resident digits.
- **A document number that is another document's number gets no class** (CN and SA driving licence, ID SIM since
  2024): README and the source note say which class to use.
- **Regions and codes are plain strings** (`administrativeDivisionCode()`, `regionCode()`,
  `publicSafetyCommissionCode()`, `nationalityCode()`) because the source codes do not map one to one to ISO 3166-2
  (Hokkaido has five commissions, KoROAD names are not ISO). No `Jurisdiction` enum for Asia.
- **Line length is 120 characters in `src/` and `tests/`** (the rule of the style commit of plan 4). A URL in an `@see`
  cannot be wrapped and is exempt; a long `\u{...}` string is split with `.` concatenation.
