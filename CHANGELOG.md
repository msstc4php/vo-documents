# Changelog

## [Unreleased]

### Added

- Document numbers, each with `formatted()`, `masked()` and a checksum where an algorithm exists: Russia (`Snils`,
  `Inn`, `InternalPassportNumber`, `PassportIssuingUnitCode`, `InternationalPassportNumber`, `DriverLicenceNumber`,
  `DriverLicenceCategory`, `OmsPolicyNumber`) and Australia (`TaxFileNumber`, `MedicareCardNumber`, a licence number per
  state or territory, `Jurisdiction`, `DriverLicenceNumberFactory`).
- `Icao\PersonName` (ICAO Doc 9303 Latin name, `mrzField()` truncation) and `Icao\Transliterator` with the multinational
  Latin and Cyrillic tables.
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
- `Name\Latin\AbstractLatinNamePart` and internal bases shared by the Latin-script countries
  (`LatinPersonNameBuilder`, `AbstractLatinPersonName`, `AbstractFamilyGivenPersonName`,
  `AbstractFamilyGivenPersonNameCodec`); `Name\AU\*` moved onto them without a public API change.
- `Contract\HasSex` is implemented by `Nir`, `FiscalCode`, the DVLA licence number and `ChiNumber`.
- Source notes in `docs/sources/` (RU, AU, ICAO, DE, FR, IT, ES, NL, CH, GB, TR).

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
- Owner's rule: a checksum is verified only when its algorithm is quoted from an official source. Verified: DE
  pension insurance number, IT codice fiscale, ES DNI and NIE, NL BSN, CH AHV number, GB NHS and H&C numbers. Format
  only: DE Steuer-ID, FR NIR key, ES social security number and pre-2008 NIE letters, GB CHI check digit and DVLA
  check characters, TR T.C. Kimlik No, and the other identity, passport and licence numbers.
- Latin override policy per country: `TransliterationVariant` (AU, DE, FR, IT, ES, NL, CH, TR) or `AnyIcaoSpelling`
  (RU, GB).
- Not covered: see README "Not covered" for the full list with reasons (among them DE Führerschein, FR 2021 identity
  card, IT CIE and patente, ES passport, NL rijbewijs, TR sürücü belgesi; the ES driving licence is the DNI or NIE).
- RU and GB (`AnyIcaoSpelling`) accept an override when the name has no derived form; the transliteration variant cap
  is per ICAO word; FR old identity cards accept the Corsican `2A`/`2B` place codes.
- Name limits: FR names use the 2014 civil-status circular's letters; NL refuses `ĳ`; TR refuses `Q`, `W`, `X`, hyphens
  and apostrophes.
- Requires `msstc4php/vo-base ^1.1.1`.

### Compatibility policy

- Transliteration tables and Latin override rules change only in a major version: a stored override is validated
  again on hydration, so a narrower table or rule would make stored names unreadable.
