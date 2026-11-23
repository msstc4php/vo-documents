# Changelog

## [1.0.0] - 2026-11-23

First stable release: from here on a breaking change needs 2.0.

**Backward compatibility (full text in the README, "Backward compatibility"):** 1.x covers public classes, methods,
parameter names and return types; the canonical stored form of every document number; the JSON view keys and the
codec and `__serialize()` payload keys of the names; enum backed values and case names; the public `MAX_LENGTH`
bounds and the bridge's type names and column lengths; exception classes and their parents; the MRZ century windows.
A minor never refuses a value an earlier 1.x release accepted (a check that gets a source later is reported by
`hasValid…()`, refusing waits for 2.0); a minor may accept more only within `MAX_LENGTH` and only when that adds no
candidate to a factory's frozen candidate set; `fromNumber()` exists only where a bare number can identify one
jurisdiction (CA health cards); enum cases may be added (keep a default arm); the package's interfaces are implemented by the package only and may gain methods in minors; `masked()` may hide
more, never less. Not covered: exception message wording, `Internal\` and `@internal`.

### Added

- A public `MAX_LENGTH` (in characters) on every string value object: the longest stored value, frozen for 1.x.
- `Document\KR\ForeignerRegistrationNumber::birthYearDigits()`, `birthMonth()`, `birthDay()`.
- `Internal\SubdivisionResolver::matches()`, `Internal\TwoDigitYearDate::inRanges()` and
  `Internal\Digits::composedWithCapitalEnye()` (internal).
- README sections "Rules that may relax" and "Backward compatibility".

### Changed

- Exception messages carry no part of the input, not even its length: a wrong length is `(wrong length)` whatever the
  input (was `(N characters)`, `(too long)` above 40 characters), an empty name part `(empty)` (was
  `(0 characters)`), a number of zeros only a checksum failure (was `(zeros only)`).
- Every `jsonSerialize()` view gives the full Latin form under `latin` (the names that derive it said
  `effectiveLatin`); their codec payloads store the override under `latinOverride` (was `latin`).
- The ES and MX surnames are `firstSurname` and `secondSurname` (properties, constructor parameters, view and payload
  keys; were `first`, `second`).
- `ForeignerRegistrationNumber` no longer implements `HasBirthDate`: no official text gives the century of the seventh
  digits 5-8.
- Relaxed where no official text supports a refusal: the SA first digit only routes a number to
  `NationalIdentityNumber` or `IqamaNumber` (a number with another first digit is accepted by both, and both accept
  the same ten digits); the MX clave de elector accepts `Ñ` among its six letters. `NameSuffix::fromCode()` reads
  `Jr.` (one trailing period).
- Birth dates are checked against the calendar only where an official text makes the digits the holder's birth date
  and binds the issuer to construct them from it (README "Limits to know about", "Birth dates"). Ranges only (month
  01-12, day 01-31; 30 February and 31 April accepted) for the ID NIK, the KR foreigner registration number, the MX
  clave de elector and the GB DVLA driver number; in 0.x all four refused a day their month lacks.
- `MX\Rfc` and `MX\ElectorKey` upper-case ASCII letters and `ñ` only: `ß`, `ﬁ`, `ı`, `ſ` and every other character
  outside the official alphabet are refused (`Rfc` accepted them in 0.x by full Unicode case mapping, `ßAB800101AB1`
  as `SSAB800101AB1`). Fullwidth letters still fold to ASCII first, as for every document number.
- `fitsLength()` of the CA health card classes is `@internal` (it serves the factory's "(fits no jurisdiction)"
  message); the AU classes have none.
- Enum cases are named after their code in upper case: the `Jurisdiction` cases of AU, US, CA, MX and BR
  (`Jurisdiction::NSW`, `::SP`), `DriverLicenceCategory::TM`, `::TB`, `NameSuffix::JR`, `::SR` and the ordinals
  `::FIRST` to `::NINTH`. Backed values are unchanged.
- `MachineReadableZone::birthDate()` takes `$reference` (was `$today`), like `expiryDate()`. The class is sealed
  (`Td1`, `Td2`, `Td3`), its `layout()` and `assertFormatSpecific()` hooks are `@internal`.
- The abstract bases of the jurisdiction classes and of the national name parts are `@internal`; type-hint the
  interface (`DlIdNumber`, `DriverLicenceNumber`, `HealthCardNumber`, `IdentityCardNumber`) or `AbstractNamePart`,
  whose protected hooks are `@internal`. `Icao\Transliterator::lists()` is `@internal`.
- The three exception families are `final`; the factories, `Codecs` and the static helpers have a private constructor.
- Class docblocks link the source notes by their GitHub URL (`docs/` is not in the installed package); ICAO Doc 9303
  Part 3 links use the host that answers.
- Requires `msstc4php/vo-base ^1.4` (unchanged).

### Removed

- `fromNumber()` of `US\DlIdNumberFactory`, `CA\DriverLicenceNumberFactory`, `MX\DriverLicenceNumberFactory` and
  `BR\IdentityCardNumberFactory`: those countries are only partly covered, so a single match among the covered
  jurisdictions does not identify the issuer (and would change when a jurisdiction is covered). `fromNumberIn()` and
  `jurisdictionsMatching()` stay. CA health cards cover every jurisdiction and keep `fromNumber()`.
- `AU\DriverLicenceNumberFactory::fromNumber()`: every valid number fits two or more jurisdictions with the published
  formats, so it could only throw.
- `Contract\Maskable` (it only redeclared vo-base's `Contract\Maskable`).
- `Icao\Transliterator::MAX_VARIANTS` (now private; the cap is 4096), `Internal\Digits::MAX_REPORTED_LENGTH`, the
  public `fitsLength()` of the AU licence classes, and the public constructors of the factories, `Codecs` and the
  static helpers.

### Upgrading from 0.x

- Replace `US\DlIdNumberFactory::fromNumber()`, `CA\DriverLicenceNumberFactory::fromNumber()`,
  `MX\DriverLicenceNumberFactory::fromNumber()`, `BR\IdentityCardNumberFactory::fromNumber()` and
  `AU\DriverLicenceNumberFactory::fromNumber()` (which always threw) with `fromNumberIn($number, $jurisdiction)`;
  `jurisdictionsMatching()` lists the covered jurisdictions a number fits.
- `isValid()` and `tryFrom()` now accept, where 0.4 refused: any first digit in `SA\NationalIdentityNumber` and
  `SA\IqamaNumber`; a day the month lacks (month 01-12, day 01-31) in `ID\NationalIdentityNumber` (30 February),
  `KR\ForeignerRegistrationNumber` (also 29 February `00` with 5 or 6), `MX\ElectorKey` and
  `GB\GreatBritainDriverLicenceNumber`; `Ñ` among the six letters of `MX\ElectorKey`
  (`US\NameSuffix::fromCode()` also reads `Jr.`). They now refuse, where 0.4 accepted: `ß`, `ﬁ`, `ı`, `ſ` and other characters outside
  the SAT alphabet in `MX\Rfc` (0.4 rewrote them as `SS`, `FI`, `I`, `S`).
- **SA routing:** both SA classes accept the same ten digits, so `NationalIdentityNumber::tryFrom($n) ??
  IqamaNumber::from($n)` now types every iqama as a national ID, and a column of either type accepts the other's
  numbers. Branch on the first digit yourself (`1` national ID, `2` iqama, ZATCA BR-KSA-F-10/F-11) and decide what
  another first digit means for you (README, SA row and "Limits to know about").
- `fitsLength()` of the AU licence classes is gone and that of the CA health card classes is `@internal`: use
  `jurisdictionsMatching()`. `Icao\Transliterator::MAX_VARIANTS` is private (the cap is 4096).
  `new Codecs()` and `new` on a factory or a static helper fail: call their static methods.
- `Name\PersonName` (interface) is `Name\NationalPersonName`.
- `MSSTC4PHP\ValueObject\Documents\Contract\Maskable` is `MSSTC4PHP\ValueObject\Contract\Maskable`.
- ES and MX names: `->first`/`->second` and the named arguments `first:`/`second:` are `firstSurname`/`secondSurname`.
- Enum cases: `Jurisdiction::Nsw` → `Jurisdiction::NSW` (every AU, US, CA, MX and BR case: upper case),
  `DriverLicenceCategory::Tm`/`Tb` → `TM`/`TB`, `NameSuffix::Junior`/`Senior` → `JR`/`SR`, `NameSuffix::First` …
  `Ninth` → `FIRST` … `NINTH`. Stored backed values (`AU-NSW`, `Tm`, `1ST`) are unchanged.
- `MachineReadableZone::birthDate(today: …)` → `birthDate(reference: …)` (only for named arguments).
- `ForeignerRegistrationNumber::birthDate()` is gone: use `birthYearDigits()`, `birthMonth()`, `birthDay()`; code that
  treated it as `HasBirthDate` must not.
- Clients of the JSON views: read `latin` instead of `effectiveLatin` (RU, AU, DE, FR, IT, ES, NL, CH, GB, TR, US, CA,
  MX, BR, JP, IN), and `firstSurname`/`secondSurname` instead of `first`/`second` (ES, MX).
- **Stored payloads change text.** Every composite name is written with the 1.0 keys (`latinOverride` on every name
  that derives its Latin form, also when it is null; `firstSurname`/`secondSurname` for ES and MX). Reading 0.x
  payloads and serialized names keeps working through 1.x (the codecs read `latin` as `latinOverride` and `first`/
  `second` as the surname keys). A stored row keeps its 0.x text until it is re-encoded: a flush does not rewrite it
  (ORM 3 even skips an equal new instance, as its database value is the same 1.0 text). Until then, lookups by the
  stored text miss: an equality query binds the 1.0 text and misses 0.x rows, and a JSON-path or ODM field query on
  one version's keys (`->>'latin'`, `$.first`, `name.first`) misses the other's rows. Migrate once after upgrading:
  decode each stored payload and write it back encoded (the `vo-documents-doctrine` 1.0.0 entry gives the DBAL
  recipe); a key rename in the database fixes JSON-path queries only.
- Going back to 0.x once 1.0 payloads exist is not supported: 0.x cannot read the 1.0 keys. Do not run 0.x and 1.x
  side by side on one store. The 0.2 PHP object layout is still read and will be dropped in 2.0.
- Code that matched exception messages: lengths are no longer reported (`(wrong length)`, `(empty)`), and zeros only
  reads as a checksum failure; the SA "(invalid first digit)" and the calendar refusals of the KR foreigner number, the ID NIK, the MX clave de
  elector and the GB DVLA driver number are gone (month and day ranges remain).
- Subclasses of `InvalidDocumentNumberException`, `InvalidMrzException` and `InvalidPersonNameException` are no longer
  possible; catch the family. Extending a jurisdiction or name part base, or `MachineReadableZone`, is outside the
  promise.

## [0.4.0] - 2026-11-23

### Added

- `Icao\Mrz\MachineReadableZone` implements vo-base's `Contract\ResolvesConcreteClass`: every vo-base bridge accepts
  the abstract type for a column, a property or a parameter and returns the fitting `Td1`, `Td2` or `Td3`.
- `Codecs` implements vo-base's `Payload\ProvidesCodecs`, so the Symfony bundle registers every composite-name codec
  from one line (`codec_providers`). README "Bridges" shows the one-place setup for each bridge.

### Changed

- Requires `msstc4php/vo-base ^1.4` (was `^1.3`).

## [0.3.0] - 2026-11-23

### Changed

- Every national `PersonName` (all 21 classes of `Codecs::all()`) serializes as its codec payload (for example
  `family`, `given`, `latin`) and is rebuilt through the codec's `decode()` on `unserialize()`, so the parts are
  validated again and the Latin form is derived again (`Internal\ReparsesThroughCodec`). Before, PHP's default layout
  restored the private Latin form as stored: a forged serialized string could give a name a `latin()` that does not
  belong to its parts, and a missing property left the object half built. A forged Latin override now throws
  `InvalidPersonNameException`, a payload of the wrong shape vo-base `InvalidPayloadException`.
- Names serialized by 0.2 still load: their properties are read, encoded and decoded the same way (a stored Latin form
  is derived again, never trusted). A 0.2 payload with a missing or mistyped property throws vo-base
  `InvalidValueObjectException` ("A serialized <class> is malformed."); keys of properties the class does not have are
  ignored. Payloads in the new format follow the codec's shape exactly, as `decode()` does.
- Names serialized by 0.3 do not load on 0.2 or 0.1, which would restore a half-built object without an error: empty
  caches, sessions and queues that hold serialized names before downgrading, and do not run 0.2 and 0.3 side by side on
  the same store.
- Requires `msstc4php/vo-base ^1.3` (was `^1.2`). vo-base 1.3 runs `parse()` again on `unserialize()` for every
  string, int and float value object, so `Internal\ReparsesOnUnserialize` is removed and `Icao\Mrz\MachineReadableZone`
  relies on the base. A payload without a string value now fails with vo-base's message "A serialized <class> must
  hold its value as a string." instead of "A serialized value object must hold its value, as a string."

## [0.2.0] - 2026-11-22

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
  Mexican CURP. `Contract\HasBirthDate` is implemented by the CURP (its 17th character gives the century), the CN
  citizen identity number and 2023 permanent residence number, and both KR registration numbers (corrected in 1.0:
  this line said "the CURP only"; since 1.0 the KR foreigner number no longer implements it).
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
