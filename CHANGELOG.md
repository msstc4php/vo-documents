# Changelog

## [Unreleased]

### Added

- `Icao\Mrz\MachineReadableZone` with the final classes `Td1`, `Td2` and `Td3`: the ICAO Doc 9303 machine readable
  zone of TD1, TD2 and TD3 documents (Parts 3 to 6), every check digit enforced, composite one included, TD1 and TD2
  long document numbers supported; `fromString()` takes 2 or 3 lines joined by `\n` or `\r\n` or one string of 88, 72
  or 90 characters. Accessors for the codes, name, sex, document number, optional data and the raw dates; `masked()`.
  `InvalidMrzException`. `MachineReadableZone` implements `HasSex`: `sex()` is `Sex::Unspecified` for `<` (Doc 9303:
  "< = unspecified"); `X` (a VIZ value) is refused. It does not implement `HasBirthDate` (`birthDate()` takes the
  reference date, since the MRZ has no century). The century rules are not in Doc 9303, see `docs/sources/ICAO.md`:
  `expiryDate()` puts the year between 84 years before and 15 years after the reference year; `birthDate()` takes the
  most recent past century and works for any reference year.
- `Td1::fromString()`, `Td2::fromString()` and `Td3::fromString()` refuse another format's lines as "Invalid ICAO MRZ."
  without building that format.
- The MRZ serializes as its value alone and is validated again on `unserialize()` (`Internal\ReparsesOnUnserialize`):
  a forged or stale payload throws `InvalidMrzException`, a payload without a string value vo-base
  `InvalidValueObjectException`, other keys are ignored.
- `Contract\Maskable` now extends vo-base `Contract\Maskable`, so every `Maskable` document class is also one of the
  core. Requires `msstc4php/vo-base ^1.2` (was `^1.1.1`).

## [0.1.0] - 2026-11-17

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
- Asia and Saudi Arabia (plan 4): names (`Name\<CC>`, codecs in `Codecs::all()`, 21 in all) and documents for China,
  Japan, South Korea, India, Indonesia and Saudi Arabia. CN: citizen identity number, 2023 and legacy foreigner
  permanent residence numbers, ordinary passport, Hong Kong/Macao and Taiwan exit-entry permits. JP: My Number, basic
  pension number, residence card, passport, driving licence. KR: resident and foreigner registration numbers,
  multiple-entry passport, driving licence. IN: Aadhaar, PAN with `PanHolderType`, passport, ABHA number. ID: NIK, NPWP,
  family card number. SA: national ID and iqama numbers (`AbstractIdentityNumber`). Names: CN, KR, ID, SA and the
  Devanagari ones of IN require the Latin form of the passport and never derive it
  (`Name\RequiredLatin\AbstractRequiredLatinPersonName` for CN and KR; a name with no family and given split is stored
  whole in `family` with a null `given`); JP derives it by MOFA's Hepburn table from the 振り仮名
  (`Name\JP\PassportHepburn`, override policy `AnyIcaoSpelling`); IN in Latin letters uses `TransliterationVariant`.
  Source notes `docs/sources/{CN,JP,KR,IN,ID,SA}.md`.
- Every document number folds fullwidth digits and letters and Arabic-Indic digits (U+0660-0669, U+06F0-06F9) to ASCII
  and strips U+3000 (`Internal\Digits::fold()`), so numbers typed on a CJK or Arabic keyboard parse. Names do not fold.
- `Name\Latin\AbstractLatinNamePart::wordWithAbbreviationDots()` (full stops after or between letters: `Moh.`, `M.A.`),
  shared by the Mexican and Indonesian name parts.
- `HealthCardNumberFactory::fromNumber()` points to `OnHealthCardNumber::fromCard()` in its no-match message for an
  Ontario card with a version code.
- `Name\Latin\AbstractLatinNamePart` and internal bases shared by the Latin-script countries (`LatinPersonNameBuilder`,
  `AbstractLatinPersonName`, `AbstractFamilyGivenPersonName`, `AbstractFamilyGivenPersonNameCodec`); `Name\AU\*` moved
  onto them with the same class names, constructors and methods. The public `family`/`given` properties of the family +
  given names are now declared on the base and typed as the abstract part (`AbstractNamePart`) at runtime; static
  analysers see the country's classes through `@extends`.
- `Contract\HasSex` is implemented by `Nir`, `FiscalCode`, the DVLA licence number, `ChiNumber`, the Québec NAM and the
  Mexican CURP. `Contract\HasBirthDate` is implemented by the CURP only (its 17th character gives the century).
- Source notes in `docs/sources/` (RU, AU, ICAO, DE, FR, IT, ES, NL, CH, GB, TR, US, CA, MX, BR, CN, JP, KR, IN, ID,
  SA).

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
- Asian decisions: checksums verified are the CN citizen identity and 2023 permanent residence numbers (ISO 7064 MOD
  11-2), the JP My Number and the IN Aadhaar (Verhoeff); everything else is format only (JP driving licence check digit:
  the notice gives no weights; KR numbers carry random digits since 2020-10). The Aadhaar Verhoeff rests on UIDAI's 2010
  paper, which recommends it (no first-digit rule is enforced); the SA national ID and iqama first digits (1 and 2) rest
  on a ZATCA staff forum announcement and are refused, not warned; a KR foreigner number with a non-calendar birth date
  and an ID NIK with an impossible date are refused. The CN and SA driving licence numbers are the identity numbers (no
  class); the ID SIM is the NIK. JP passports derive `井上` as `INOE` (a long vowel); the override `INOUE` fixes it.
  README "Not covered" now lists all 20 countries.
- Code review of plan 4: the IN passport mask keeps the last 3 characters (`*****002`); both KR licence forms mask as 12
  digits (`********3361`); `PermanentAccountNumber::holderType()` throws a fixed-text `LogicException` on a tampered
  value instead of echoing it. Numbers also strip the bidi marks U+200E, U+200F, U+061C, U+202A-202E and U+2066-2069.
  Name words start with a letter and use letters only: Han and Hangul parts take `\p{Lo}` (radicals, Hangzhou numerals
  and the Hangul tone marks are refused; CN and JP keep `〇`, JP keeps `々` after a character), a JP part or reading
  cannot start with `ー` or an iteration mark, and the Devanagari ZWJ/ZWNJ is allowed only after a virama. KR folds
  U+30FB and U+2022 to `·` like CN; SA stores the Persian kaf U+06A9 and yeh U+06CC as U+0643 and U+064A. JP readings
  expand `ゝゞヽヾ` before the Hepburn derivation (`いすゞ` → `ISUZU`). Name length messages say "(too long)" above 100
  characters, and a raw input over 10 000 bytes is refused before normalisation.
- Name classes use `\p{sc=Latin}`, `\p{sc=Cyrillic}`, `\p{sc=Han}`, `\p{sc=Hangul}`, `\p{sc=Arabic}` and
  `\p{sc=Devanagari}` (Script property): requires `lib-pcre >=10.40`.
- Requires `msstc4php/vo-base ^1.1.1`.

### Compatibility policy

- Transliteration tables and Latin override rules change only in a major version: a stored override is validated
  again on hydration, so a narrower table or rule would make stored names unreadable.
