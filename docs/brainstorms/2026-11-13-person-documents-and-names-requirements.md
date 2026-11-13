# Person documents and names: value objects for the top 100 economies

Date: 2026-11-13 (UTC). Status: draft for the owner's review; builder check done, its questions settled.

## Why

The library exists because its author, like most PHP developers, kept rewriting value objects for everyday things.
Two gaps remain that every project with real people in it hits:

- **Document fields.** Every country has a handful (rarely more than ten) of official documents: national ID, internal
  and international passport, driving licence, social/health insurance number, taxpayer number. What was missing is a
  ready value object per field of those documents for the major economies, one that does what a hand-written regex
  never does: checksum, the data encoded in the number, a safe form for logs.
- **Names.** Every country has its own rules for writing a person's name, starting with its alphabet (a Russian name
  may not contain Latin letters). Input is the national form; the same name must be available in Latin as ICAO 9303
  writes it in a passport. The ICAO Latin name is also a value object of its own, not tied to any country.

Success: a developer types a property as `PL\Pesel` or `RU\PersonName`, gets validation, the encoded data, a masked
form and an ICAO Latin name, and it works through every existing bridge (validation, request, route, storage) without
writing anything.

## Scope and waves (owner's decisions)

- **Wave 1:** the top 20 economies by nominal GDP for 2025 in the IMF World Economic Outlook, October 2026 edition:
  US, CN, DE, JP, GB, IN, FR, RU, IT, CA, BR, ES, KR, AU, MX, TR, ID, NL, SA, CH.
  *(Source used to fix the list: statisticstimes.com projected ranking, which cites that WEO edition; places 21-22 are
  PL and TW, so the edge is clear.)*
- **Later waves:** up to the top 100 by the same ranking, in groups, each a minor release under this same design. The
  country list of each later wave is fixed from the WEO edition current when that wave starts.

## Documents

Per country, every official document whose fields have a published format: national ID / civil registration number,
internal and international passport, driving licence, social or health insurance number, taxpayer number, and
document fields that have a format of their own (e.g. the Russian passport's series and issuing-unit code, driving
licence categories). *(Designer's call: a field with no published official format is left out and listed in the
package README as not covered, with the reason; no format reverse-engineered from samples.)*

