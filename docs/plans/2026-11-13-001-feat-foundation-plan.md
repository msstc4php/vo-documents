# vo-documents, plan 1 of 4: foundation, ICAO name, Russia, Australia

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create `msstc4php/vo-documents` with every shared building block and two reference countries that
exercise all three patterns: Russia (national documents, Cyrillic names, ICAO transliteration) and Australia
(documents issued by states and territories, jurisdiction factory, Latin names).

**Architecture:** String value objects extend the core `AbstractStringValueObject`, so `ValueObjectMapper` and every
bridge already handle them. Composite names are `ValueObject`s with a `PayloadCodec` shipped by the package. Checksums,
masking and Unicode handling live in `Internal\` helpers so the later country plans only declare data.

**Tech Stack:** PHP 8.4, `msstc4php/vo-base ^1.1`, `ext-mbstring`, `symfony/polyfill-intl-normalizer`, PHPUnit,
PHPStan max, Infection.

**Spec:** `vo-base/docs/brainstorms/2026-11-13-person-documents-and-names-requirements.md`

## Plan series (wave 1)

The spec's wave 1 is too large for one plan. Each plan ends with green gates; the package is released once, as 0.1.0,
after plan 4.

1. **This plan:** scaffold of `vo-documents` and `vo-documents-doctrine`, shared contracts and helpers, `Icao\PersonName`, ICAO transliteration (Latin and Cyrillic
   tables), name framework, RU (names + documents), AU (names + national and state/territory documents).
2. Europe (Latin script, ICAO multinational table): DE, FR, IT, ES, NL, CH, GB, TR.
3. Americas: US (national + 51 state/DC driving licences and ID cards, factory), CA (national + 13 provinces and
   territories), MX, BR (including state-issued documents).
4. Asia and the Gulf: CN, JP (kana reading to passport Hepburn), KR, IN, ID, SA; README "not covered" list completed;
   release 0.1.0.

Every plan also adds the Doctrine types for its classes. Plans 2-4 are written after this plan lands, reusing its interfaces verbatim.

## Global Constraints

- Package `msstc4php/vo-documents`, namespace `MSSTC4PHP\ValueObject\Documents\`, checkout
  `msstc4php/vo-documents` next to the other packages; `require`: `php >=8.4`, `msstc4php/vo-base ^1.1`,
  `ext-mbstring *`, `symfony/polyfill-intl-normalizer ^1.31`. Nothing else at runtime.
- Scaffolding, tool configs, CI and `.claude/docs` exactly as `vo-base-spiral` (byte-identical tool configs from the
  `php84` templates of `msstc4symfony/bundle-standard`; CI `templates` job runs `cmp`). Gates: PHPStan max +
  `phpstan-ci.neon`, cs-fixer, rector, PHPUnit, Infection >= 75% covered MSI, lowest-deps job.
- `__toString()` / `jsonSerialize()` give the full value; masking only through `masked()`.
- **No exception message contains the input or any part of it.** Messages give the document name and the length or
  the reason ("Invalid SNILS (14 characters).", "Invalid SNILS checksum."). Never call `InvalidValueObjectException::excerpt()`.
- Every exception thrown for invalid input extends the core `InvalidValueObjectException`.
- Every format, checksum, mask rule and table cites its official source with `@link` on the class: an agency page,
  official gazette or a standard cited by number. Never a blog or a generator site.
- Encoded dates are `DateTimeImmutable` at 00:00:00 UTC; an impossible date is invalid input.
- Name input: NFC-normalised, trimmed, inner runs of whitespace collapsed to one space; letter case kept as given.
- Code comments in English, minimal; strict PHPStan types (`non-empty-string`, `list<>`, `class-string<>`).
- Commits on local `main` of the new repo; no push, no tag, no GitHub action without the owner.

## Decisions this plan makes (beyond the spec)

- **Canonical `Icao\PersonName` string:** characters `A-Z`, `-` and `<`. `<<` separates the primary identifier from
  the secondary identifiers; a single `<` stands for a space inside either. Hyphens stay `-` (so the visual-zone form
  `RIMSKY-KORSAKOV` survives) and become `<` only in `mrzField()`. A mononym has no `<<`: `MADONNA`.
  Example: `VAN<DER<BERG<<JAN<PIETER`.
- **Exceptions per family, not per class:** `InvalidDocumentNumberException`, `InvalidPersonNameException`,
  `AmbiguousJurisdictionException` (all in `Documents\Exception\`).
- **Doctrine** gets the separate bridge `msstc4php/vo-documents-doctrine` (owner's decision), namespace
  `MSSTC4PHP\ValueObject\Documents\Doctrine\`, requiring `msstc4php/vo-documents` and `msstc4php/vo-base-doctrine ^1.1`;
  one ORM and one ODM type per value object, named `vod_<cc>_<snake_name>` (e.g. `vod_ru_snils`), registered by a
  `TypeRegistrar` like the one in `vo-base-doctrine`. Every later plan adds the types of its classes.
- **Sub-national class naming:** `Document\<CC>\DriverLicence\<Abbrev>DriverLicenceNumber`, e.g.
  `Document\AU\DriverLicence\NswDriverLicenceNumber`, all implementing `Document\<CC>\DriverLicenceNumber`.

## Review Focus

1. Input with separators and odd spacing (`112 233 445 95`, `112-233-445-95`, tabs, NBSP) must normalise, not fail;
   letters where digits belong must fail with no input echoed.
2. A Latin override that differs from the derived form only by case or spacing (`ivanov<<ivan`) must be normalised and
   accepted when it is a valid variant, not refused.
3. A Russian name typed with Latin look-alike letters (Latin `a`, `o`, `e`, `c` inside Cyrillic) must be refused:
   it is the most common real-world corruption of Cyrillic names.
4. Decomposed Unicode input (`й` as `и` + U+0306, `ё` as `е` + U+0308) must be accepted and stored composed.
5. An AU licence number that fits several jurisdictions must never come back as one silently; the exception lists
   them, ordered as the enum declares them.

---

### Task 1: Scaffold the package

**Files:**
- Create: `vo-documents/` with the file set of `vo-base-spiral` (composer.json, composer-ci.json, tool configs,
  Makefile, `.github/workflows/checks.yml`, `.gitattributes`, `.editorconfig`, `.gitignore`, LICENSE, SECURITY.md,
  README.md, CHANGELOG.md, CLAUDE.md, `deptrac.yaml`, `phpstan-baseline.neon`, `.claude/docs/{README,architecture,
  conventions,integrations,known-issues}.md`), `src/`, `tests/Unit/`.
- Test: `tests/Unit/PackageSmokeTest.php`

- [ ] **Step 1:** Copy the scaffold from `vo-base-spiral`; set name, description ("Value objects for identity
  documents and personal names of the world's largest economies, with ICAO 9303 Latin names."), keywords, `require`
  per Global Constraints, autoload `MSSTC4PHP\ValueObject\Documents\` → `src/`, tests → `tests/`. Drop Spiral-only CI
  jobs (`without-validator`) and require-dev entries. `deptrac.yaml`: forbid every framework namespace, as in the core.
- [ ] **Step 2:** `git init -b main`, remote `git@github.com:msstc4php/vo-documents.git` (the owner creates it later).
- [ ] **Step 3:** Smoke test `test_core_mapper_treats_a_package_string_value_object_as_scalar_backed` using a tiny
  fixture class extending `AbstractStringValueObject`: `(new ValueObjectMapper())->isScalarBacked(Fixture::class)` is
  `true`.
- [ ] **Step 4:** Run `COMPOSER=composer-ci.json composer update --ignore-platform-req=php`, `make check`, `make test`,
  and `cmp` of every tool config against the templates. Expected: all clean.
- [ ] **Step 5:** Commit `chore: scaffold vo-documents`.

### Task 2: Shared contracts, exceptions and helpers

**Files:**
- Create: `src/Contract/{Maskable,HasBirthDate,HasSex,Subdivision,IssuedBySubdivision}.php`, `src/Sex.php`,
  `src/Exception/{InvalidDocumentNumberException,InvalidPersonNameException,AmbiguousJurisdictionException}.php`,
  `src/Internal/{Checksum,Mask,Digits,UnicodeText}.php`
- Test: `tests/Unit/Internal/{ChecksumTest,MaskTest,DigitsTest,UnicodeTextTest}.php`, `tests/Unit/Exception/AmbiguousJurisdictionExceptionTest.php`

**Interfaces (Produces):**
- `interface Maskable { public function masked(): non-empty-string; }`
- `interface HasBirthDate { public function birthDate(): DateTimeImmutable; }` (00:00 UTC)
- `interface HasSex { public function sex(): Sex; }`; `enum Sex: string { case Female = 'female'; case Male = 'male'; }`
- `interface Subdivision extends BackedEnum { public function isoCode(): non-empty-string; }` (ISO 3166-2, e.g. `AU-NSW`; enums implementing it are string-backed with the ISO code as value)
- `interface IssuedBySubdivision { public function jurisdiction(): Subdivision; }`
- `AmbiguousJurisdictionException extends InvalidValueObjectException`:
  `__construct(non-empty-string $document, non-empty-list<Subdivision> $jurisdictions)`,
  `jurisdictions(): non-empty-list<Subdivision>`; message `"%s number fits several jurisdictions: %s."` with ISO codes only.
- `final class Checksum` (static, `@internal`): `luhn(string $digits): bool`,
  `weightedMod(string $digits, list<int> $weights, int $modulus): int` (sum of digit × weight mod modulus),
  `mod97(string $digits): int`, `iso7064Mod11_2(string $digits): string` (returns the check character `0-9`/`X`).
- `final class Digits` (`@internal`): `strip(string $input, string $separators = " -\t\u{00A0}"): string`.
- `final class Mask` (`@internal`): `keepLastDigits(non-empty-string $formatted, int<0, max> $visible): non-empty-string`
  replaces every digit except the last `$visible` digits with `*` and keeps every non-digit character in place.
- `final class UnicodeText` (`@internal`): `normalizeName(string $input): string` (NFC + trim + collapse whitespace,
  including NBSP), `isInScript(string $text, non-empty-string $letterClass): bool` (regex `^[…]+$` with `u`).

- [ ] **Step 1: tests.** `ChecksumTest`: `luhn('79927398713')` true / `'79927398710'` false;
  `weightedMod('112233445', [9, 8, 7, 6, 5, 4, 3, 2, 1], 101) === 95`;
  `iso7064Mod11_2('11010519491231002') === 'X'` (example from GB 11643-1999). `DigitsTest`: `strip("112 233-445\u{00A0}95") === '11223344595'`.
  `MaskTest`: `keepLastDigits('112-233-445 95', 4) === '***-***-*45 95'`, `keepLastDigits('1234', 0) === '****'`.
  `UnicodeTextTest`: `normalizeName("  Андреи\u{0306}\u{00A0}  Петров ") === 'Андрей Петров'` (composed `й`);
  `isInScript('Иван', '\p{Cyrillic}')` true, `isInScript("Ив\u{0061}н", '\p{Cyrillic}')` (Latin `a`) false.
  `AmbiguousJurisdictionExceptionTest`: message lists ISO codes only and never the number.
- [ ] **Step 2:** run, see failures. **Step 3:** implement. **Step 4:** `make test` green, `make check` clean.
- [ ] **Step 5:** Commit `feat: shared contracts, exceptions and checksum/mask/unicode helpers`.

### Task 3: `Icao\PersonName` and the ICAO transliterator

**Files:**
- Create: `src/Icao/PersonName.php`, `src/Icao/Transliterator.php`, `src/Icao/Table/{LatinMultinational,Cyrillic}.php`,
  `docs/sources/ICAO.md`
- Test: `tests/Unit/Icao/{PersonNameTest,TransliteratorTest}.php`

**Interfaces (Produces):**
- `final class PersonName extends AbstractStringValueObject` (canonical form per "Decisions"):
  `static fromParts(string $primary, list<string> $secondary = []): self` (accepts spaces/lower case, upper-cases,
  maps spaces to `<`), `primaryIdentifier(): non-empty-string` (spaces restored), `secondaryIdentifiers(): list<non-empty-string>`,
  `visualZone(): non-empty-string` (`RIMSKY-KORSAKOV, NIKOLAI ANDREEVICH` style: primary, comma, secondaries),
  `mrzField(int<2, max> $width = 39): non-empty-string` (hyphens and spaces → `<`, padded with `<`, truncated by the
  Doc 9303 Part 3 truncation rule).
- `final class Transliterator`: `toLatin(string $nationalPart): non-empty-string` (default spelling, upper case),
  `variants(string $nationalPart): non-empty-list<non-empty-string>` (every spelling the tables allow; the default
  first), throws `InvalidPersonNameException` for a character no table covers.

- [ ] **Step 1: research.** From ICAO Doc 9303 Part 3 (8th ed., 2021), section "Transliteration" (multinational Latin
  characters, Cyrillic) and the MRZ name truncation rules, write `docs/sources/ICAO.md`: the two tables verbatim, the
  characters with alternative spellings, the truncation steps, the PDF link. Tables in code are copied from that note.
- [ ] **Step 2: tests.** `PersonNameTest`: `fromParts('Rimsky-Korsakov', ['Nikolai', 'Andreevich'])` →
  `'RIMSKY-KORSAKOV<<NIKOLAI<ANDREEVICH'`; `from('MADONNA')->secondaryIdentifiers() === []`;
  `fromParts('van der Berg', ['Jan'])->value === 'VAN<DER<BERG<<JAN'`;
  `mrzField(20)` of a long name equals the truncation example in `ICAO.md`; refused: `'IVANOV<<<IVAN'`, `'IVÁNOV'`,
  `'IVANOV<<'`, `'-IVANOV'`, `''`, digits; refusal messages contain none of the input.
  `TransliteratorTest`: `toLatin('Щербаков') === 'SHCHERBAKOV'`, `toLatin('Объедков') === 'OBIEDKOV'`,
  `toLatin('Müller') === 'MUELLER'`, `variants('Müller') === ['MUELLER', 'MULLER']`, `toLatin('Ñúñez') === 'NUNEZ'`,
  `toLatin('Ł')` per table; an unsupported script (`'李'`) throws. Every expectation cross-checked with `ICAO.md`.
- [ ] **Steps 3-4:** run failing, implement, green + `make check`.
- [ ] **Step 5:** Commit `feat: ICAO 9303 person name and Latin/Cyrillic transliteration`.

### Task 4: Name framework and Russian names

**Files:**
- Create: `src/Name/AbstractNamePart.php`, `src/Name/PersonName.php` (interface), `src/Name/RU/{FamilyName,GivenName,Patronymic,PersonName,PersonNameCodec}.php`,
  `src/Codecs.php`, `docs/sources/RU.md` (names section)
- Test: `tests/Unit/Name/RU/{NamePartsTest,PersonNameTest,PersonNameCodecTest}.php`, `tests/Unit/CodecsTest.php`

**Interfaces (Produces):**
- `abstract class AbstractNamePart extends AbstractStringValueObject`; subclasses implement
  `abstract protected function letters(): non-empty-string` (a regex letter class, e.g. `\p{Cyrillic}`) and may override
  `protected function innerSeparators(): string` (default `"-' "`); `parse()` normalises via `UnicodeText::normalizeName`,
  requires every word to start and end with a letter and no two separators in a row; message
  `"Invalid %s (%d characters)."` with the part's label.
- `interface Name\PersonName extends ValueObject { public function latin(): Icao\PersonName; public function full(): non-empty-string; }`
- `final readonly class Name\RU\PersonName implements Name\PersonName`:
  `__construct(FamilyName $family, GivenName $given, ?Patronymic $patronymic = null, ?Icao\PersonName $latin = null)`;
  a given `$latin` must equal one of the combinations of `Transliterator::variants()` over the parts (family →
  primary; given + patronymic → secondary), else `InvalidPersonNameException`; `latin()` returns it or the derived one;
  `full()` → `Иванов Иван Иванович`, `short()` → `Иванов И. И.`, `initialsFirst()` → `И. И. Иванов`;
  `equals()` compares all parts and the stored override.
- `final class PersonNameCodec implements PayloadCodec<Name\RU\PersonName>`: payload
  `array{family: string, given: string, patronymic: string|null, latin: string|null}`; `latin` is `null` unless an
  override was given (the derived form is never stored).
- `final class Codecs { /** @return list<PayloadCodec<ValueObject>> */ public static function all(): array; }` — every
  codec of the package, for `new ValueObjectMapper(Codecs::all())`.

- [ ] **Step 1: research.** Law/standard for Russian name spelling in civil documents (letters allowed, hyphen in double
  surnames, patronymic "при наличии") into `docs/sources/RU.md` with links.
- [ ] **Step 2: tests.** Parts: `GivenName::from('Иван')`; `FamilyName::from('Римский-Корсаков')`; `FamilyName::from("д'Артаньян")`
  if the source allows the apostrophe, else refused; refused: `'Ivan'`, `'Ивaн'` (Latin a), `'Иван2'`, `'-Иван'`,
  `'Иван--Петров'`; NFC: `GivenName::from("Андреи\u{0306}")->value === 'Андрей'`. Composite:
  derived `latin()->value === 'IVANOV<<IVAN<IVANOVICH'`; no patronymic → `'IVANOV<<IVAN'`; override
  `'ivanov<<ivan<ivanovich'` accepted (normalised); override `'IWANOW<<IWAN'` refused; `short()` without patronymic →
  `Иванов И.`. Codec: encode/decode round trip with and without override; `decode(['family' => 1])` throws
  `InvalidPayloadException`; `ValueObjectMapper(Codecs::all())->fromUntrustedPayload(PersonName::class, …)` works.
- [ ] **Steps 3-4:** failing, implement, green + `make check`.
- [ ] **Step 5:** Commit `feat: name framework and Russian person names`.

### Task 5: Russian documents

**Files:**
- Create: `src/Document/RU/{Snils,Inn,InternalPassportNumber,PassportIssuingUnitCode,InternationalPassportNumber,DriverLicenceNumber,DriverLicenceCategory,OmsPolicyNumber,Region}.php`,
  `docs/sources/RU.md` (documents section)
- Test: `tests/Unit/Document/RU/*Test.php`

**Interfaces (Produces):** every number class `final`, extends `AbstractStringValueObject`, implements `Maskable`,
stores digits only, offers `formatted(): non-empty-string` (conventional display form), throws
`InvalidDocumentNumberException`.
- `Snils`: 11 digits; checksum by the Pension Fund rule (weights 9..1 over the first 9 digits; sum < 100 → sum,
  100 or 101 → 00, > 101 → sum mod 101 with 100 → 00); numbers up to `001-001-998` are exempt from the checksum;
  `formatted()` `112-233-445 95`; `masked()` is `Mask::keepLastDigits(formatted(), 4)` → `***-***-*45 95`, unless
  `RU.md` records an official masking rule. The same default (last 4 digits of `formatted()`) applies to every
  document in this plan.
- `Inn`: the individual's 12-digit INN (weights `7,2,4,10,3,5,9,4,6,8` and `3,7,2,4,10,3,5,9,4,6,8`, mod 11 mod 10);
  `taxOfficeCode(): non-empty-string` (first 4 digits).
- `InternalPassportNumber`: series (4 digits) + number (6 digits); `series()`, `number()`, `formatted()` `45 08 123456`.
- `PassportIssuingUnitCode`: `NNN-NNN`.
- `InternationalPassportNumber`: 9 digits, `formatted()` `75 1234567`.
- `DriverLicenceNumber`: 10 characters per the MVD format (digits; older series with Cyrillic letters only if the
  source still recognises them), `formatted()` `99 99 123456`.
- `DriverLicenceCategory`: string-backed enum of the categories the source lists (`A`, `A1`, `B`, `BE`, `B1`, `C`,
  `CE`, `C1`, `C1E`, `D`, `DE`, `D1`, `D1E`, `M`, `Tm`, `Tb`).
- `OmsPolicyNumber`: the 16-digit single-format policy number with its control digit per the FFOMS order.
- `Region implements Subdivision` only if a researched field encodes a region with a published mapping to ISO 3166-2:RU;
  otherwise not created (record why in `RU.md`).

- [ ] **Step 1: research** every field above into `RU.md`: official source link, exact format, checksum, mask rule,
  official example numbers. Any value in this task's text that the source contradicts loses to the source; record it.
- [ ] **Step 2: tests** per class: official example valid; same with separators and NBSP valid; one digit changed →
  invalid checksum; wrong length; letters; mask and `formatted()` exact strings from `RU.md`; message never contains
  the input (assert `assertStringNotContainsString` for every digit run of length >= 3 of the input).
- [ ] **Steps 3-4:** failing, implement, green + `make check` + `make infection` >= 75%.
- [ ] **Step 5:** Commit `feat: Russian identity document numbers`.

### Task 6: Australia — national documents, names, states and territories

**Files:**
- Create: `src/Document/AU/{Jurisdiction,DriverLicenceNumber,DriverLicenceNumberFactory,TaxFileNumber,MedicareCardNumber}.php`,
  `src/Document/AU/DriverLicence/{Act,Nsw,Nt,Qld,Sa,Tas,Vic,Wa}DriverLicenceNumber.php`,
  `src/Name/AU/{FamilyName,GivenName,PersonName,PersonNameCodec}.php`, `docs/sources/AU.md`
- Modify: `src/Codecs.php` (add the AU codec)
- Test: `tests/Unit/Document/AU/**`, `tests/Unit/Name/AU/**`

**Interfaces (Produces)** — the pattern plans 2-4 copy for every sub-national document:
- `enum Jurisdiction: string implements Subdivision` with cases `Act = 'AU-ACT'`, `Nsw = 'AU-NSW'`, `Nt = 'AU-NT'`,
  `Qld = 'AU-QLD'`, `Sa = 'AU-SA'`, `Tas = 'AU-TAS'`, `Vic = 'AU-VIC'`, `Wa = 'AU-WA'`.
- `interface DriverLicenceNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision` with
  `jurisdiction(): Jurisdiction`.
- One `final` class per jurisdiction implementing it.
- `final class DriverLicenceNumberFactory`:
  `static fromNumber(string $number): DriverLicenceNumber` (exactly one match, else `AmbiguousJurisdictionException`
  with the matches in enum order, or `InvalidDocumentNumberException` when none),
  `static fromNumberIn(string $number, Jurisdiction $jurisdiction): DriverLicenceNumber`,
  `static jurisdictionsMatching(string $number): list<Jurisdiction>`.
- `TaxFileNumber` (9 digits, ATO weighted checksum), `MedicareCardNumber` (Services Australia check digit) — values
  from `AU.md`.
- `Name\AU\PersonName`: Latin script, `__construct(FamilyName $family, GivenName $given, ?Icao\PersonName $latin = null)`
  (given names may hold several words), derived Latin through the multinational table.

- [ ] **Step 1: research** `AU.md`: each jurisdiction's licence number format from the issuing authority (and the
  card number where it is separate), TFN and Medicare checksums from ATO / Services Australia; mark a jurisdiction
  with no published format as not covered and do not create its class.
- [ ] **Step 2: tests:** each jurisdiction's official example; `jurisdictionsMatching()` of a number valid in several
  jurisdictions returns them in enum order; `fromNumber()` of that number throws `AmbiguousJurisdictionException`
  whose `jurisdictions()` equals that list and whose message has no digits of the number; `fromNumberIn()` returns
  the concrete class; a number valid nowhere throws `InvalidDocumentNumberException`; `jurisdiction()` of each class;
  TFN/Medicare checksum positives and negatives; AU names: `GivenName::from('Zoë')` latin `'ZOE'`, Cyrillic refused.
- [ ] **Steps 3-4:** failing, implement, green + `make check` + `make infection`.
- [ ] **Step 5:** Commit `feat: Australian documents, state and territory licences, names`.

### Task 7: Doctrine bridge `vo-documents-doctrine`

**Files:**
- Create: `vo-documents-doctrine/` scaffolded like `vo-base-doctrine` (same tool configs, CI with a MySQL/SQLite and
  MongoDB service exactly as there), `src/Orm/{TypeRegistrar,…Type}.php`, `src/Odm/{TypeRegistrar,…Type}.php`
- Test: `tests/Unit/Orm/*`, `tests/Unit/Odm/*`, `tests/Integration/*` (round trip through a real connection, as in
  `vo-base-doctrine`)

**Interfaces (Produces):**
- ORM: one `final` type per string value object of `vo-documents` extending
  `MSSTC4PHP\ValueObject\Doctrine\Orm\AbstractStringVoType` (column length = the longest canonical form), and one per
  composite name extending `AbstractJsonVoType` with its codec; ODM the same over the ODM abstract types.
- `Orm\TypeRegistrar::register(): void` and `Odm\TypeRegistrar::register(): void`, idempotent, mirroring
  `vo-base-doctrine`; `TypeRegistrar::types(): array<non-empty-string, class-string<Type>>` (name ⇒ class) so a Symfony
  `doctrine.yaml` can be written from it.
- Enums (`DriverLicenceCategory`, `Jurisdiction`) get no type: Doctrine maps backed enums natively (`enumType`).

- [ ] **Step 1:** scaffold from `vo-base-doctrine`, `git init -b main`, remote `git@github.com:msstc4php/vo-documents-doctrine.git`;
  `composer.json` uses VCS repositories for `vo-base`, `vo-base-doctrine` and a `path` repository to `../vo-documents`
  in `composer-local.json` (CI checks out `vo-documents` next to the bridge, as other bridges do for `vo-base`).
- [ ] **Step 2: tests:** a test asserting that every non-abstract string value object and composite in
  `vo-documents/src` has exactly one ORM and one ODM type (reflection over the class map, so a class added later
  without a type fails); per type: round trip, `null`, invalid stored value throws; integration round trip of
  `Snils` and `Name\RU\PersonName` (with and without a Latin override).
- [ ] **Steps 3-4:** failing, implement, `make check`, `make test`, `make infection` green.
- [ ] **Step 5:** commit `feat: Doctrine ORM and ODM types for vo-documents`.

### Task 8: Documentation and core cross-links

**Files:**
- Modify: `vo-documents/README.md`, `CHANGELOG.md` (`## [Unreleased]`), `.claude/docs/*`; the same three in
  `vo-documents-doctrine`;
  `vo-base/README.md`, `vo-base/CLAUDE.md`, `vo-base/.claude/docs/integrations.md` (list the new package).

- [ ] **Step 1:** README: install; table of every class with its source; masking and the full-value `__toString`;
  ICAO name and override rule; the jurisdiction factory and its ambiguity behaviour; per-bridge notes: works as is for
  validation, request and route in every bridge and for storage in Laravel, Yii2, CI4 and Cycle; Doctrine through
  `vo-documents-doctrine` (show `TypeRegistrar` and a Symfony `doctrine.yaml`); composite names need `Codecs::all()` passed to the mapper
  (show the container binding per bridge); "not covered" list with reasons (so far: what `RU.md`/`AU.md` record).
- [ ] **Step 2:** `.claude/docs`: architecture (namespaces, patterns), conventions (research note → class → tests,
  messages without input), known-issues (anything discovered).
- [ ] **Step 3:** `make check`, `make test`; commit in each repo: `docs: vo-documents foundation`.
