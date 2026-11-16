# vo-documents, plan 3 of 4: Americas (US, CA, MX, BR)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Names and identity documents of the four American wave-1 countries, national and sub-national (US states
and DC, Canadian provinces and territories, Mexican and Brazilian states where a format is published), plus their
Doctrine types.

**Architecture:** As plan 2: one research note per country, then names, then documents. Sub-national documents follow
the Australian pattern of plan 1 exactly: a `Jurisdiction` enum per country implementing `Contract\Subdivision`, one
interface per document extending `IssuedBySubdivision`, one final class per jurisdiction on an abstract base, and a
factory on `Internal\SubdivisionResolver` (`fromNumber`, `fromNumberIn`, `jurisdictionsMatching`) that throws
`AmbiguousJurisdictionException` and never picks silently.

**Tech Stack:** as plans 1 and 2 (PHP 8.4, `msstc4php/vo-base ^1.1.1`, `lib-pcre >=10.40`, PHPUnit, PHPStan max,
Infection).

**Spec:** `docs/brainstorms/2026-11-13-person-documents-and-names-requirements.md`; plans 1 and 2
(`docs/plans/2026-11-13-001-feat-foundation-plan.md`, `docs/plans/2026-11-13-002-feat-europe-plan.md`): their Global
Constraints and Decisions apply unchanged.

## Global Constraints (in addition to plans 1 and 2)

- Owner's rule: a checksum or any other structural rule (allowed ranges, forbidden prefixes, letter sets) is enforced
  only when it is quoted from an official source; otherwise "format only". A still-valid number is never refused
  because its check is unpublished.
- For US formats the sources are the AAMVA DL/ID Card Design Standard and the jurisdiction's own publications (spec);
  a jurisdiction with no published format goes on the README "not covered" list with the reason. The same rule for CA
  provinces, MX and BR states: the issuing authority's own publication, or a standard cited by number.
- Script classes use `\p{sc=...}` (Script property), never a bare `\p{Latin}` (see `.claude/docs/known-issues.md`).
- Regions: ISO 3166-2 through the country's `Jurisdiction` enum only where the source's codes map one to one;
  otherwise a plainly named string accessor (`birthStateCode()`, `fiscalRegion()`), never a guessed mapping.
- Masks: last 4 kept, 3 when the number is 8 characters or shorter, unless the source publishes a rule (SSA and IRS
  "last four" are such rules; quote them).
- Every new value object gets one ORM and one ODM type in `vo-documents-doctrine`.

## Decisions this plan makes

- **Namespaces:** `Document\US\DlId\<Abbr>DlIdNumber` implementing `Document\US\DlIdNumber` (AAMVA's "DL/ID"
  customer number: one class covers a jurisdiction's driving licence and ID card when its source says they share one
  numbering; otherwise the research note lists the ID card separately and it gets its own `<Abbr>IdCardNumber`).
  `Document\US\Jurisdiction`: 51 cases, the 50 states and `US-DC`; territories are out of scope (spec: "states (and
  DC)"). Canada: `Document\CA\DriverLicence\<Abbr>DriverLicenceNumber`, `Document\CA\HealthCard\<Abbr>HealthCardNumber`,
  `Document\CA\Jurisdiction` (13 cases). MX and BR: `Document\<CC>\<Doc>\<Abbr>...` only for the states the research
  finds published; their `Jurisdiction` enums list all states (32 MX entities, 27 BR units) regardless.
- **Factory exceptions** keep the AU message form: ISO codes only, never the number.
- **US names:** `Name\US\{GivenName, MiddleName, FamilyName, NameSuffix}` and `PersonName(FamilyName $family,
  GivenName $given, ?MiddleName $middle = null, ?NameSuffix $suffix = null)`. `NameSuffix` is a closed set taken from
  the AAMVA standard's name-suffix data element (quote the list). The ICAO form carries the suffix only if the
  research quotes a State Department or ICAO rule for it; otherwise it is left out, and the class doc says so.
- **Canadian names:** family + given on `AbstractFamilyGivenPersonName`; letters are English and French
  (`\p{sc=Latin}` unless a federal source lists a closed set).
