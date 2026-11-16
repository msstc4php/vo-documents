# Changelog

## [Unreleased]

### Added

- Document numbers, each with `formatted()`, `masked()` and a checksum where an algorithm exists: Russia (`Snils`,
  `Inn`, `InternalPassportNumber`, `PassportIssuingUnitCode`, `InternationalPassportNumber`, `DriverLicenceNumber`,
  `DriverLicenceCategory`, `OmsPolicyNumber`) and Australia (`TaxFileNumber`, `MedicareCardNumber`, a licence number
  per state or territory, `Jurisdiction`, `DriverLicenceNumberFactory`).
- `Icao\PersonName` (ICAO Doc 9303 Latin name, `mrzField()` truncation) and `Icao\Transliterator` with the
  multinational Latin and Cyrillic tables.
- National names `Name\RU\*` and `Name\AU\*`: name parts, `PersonName` with a derived Latin form and an optional Latin
  override, payload codecs, `Codecs::all()`.
- Exceptions per family: `InvalidDocumentNumberException`, `InvalidPersonNameException`,
  `AmbiguousJurisdictionException`. No message contains the input.
- Europe (plan 2): names (`Name\<CC>\FamilyName`, `GivenName`, `PersonName`, codecs in `Codecs::all()`) and documents
  for Germany, France, Italy, Spain, the Netherlands, Switzerland, the United Kingdom and Türkiye: DE Personalausweis,
  Reisepass, Steuer-ID, Rentenversicherungsnummer; FR NIR, CNI (issued until 2021), passport, numéro fiscal, permis; IT
  codice fiscale, passport; ES DNI, NIE, social security number; NL BSN, identity document number; CH AHV number,
  identity document number, driving licence; GB NINO, passport, DVLA and DVA licences, NHS, H&C and CHI numbers; TR
  T.C. Kimlik No, passport. Source notes `docs/sources/{DE,FR,IT,ES,NL,CH,GB,TR}.md`.
- Americas (plan 3): names (`Name\<CC>\FamilyName`, `GivenName`, `PersonName`, codecs in `Codecs::all()`; also
  `Name\US\MiddleName` and `Name\US\NameSuffix`) and documents for the United States, Canada, Mexico and Brazil. US:
  SSN, ITIN, passport book and card, Medicare beneficiary identifier, DL/ID numbers of 30 states with
  `DlIdNumberFactory`. CA: SIN, passport, driver's licences of 6 provinces and health cards of all 13 with
  `DriverLicenceNumberFactory` and `HealthCardNumberFactory`. MX: CURP, clave de elector, RFC, NSS and the Estado de
  México licence with `DriverLicenceNumberFactory`. BR: CPF, título de eleitor, NIS, CNS, CNH, passport and the São
  Paulo RG with `IdentityCardNumberFactory`. Each country has a `Jurisdiction` enum (ISO 3166-2: 51, 13, 32 and 27
  cases). Source notes `docs/sources/{US,CA,MX,BR}.md`.
- `Name\Latin\AbstractLatinNamePart` and internal bases shared by the Latin-script countries (`LatinPersonNameBuilder`,
  `AbstractLatinPersonName`, `AbstractFamilyGivenPersonName`, `AbstractFamilyGivenPersonNameCodec`); `Name\AU\*` moved
  onto them with the same class names, constructors and methods. The public `family`/`given` properties of the family +
  given names are now declared on the base and typed as the abstract part (`AbstractNamePart`) at runtime; static
  analysers see the country's classes through `@extends`.
- `Contract\HasSex` is implemented by `Nir`, `FiscalCode`, the DVLA licence number, `ChiNumber`, the Québec NAM and the
  Mexican CURP. `Contract\HasBirthDate` is implemented by the CURP only (its 17th character gives the century).
- Source notes in `docs/sources/` (RU, AU, ICAO, DE, FR, IT, ES, NL, CH, GB, TR, US, CA, MX, BR).

### Notes

- The Russian derived Latin name omits the patronymic and the soft sign Ь (MVD Orders 996 and 186).
- `Inn`, `TaxFileNumber` and `MedicareCardNumber` validate the format only; the check digit is not verified because no
  official algorithm is published. Only checksums with an official source are verified: `Snils`, `OmsPolicyNumber` and
  the European ones listed below.
