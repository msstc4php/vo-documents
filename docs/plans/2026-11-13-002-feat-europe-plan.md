# vo-documents, plan 2 of 4: Europe (DE, FR, IT, ES, NL, CH, GB, TR)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Names and identity documents of the eight European wave-1 countries, plus their Doctrine types, on the
patterns plan 1 established.

**Architecture:** Every country is one task: research note → name classes → document classes → tests. Latin-script
names share one base extracted from the Australian implementation. Documents reuse `Internal\NumberFormat`,
`Internal\Digits`, `Internal\Checksum`, `Internal\Mask`, `SubdivisionResolver` (if a country issues a document per
subdivision) and the contracts `Maskable`, `HasBirthDate`, `HasSex`, `IssuedBySubdivision`.

**Tech Stack:** as plan 1 (PHP 8.4, `msstc4php/vo-base ^1.1.1`, PHPUnit, PHPStan max, Infection).

**Spec:** `docs/brainstorms/2026-11-13-person-documents-and-names-requirements.md`; plan 1:
`docs/plans/2026-11-13-001-feat-foundation-plan.md` (its Global Constraints and Decisions apply unchanged).

## Global Constraints (in addition to plan 1's)

- **Owner's rule (2026-11-13): a checksum is verified only when its algorithm is quoted from an official source**
  (act, gazette, agency page, or a standard cited by number). Otherwise the class checks the format only and says
  "format only; the check digit is not verified because no official algorithm is published". The same applies to
  any other structural rule (e.g. an allowed first digit).
- Encoded data (birth date, sex, region) is exposed only when the official source defines the encoding.
- Regions are ISO 3166-2 codes through a `Subdivision` enum only when the source's codes map to ISO 3166-2 one to one;
  otherwise a plainly named string accessor (e.g. `birthPlaceCode()`), never a guessed mapping.
- Latin-script names use the `TransliterationVariant` override policy (one of the ICAO table's spellings), unless the
  country's own law lets a passport keep another spelling — then `AnyIcaoSpelling`, with the quote in the source note.
- Masks keep the last 4 characters unless the number is 8 characters or shorter (then 3) or the source publishes a
  masking rule.
- Every new value object gets one ORM and one ODM type in `vo-documents-doctrine` (the completeness test enforces it).

## Decisions this plan makes

- **Latin name base:** `Name\Latin\AbstractLatinNamePart` (letters `\p{Latin}`, separators `-' `) and an internal
  `Name\Latin\LatinPersonNameBuilder` that derives the ICAO form and applies the override policy for a list of
  family parts and given parts. `Name\AU\*` is refactored onto them in Task 1; its public API stays the same.
- **Spanish names** have `FamilyName $first`, `?FamilyName $second` (second surname optional per the spec); the ICAO
  primary identifier is both surnames joined by a space.
- **Dutch names**: prefixes (tussenvoegsels, `van der`) are part of the family name value; no separate class.
- **Classes per country** follow `Document\<CC>\<Name>`; the exact document list per country is the research step's
  output, from this candidate list (a candidate with no published format goes to "not covered"):

| CC | Candidates |
|---|---|
| DE | Personalausweis number, Reisepass number, Steuer-ID, Rentenversicherungsnummer (birth date, sex), Führerschein number |
| FR | NIR (sex, birth year/month, department), CNI number, passport number, numéro fiscal (SPI), permis de conduire number |
| IT | Codice fiscale (birth date, sex, birth-place code), CIE number, passport number, patente number |
| ES | DNI, NIE, número de afiliación a la Seguridad Social, passport number (the driving licence number is the DNI) |
| NL | BSN, passport / ID-card document number, rijbewijsnummer |
| CH | AHV-Nummer, passport / ID-card number, Führerausweis number |
| GB | National Insurance number, passport number, DVLA driving licence number (birth date, sex), DVA (Northern Ireland) licence number, NHS number |
| TR | T.C. Kimlik No, passport number, sürücü belgesi number |

## Review Focus

1. A number with the right shape but an unpublished check digit must be accepted (owner's rule), never refused.
2. Encoded birth dates must reject impossible dates (30 February) and handle century rules exactly as the source says.
3. Codice fiscale omocodia (letters replacing digits) must be handled exactly as the official decree says.
4. Turkish dotted/dotless I (`İ`, `ı`) must normalise and transliterate per table 6.A, never through locale-dependent
   upper-casing.
5. Spanish names with one surname, and Dutch prefixes in lower case, must round-trip through the codec unchanged.

---

### Task 1: Latin name base, AU refactor

**Files:** Create `src/Name/Latin/{AbstractLatinNamePart,LatinPersonNameBuilder}.php`; modify `src/Name/AU/*`.
Test: `tests/Unit/Name/Latin/*`; existing AU name tests must pass unchanged.

- [ ] Extract the base and the builder (`derive(list<string> $family, list<string> $given): IcaoPersonName`,
  `resolve(?IcaoPersonName $override, LatinOverridePolicy $policy)`), refactor AU onto them, keep AU behaviour and API.
- [ ] `make check`, `make test`, `make infection`; commit `refactor: shared Latin-script name base`.

### Tasks 2-9: one per country — DE, FR, IT, ES, NL, CH, GB, TR (same steps each)

**Files:** `docs/sources/<CC>.md`, `src/Name/<CC>/*`, `src/Document/<CC>/*`, `src/Codecs.php`, tests mirroring them.

- [ ] **Research** into `docs/sources/<CC>.md`: for every candidate, the official source (link, act number), exact
  format, checksum (with its official text, or "format only"), encoded data, mask rule, official example numbers; the
  rules for writing names (alphabet, separators, parts) and any rule on the passport's Latin spelling.
- [ ] **Names:** parts on `AbstractLatinNamePart`, a composite `Name\<CC>\PersonName` and its codec registered in
  `Codecs::all()`. Tests: national form, NFC, refused scripts, derived Latin, override accepted/refused per policy,
  codec round trip.
- [ ] **Documents:** one class per covered candidate. Tests: official examples, separators/NBSP, wrong length,
  wrong characters, checksum failures (only where verified), encoded data, `formatted()`, `masked()`, no input echoed.
- [ ] `make check`, `make test`, `make infection`; commit `feat: <country> names and documents`.

### Task 10: Doctrine types

- [ ] In `vo-documents-doctrine`: one ORM and one ODM type per new value object (`vod_<cc>_<snake_name>`), samples,
  registrar entries; the completeness test passes. `make check`, `make test`, `make infection`; commit
  `feat: Doctrine types for the European documents`.

### Task 11: Documentation

- [ ] `README.md` class table and "not covered" list, `CHANGELOG.md` `[Unreleased]`, `.claude/docs/*` in both repos;
  commit `docs: European documents and names`.