- **Mexican names:** like the Spanish ones (`FamilyName $first`, `?FamilyName $second`, given names), following the
  CURP instructivo for which surnames exist.
- **Brazilian names:** `GivenName` and `FamilyName`, each a multi-word part (Portuguese names carry several surnames
  with no fixed count; particles `da`, `dos`, `de` stay inside the value in the case given).
- **Latin override policy:** `TransliterationVariant` for all four, unless the research quotes a rule letting the
  passport keep another spelling (then `AnyIcaoSpelling`).
- **`Sex` gains `Unspecified = 'unspecified'`** only if a plan-3 source encodes a third value (e.g. a passport or
  CURP "X"); the case is added in the task that first needs it, with the quote. Before 0.1.0 this is not a break.

## Candidates per country

The research step of each task turns this list into the covered set; a candidate with no published format goes to
"not covered".

| CC | National | Sub-national |
|---|---|---|
| US | SSN (SSA allocation rules), ITIN (IRS), passport book number, passport card number, Medicare Beneficiary Identifier (CMS) | DL/ID number for each of the 50 states and DC |
| CA | SIN (ESDC), passport number | driver's licence number and health card number for each of the 13 provinces and territories |
| MX | CURP (RENAPO: birth date, sex, birth-state code, check digit), RFC of individuals (SAT), INE clave de elector (birth date, sex, state), NSS (IMSS), passport number | licencia de conducir per state |
| BR | CPF (Receita Federal: check digits, fiscal region), CNH registration number (CONTRAN/SENATRAN), título de eleitor (TSE: check digits, state), PIS/PASEP/NIT, CNS (Ministério da Saúde), passport number | RG per state (the CIN uses the CPF) |

## Review Focus

1. US formats overlap heavily (plain 7-9 digit numbers in many states): `DlIdNumberFactory::fromNumber('123456789')`
   must throw `AmbiguousJurisdictionException` listing every matching state in enum order, never return one.
2. SSN: the SSA ranges (area 000, 666, 900-999; group 00; serial 0000) are refused only as SSA states them; an ITIN
   (9xx) is not an SSN and an SSN is not an ITIN.
3. CURP century: the 17th character (digit before 2000, letter from 2000 per RENAPO) decides the birth year; 30
   February is refused; the check digit is verified only if the instructivo's algorithm is quoted.
4. CPF and other mod-11 numbers: a number made of one repeated digit (`111.111.111-11`) passes the arithmetic; it is
   refused only if the Receita Federal says so, and the decision is quoted in the test.
5. Brazilian and Mexican names with particles in lower case (`da Silva`, `de la Cruz`) and a Mexican name with one
   surname must round-trip through the codec unchanged.

---

### Task 1: United States, names and federal documents

**Files:** `docs/sources/US.md` (names and federal part), `src/Name/US/*`, `src/Document/US/{SocialSecurityNumber,
Itin,PassportBookNumber,PassportCardNumber,MedicareBeneficiaryIdentifier}.php` (the covered subset),
`src/Codecs.php`, tests mirroring them.

- [ ] **Research** into `docs/sources/US.md`, as plan 2 Tasks 2-9: official source per candidate, format, rules
  with their quotes, encoded data, mask rule, official examples; name rules (alphabet, suffix list, passport spelling).
- [ ] **Names** as decided above; tests: national form, NFC, refused scripts, derived ICAO form with and without middle
  name, suffix handling as researched, override accepted/refused, codec round trip.
- [ ] **Documents**; tests: official examples, separators/NBSP, wrong length, wrong characters, every quoted range
  rule (Review Focus 2), `formatted()` (`123-45-6789` for an SSN), `masked()`, no input echoed.
- [ ] `make check`, `make test`, `make infection`; commit `feat: United States names and federal documents`.

### Task 2: United States DL/ID research

**Files:** `docs/sources/US.md` (section "DL/ID numbers").