- `DriverLicenceNumberFactory::fromNumber()` is always ambiguous for AU with the published formats; use
  `fromNumberIn()`.
- Name parts are limited to 100 characters, `Icao\PersonName` to 255.
- A Latin letter outside ICAO table 6.A is spelled as its base letter (`Nguyễn` → `NGUYEN`, `Ș` → `S`); compatibility
  characters (fullwidth letters, `ª`, `ﬁ`) are refused in name parts, except those an ICAO table lists (`Ĳ`, `Ŀ`).
- Owner's rule: a checksum is verified only when its algorithm is quoted from an official source. Verified: DE pension
  insurance number, IT codice fiscale, ES DNI and NIE, NL BSN, CH AHV number, GB NHS and H&C numbers. Format only: DE
  Steuer-ID, FR NIR key, ES social security number and pre-2008 NIE letters, GB CHI check digit and DVLA check
  characters, TR T.C. Kimlik No, and the other identity, passport and licence numbers.
- Latin override policy per country: `TransliterationVariant` (AU, DE, FR, IT, ES, NL, CH, TR, US, MX) or
  `AnyIcaoSpelling` (RU, GB, CA, BR).
- Not covered: see README "Not covered" for the full list with reasons (among them DE Führerschein, FR 2021 identity
  card, IT CIE and patente, ES passport, NL rijbewijs, TR sürücü belgesi, 21 US DL/ID jurisdictions, 7 CA licences, 31
  MX state licences, the MX passport, 26 BR RG units; the ES driving licence is the DNI or NIE).
- RU, GB and CA (`AnyIcaoSpelling`) accept an override when the name has no derived form; the transliteration variant
  cap is per ICAO word; FR old identity cards accept the Corsican `2A`/`2B` place codes.
- Name limits: FR names use the 2014 civil-status circular's letters; NL refuses `ĳ`; TR refuses `Q`, `W`, `X`, hyphens
  and apostrophes.
- `Sex::Unspecified` exists for the CURP's `X` (RENAPO, non-binary persons); no other document returns it.
- Mexican name parts accept `/` and `.` as RENAPO records them and the ICAO form omits them (Doc 9303 Part 3 §4.6); no
  other country accepts them. Spanish name parts accept the Catalan middle dot U+00B7 only in `l·l`, omitted likewise
  (`Marcel·lí` → `MARCELLI`); `ŀ`/`Ŀ` are stored as `l·`/`L·`. Name parts with an apostrophe separator store U+02BC
  as U+0027, as they do U+2019.
- Americas decisions: US SSN refuses only the SSA rules (the IRM's open list of "never issued" numbers is not applied);
  BC and SK licence numbers are stored zero-padded to 8 digits and are always ambiguous for `fromNumber()`; the Ontario
  health number is the ten digits and its version code a value object of its own (`OnHealthCardVersionCode`), split
  from the printed card by `OnHealthCardNumber::fromCard()` (an `OnHealthCard` result); `OnHealthCardNumber::from()`
  refuses a trailing version code instead of dropping it; the SP RG is stored with its number part zero-padded to 8
  digits (9 characters), so `1.234.567-2` and `01.234.567-2` are one value; the CPF accepts repeated digits; checksums of the BC and ON health
  cards, the BR CPF, NIS, CNS and SP RG are verified, those of the CURP, RFC, NSS, SIN, título de eleitor and CNH are
  not (no published algorithm). An official source is any government body's own publication; uncovered jurisdictions
  and their reasons are in README "Not covered" (`fromNumberIn()` throws "(format not covered)").
- With one covered unit (MX licences, BR RG), `fromNumber()` assigns any fitting number to that unit; callers that
  know the issuer must use `fromNumberIn()`.
- Name classes use `\p{sc=Latin}` and `\p{sc=Cyrillic}` (Script property): requires `lib-pcre >=10.40`.
- Requires `msstc4php/vo-base ^1.1.1`.

### Compatibility policy

- Transliteration tables and Latin override rules change only in a major version: a stored override is validated
  again on hydration, so a narrower table or rule would make stored names unreadable.