**Documents issued by states and provinces are in wave 1** (owner's decision): driving licences and ID cards of the
US states (and DC), the Canadian provinces and territories and the Australian states and territories, and any other
wave-1 country where a field is issued per subdivision.

- One value object per jurisdiction, all implementing one interface per country and document (e.g. a US driving
  licence interface). The value object tells **which jurisdiction issued it** through its own method (owner's
  decision), as an ISO 3166-2 code (`US-CA`). *(designer's call: the ISO code, as a package enum per country)*
- **A factory per country and document takes a bare number and returns the concrete value object** (owner's
  decision). *(Designer's call, forced by the data: formats overlap, e.g. several states issue plain 9-digit numbers,
  so a number alone often fits more than one jurisdiction. The factory therefore returns the value object when
  exactly one jurisdiction matches; when several match it throws a dedicated "ambiguous" exception carrying the
  matching jurisdictions, and it offers a call taking the jurisdiction as well, plus one that lists every matching
  jurisdiction for a number. It never picks one silently.)*
- *(Designer's call: for US formats the AAMVA DL/ID Card Design Standard and the jurisdiction's own publications are
  the sources; a jurisdiction with no published format goes on the "not covered" list.)*

Each value object (owner's decisions):

- **Normalises** input to one canonical form (spaces, hyphens, case) and can give the conventional display form back
  (`112-233-445 95` for a SNILS).
- **Checks the checksum** wherever the country defines one; otherwise the strict format. Encoded dates must be real
  dates (no 31 February in a PESEL). *(designer's call)*
- **Exposes the encoded data, typed**: birth date and sex from a PESEL, region where the number encodes it.
  *(Designer's call: a birth date is a `DateTimeImmutable` at 00:00 UTC; sex is a package enum; a region is an ISO
  3166-2 code where one exists.)*
- **`masked()`** gives a form safe for logs, with a per-document rule for what stays visible, documented on the class.
- **`__toString()` / `jsonSerialize()` give the full value**, like every other value object in the library. Masking
  happens only on an explicit `masked()` call. *(owner's decision)*
- Every format and checksum is checked against the country's official source, cited on the class (`@link`). An
  agency page, an official gazette, or a standard cited by number (paid ones included) counts; blogs and generator
  sites do not. *(designer's call)* Test
  numbers come from official examples where they exist. *(designer's call)*

## Names

- **Input is the national form**, validated by the country's rules: its alphabet first, then the characters allowed
  inside a part (hyphen in double surnames, apostrophe where usual, spaces in compound parts) and the structure of a
  full name (patronymic in RU; two surnames in ES and the Spanish-speaking world; family name first in CN, JP, KR).
  Unicode is normalised to NFC and surrounding/double spaces are collapsed. *(Designer's call: letter case is kept as
  given; automatic "proper case" breaks names like McDonald or de la Cruz.)*
- **Shape** *(designer's call)*: a value object per part (`RU\GivenName`, `RU\FamilyName`, `RU\Patronymic`) and a
  composite per country (`RU\PersonName`) that knows the order of parts and the usual display forms ("Иванов Иван
  Иванович", "Иванов И. И.").
- **Alphabet of a country** *(designer's call)*: the letters of all of the country's official languages in the
  script it writes names in on its civil documents: Latin with every national language's letters for CH, ES and the
  other Latin-script countries, Cyrillic for RU, Han for CN. Minority scripts (Uyghur in CN, Tamil, Bengali and the
  other Indian scripts besides Devanagari) are not in wave 1 and go on the "not covered" list.
- **Parts that may be missing** *(designer's call)*: a single-part name (mononym) is valid where the country allows
  it, and in `Icao\PersonName` (primary identifier only). The Russian patronymic is optional ("при наличии"), and
  so is the Spanish second surname.
- **Latin / ICAO:** every national name gives itself in Latin, transliterated by ICAO Doc 9303 Part 3, as an
  `Icao\PersonName` value object. *(owner's decision)*
- **Own Latin spelling** (owner's decision): for the algorithmically transliterated countries the Latin form is
  derived by default but may also be given explicitly (a Russian keeping the spelling of an earlier passport,
  MUELLER or MULLER). A given form must be a valid `Icao\PersonName`. *(Designer's call, revised 2026-11-13 after
  the final review: the policy is per country. Latin-script countries require one of the spellings the ICAO table
  allows for that national name; Russia accepts any valid ICAO name, because MVD Orders 996/186 let the passport
  keep the spelling "так, как это указано в подтверждающем документе".)*
- **`Icao\PersonName`** is its own value object, not tied to a country (owner's decision): Latin A-Z only, space and
  hyphen inside a part, upper case, primary identifier (surname) and secondary identifiers (given names) as separate
  parts, and it can produce the MRZ name field (`IVANOV<<IVAN<IVANOVICH`) truncated to a given width by the ICAO rule.
  *(Designer's call: its canonical string is that MRZ-style form without truncation, so it is a plain string value
  object and works in every bridge with no codec.)*
- **Where the national form does not determine the passport spelling** (owner's decision): the name is built from the
  national form **plus a mandatory Latin form**, which is validated as an `Icao\PersonName`; for Japan, from the
  national form **plus the kana reading**, from which the Latin form is derived algorithmically (passport Hepburn).
  This covers CN (polyphonic characters), JP (kanji readings), SA (Arabic written without vowels).
  *(Designer's call, by the owner's rule: KR joins this group, because Korean passports carry the holder's chosen
  spelling, LEE or KIM, not the Revised Romanization of the Hangul; IN accepts a Latin-script national form, and a
  Devanagari one only with its Latin form.)* Cyrillic and Latin with diacritics (every other wave-1 country) are
  transliterated algorithmically by the ICAO tables.

## Integration

- New package (owner's decision: not part of the `vo-base-*` family): `msstc4php/vo-documents`, namespace `MSSTC4PHP\ValueObject\Documents\`, with
  `Document\<CC>\…`, `Name\<CC>\…` and `Icao\…`. Depends on `vo-base ^1.1`, `ext-mbstring` and
  `symfony/polyfill-intl-normalizer` (NFC without requiring `ext-intl`) *(designer's call)*; same scaffolding and gates as every
  package (`vo-base-doctrine` as the model). The owner creates the GitHub repository.
- `ValueObjectMapper` already recognises any `StringConvertible` value object from any package, so every document
  number and name part works in all bridges with no core release. Composite names need a payload codec: the package
  ships the codecs and one entry point listing them, and the README shows the registration per bridge.
- **Doctrine gets its own bridge** (owner's decision): `msstc4php/vo-documents-doctrine`, ORM and ODM types for every
  value object of `vo-documents`, built on the abstract types and `TypeRegistrar` of `vo-base-doctrine`. Doctrine maps
  per class, unlike the other bridges, which work as they are. The bridge grows with each wave and is released with
  the same version number as `vo-documents`.

## Goals

- Wave 1: documents (national and sub-national) and names of the 20 countries above, plus `Icao\PersonName`, released as `vo-documents` 0.1.0 and `vo-documents-doctrine` 0.1.0.
- Every class cites its official source; every checksum and every transliteration table is tested against official
  examples.
- Same gates as every package: PHPStan max + `phpstan-ci.neon`, cs-fixer, rector, tests, Infection >= 75% covered MSI,
  lowest-deps job, templates `cmp`, `/acc:code-review`, `README`, `CHANGELOG`, `.claude/docs`.

## Non-goals

- Parsing a whole MRZ or a whole document (that belongs to direction 3, identifiers and machine-readable codes).
- National Latin standards other than ICAO (e.g. GOST 7.79) in wave 1.
- Automatic transliteration where the spelling is a tradition, not a rule (CN, JP kanji, SA, KR).
- Checking a number against a registry (whether a document exists or is valid); only its form.
- Packagist publication (the owner does it in the web UI).

## Anti-goals (failures even if it "works")

- An exception message containing the input value: a rejected number or name is often a real person's data with a typo
  and ends up in logs. Messages give the length or the reason only.
- A format or checksum taken from a blog or a generator site instead of the official source.
- A Latin name produced by guessing where the passport spelling is a personal choice.
- A national name accepting a script the country does not use for that form.

## Left to the builder

Class names, the full list of fields per country within the rule above, file layout, exact masking rule per document,
test structure, message wording within the anti-goals, the order countries are built in within the wave.

## Release

`vo-documents` 0.1.0 after wave 1; each later wave a minor release. Push and tag only after explicit confirmation,
tag only after green CI.