- [ ] For each of the 51 jurisdictions: the source read (AAMVA standard section or the DMV's own page/regulation),
  the quoted format, whether the ID card shares it, and the verdict (covered / not covered with reason).
- [ ] The overlap table: for each normalised shape (e.g. "9 digits"), the jurisdictions it fits, in enum order. This
  table is the factory's test oracle for Task 3.
- [ ] Commit `docs: United States DL/ID number sources`.

### Task 3: United States DL/ID classes and factory

**Files:** `src/Document/US/{Jurisdiction,DlIdNumber,DlIdNumberFactory}.php`, `src/Document/US/DlId/*`, tests.

**Interfaces:** `DlIdNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision` with
`jurisdiction(): Jurisdiction`; `DlIdNumberFactory::fromNumber(string): DlIdNumber`, `::fromNumberIn(string,
Jurisdiction): DlIdNumber`, `::jurisdictionsMatching(string): list<Jurisdiction>`.

- [ ] One class per covered jurisdiction; tests per class: the quoted examples, every quoted shape, wrong
  length/characters, message `Invalid US-CA DL/ID number (...)` without input.
- [ ] Factory tests driven by Task 2's overlap table, row by row, plus Review Focus 1; a test that every covered class
  is registered in the factory and every enum case is either covered or listed as not covered.
- [ ] `make check`, `make test`, `make infection`; commit `feat: United States DL/ID numbers and factory`.

### Task 4: Canada, names and federal documents

**Files:** `docs/sources/CA.md` (names and federal part), `src/Name/CA/*`, `src/Document/CA/{SocialInsuranceNumber,
PassportNumber}.php`, `src/Codecs.php`, tests.

- [ ] Research, names, documents and gates as Task 1 (SIN: verify the check digit only if ESDC's text is quoted;
  the first digit's meaning, e.g. 9 for temporary residents, only as ESDC states it); commit
  `feat: Canada names and federal documents`.

### Task 5: Canada provincial documents

**Files:** `docs/sources/CA.md` (provincial sections), `src/Document/CA/{Jurisdiction,DriverLicenceNumber,
DriverLicenceNumberFactory,HealthCardNumber,HealthCardNumberFactory}.php`, `src/Document/CA/{DriverLicence,
HealthCard}/*`, tests.

- [ ] Research per province and territory for both documents, with an overlap table per document (as Task 2).
- [ ] Classes and factories as Task 3; health-card version codes (e.g. Ontario's letters) only as the province
  publishes them; encoded data (some licences encode the name and birth date) exposed only where the province defines
  the encoding.
- [ ] `make check`, `make test`, `make infection`; commit `feat: Canada provincial documents`.

### Task 6: Mexico

**Files:** `docs/sources/MX.md`, `src/Name/MX/*`, `src/Document/MX/*` (national covered set, plus `Jurisdiction`,
interface, classes and factory for state licences if any state is covered), `src/Codecs.php`, tests.

- [ ] Research, names, documents and gates as Task 1 (and Task 3 for state licences). CURP exposes `birthDate()`,
  `sex()`, `birthStateCode()` (RENAPO codes incl. `NE` for foreign-born are not ISO 3166-2, so a string);
  Review Focus 3 and 5. Commit `feat: Mexico names and documents`.

### Task 7: Brazil

**Files:** `docs/sources/BR.md`, `src/Name/BR/*`, `src/Document/BR/*` (national covered set, plus `Jurisdiction`,
interface, classes and factory for RG if any state is covered), `src/Codecs.php`, tests.

- [ ] Research, names, documents and gates as Task 1 (and Task 3 for RG). CPF exposes `fiscalRegion()` (the Receita
  Federal's region digit, a string, since a region spans several states); título de eleitor exposes the state as
  `Jurisdiction` only if the TSE's codes map one to one. Review Focus 4 and 5. Commit
  `feat: Brazil names and documents`.

### Task 8: Doctrine types

- [ ] In `vo-documents-doctrine`: one ORM and one ODM type per new value object (`vod_<cc>_<snake_name>`), samples,
  registrar entries; the completeness test passes. `make check`, `make test`, `make infection`; commit
  `feat: Doctrine types for the American documents`.

### Task 9: Documentation

- [ ] `README.md` class tables and "not covered" list (every uncovered US/CA/MX/BR jurisdiction with its reason),
  `CHANGELOG.md` `[Unreleased]`, `.claude/docs/*` in both repos; commit `docs: American documents and names`.
