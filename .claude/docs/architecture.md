# Architecture

deptrac forbids every framework namespace (Doctrine, Symfony, Illuminate, yii, CodeIgniter, Cycle, Spiral), as in the
core.

| Area | Purpose |
|---|---|
| `Contract\`, `Exception\`, `Sex` | Shared interfaces (`HasSex`, `HasBirthDate` (CURP only), `Maskable`, `Subdivision`, `IssuedBySubdivision`), the three exception families, the sex enum (`Female`, `Male`, `Unspecified` for the CURP `X`) |
| `Internal\` | `@internal` helpers: `Checksum`, `Mask`, `Digits`, `UnicodeText`/`Text`, `NumberFormat` (character class + length range of a licence), `NumberShapes` (several fixed shapes, length derived from the shape: US DL/ID; shares `Digits::upperCasedOfLength()` with `NumberFormat`), `SubdivisionResolver` (one match / none / ambiguous over an enum of subdivisions), `PayloadFields` (codec payload validation; `encodable()` encode guard, `latin()` required ICAO field, `latinOverride()`), `Optional` (`equal()` of two optional value objects), `TwoDigitYearDate` (day/month valid in some century: DVLA, CHI, IT codice fiscale), `Gb11643` (18-character layout shared by the CN citizen number and the 2023 permanent residence number: `normalize()`, birth date, MOD 11-2 check), `KoreanRegistrationNumber` (13 digits of the KR resident and foreigner numbers: birth date and sex from the 7th digit). `Digits::fold()` maps fullwidth digits and letters and Arabic-Indic digits (U+0660-0669, U+06F0-06F9) to ASCII before every number parse, and `SEPARATORS` includes U+3000; `Checksum` gains `verhoeff()` (IN Aadhaar) |
| `Icao\Mrz\*` | `MachineReadableZone` (abstract; `fromString()` picks `Td1`/`Td2`/`Td3` by length 90/72/88 or by 3/2/2 lines) and the final `Td1`, `Td2`, `Td3`; `@internal` `Internal\MrzLayout`, `MrzField`, `MrzCheckDigit` (7-3-1 weights, `<` = 0), `MrzDocumentNumber` (long-number split). `HasSex` (`<` = `Sex::Unspecified`), not `HasBirthDate`; `Maskable`; re-validated on unserialize by vo-base 1.3. Source notes `docs/sources/ICAO.md` section 5 |
| `Icao\PersonName` | MRZ-style Latin name (`A-Z`, `-`, `<`; `<<` between primary and secondary), `mrzField()` truncation |
| `Icao\Transliterator` | National name part → ICAO Latin (`toLatin()` default, `variants()` all spellings) |
| `Icao\Table\*` | `@internal` `TransliterationTable` per script (LatinMultinational, Cyrillic); add a class + register it in the `Transliterator` constructor for a new script |
| `Name\AbstractNamePart` | Base of every national name part: `letters()` (regex class), `label()` (message), `innerSeparators()` (default `-' `) |
| `Name\LatinSpelling` | `@internal`: finds a Latin override among the ICAO spellings of a name, word by word (`Name\IdentifierSpellings`: variants per word + national separators; hyphen ≡ `<`; cap 4096 per word) |
| `Name\LatinOverridePolicy` | `@internal` abstract: `resolve(?override, derived)` (template method, drops the derived form); `withoutDerivedForm()` (refuses, except `AnyIcaoSpelling` with an override); subclasses `AnyIcaoSpelling` (RU, GB, CA, BR, JP) and `TransliterationVariant` (AU, DE, FR, IT, ES, NL, CH, TR, US, MX, IN in Latin letters; via `LatinSpelling`). CN, KR, ID, SA and IN-Devanagari have no policy: the Latin form is required |
| `Name\PersonName` | Interface of a national full name: `latin(): Icao\PersonName`, `full()` |
| `Name\Latin\*` | `AbstractLatinNamePart` (`\p{sc=Latin}` part; `wordWithAbbreviationDots()` for MX and ID); `@internal`: `LatinPersonNameBuilder` (derived form, spellings per word for `TransliterationVariant`; strips the `$omitted` punctuation first), `AbstractLatinPersonName` (effective Latin form + kept override, hook `overridePolicy()`; constructor argument `$omittedPunctuation` (MRZ-omitted punctuation, default none, MX `.` `/`, ES `·`)), `AbstractFamilyGivenPersonName<TFamily, TGiven>` (public `family`/`given`, `full()`, `equals()`, JSON view; AU DE FR IT TR NL CH GB CA BR), `AbstractFamilyGivenPersonNameCodec<TName>` (keys `family`/`given`/`latin`; hooks `shape()`, `create()`) |
| `Name\RequiredLatin\*` | `@internal` `AbstractRequiredLatinPersonName<TFamily, TGiven>` (family + nullable given in a national script + a **mandatory** `Icao\PersonName`, never derived; equality includes it, compared as stored; JSON view has `latin`, typed `RequiredLatinPayloadShape`) and its codec (keys `family`, `given`, `latin`; hooks `shape()`, `create()`); used by CN and KR. `given` null stores a name with no family/given split whole in `family`. The Latin base (`Name\Latin`) is the opposite: derived form plus optional override |
| `Name\RU\*` | `AbstractRussianNamePart` (letters, word pattern), `FamilyName`, `GivenName`, `Patronymic`, `PersonName` (+ `short()`, `initialsFirst()`), `PersonNameCodec`; `@internal RussianTransliteration` (drops Ь, then `Icao\Transliterator`) |
| `Document\RU\*` | Number VOs (`Snils`, `Inn`, `InternalPassportNumber`, `PassportIssuingUnitCode`, `InternationalPassportNumber`, `DriverLicenceNumber`, `OmsPolicyNumber`): `final`, extend `AbstractStringValueObject`, implement `Maskable`, store digits only, `formatted()`; enum `DriverLicenceCategory`. No `Region` (no published ISO mapping, RU.md) |
| `Codecs` | `Codecs::all()`: every payload codec, for `new ValueObjectMapper(Codecs::all())`; add each new composite's codec here |

The transliterator picks the one table whose `script()` covers every letter of the input; letters from two tables
→ "mixed scripts", none → "no ICAO transliteration".

`Document\AU\*`: `Jurisdiction` (enum, ISO 3166-2), `DriverLicenceNumber` interface, `AbstractDriverLicenceNumber` + one
final class per jurisdiction in `DriverLicence\`, `DriverLicenceNumberFactory`, `TaxFileNumber`, `MedicareCardNumber`.
`Name\AU\*`: part classes on the Latin base, `PersonName`/`PersonNameCodec` on the `Name\Latin` family+given bases.

`Document\DE\*`: `IdentityCardNumber` (+ `authorityCode()`), `PassportNumber`, `TaxIdentificationNumber` (format
only), `PensionInsuranceNumber` (VKVV checksum; fields as strings, no `HasBirthDate`/`HasSex`). `Name\DE\*` mirrors
`Name\AU\*` (Latin base, `TransliterationVariant`).

`Document\FR\*`: `Nir` (`HasSex`; DSN field rules; key format only; `birthMonth()` nullable, `birthPlaceCode()`
string), `LegacyIdentityCardNumber` (12-character card issued until 2021, `2A`/`2B` accepted in the place code; the
2021 card is not covered), `PassportNumber`
(`99AA99999`), `TaxIdentificationNumber` (SPI, format only), `DriverLicenceNumber` (12 digits, format only).
`Name\FR\*`: `AbstractFrenchNamePart` overrides `letters()` with the 2014 civil-status circular's list (no `\p{Latin}`).

`Document\IT\*`: `FiscalCode` (codice fiscale; `HasSex`; DM 1976 art. 7 check letter verified; omocodia letters read
through lookup tables; year as two digits, `birthMonth()`/`birthDay()` ints, `birthPlaceCode()` string),
`PassportNumber` (`AA9999999`). CIE and patente numbers are not covered. `Name\IT\*` mirrors `Name\DE\*`.

`Document\ES\*`: `Dni` (8 digits + mod 23 letter, verified), `Nie` (X/Y/Z + 7 digits + letter, verified through the
internal `ControlLetter`; pre-2008 X + 8 digits + letter, format only), `SocialSecurityNumber` (12 digits, format only;
`provinceCode()` string). The driving licence number is the DNI or NIE (no class); the passport number is not covered.
`Name\ES\PersonName($first, ?$second, $given, ?$latin)` extends `AbstractLatinPersonName` only: both surnames are family
parts (primary identifier joined by a space); codec keys `first`, `second`, `given`, `latin`. Parts extend
`Name\ES\AbstractSpanishNamePart` (U+00B7 only in `l·l`, matched as `\x{00B7}`; `ŀ`/`Ŀ` folded to `l·`/`L·`); the
`PersonName` passes `['·']` as `$omittedPunctuation`.

`Document\NL\*`: `Bsn` (9 digits, elfproef verified per LO BSN; 8 digits read with a leading zero and stored with 9),
`IdentityDocumentNumber` (passport, emergency documents and identity card: 2 letters, 6 letters/digits, 1 digit, no
`O`, `0` allowed for model 2014). The rijbewijsnummer is not covered. `Name\NL\PersonName($family, ?$given, ?$latin)`:
prefixes live inside `FamilyName` as typed; letters come from the BRP Teletex tables (`Name\NL\AbstractDutchNamePart`,
which also overrides `word()` for apostrophe prefixes); codec keys `family`, `given` (null for one name), `latin`.

`Document\CH\*`: `AhvNumber` (13 digits, `756` + 9 random + check digit verified per BSV WL VA/IK Anhang 7, weights
1/3 mod 10; dots of `756.3047.5009.62` accepted; `formatted()` gives that form), `IdentityDocumentNumber` (passport and
identity card: 8 characters, no `I`/`O`, format only), `DriverLicenceNumber` (federal, not per canton: 12 digits =
FABER-PIN + running number with `faberPin()`/`sequenceNumber()`, or 15 digits of the 2003 card with both null).
`Name\CH\PersonName($family, ?$given, ?$latin)`: letters of ISO 8859-1 + Latin Extended-A
(`Name\CH\AbstractSwissNamePart`, SR 143.111 Art. 22 / ZStV Art. 80); codec keys `family`, `given` (null without a
given name), `latin`.

`Document\GB\*`: `NationalInsuranceNumber` (HMRC NIM39110 prefix exclusions, suffix `A`-`D`, no check digit),
`PassportNumber` (9 digits), `GreatBritainDriverLicenceNumber` (DVLA, 16 characters; `HasSex`, two-digit year,
`birthMonth()`/`birthDay()`; check characters random, not verified), `NorthernIrelandDriverLicenceNumber` (DVA, 8
digits; two plain classes, no `SubdivisionResolver`: the formats never overlap), `NhsNumber` and `HealthAndCareNumber`
(NHS Modulus 11 verified through the internal `NhsCheckDigit`; H&C range 320000001-399999999), `ChiNumber` (Scotland;
`HasSex`, `DDMMYY` date validated, check digit format only). `Internal\TwoDigitYearDate` validates a day/month with a
century-less year (shared by the DVLA and CHI classes). `Name\GB\PersonName($family, ?$given, ?$latin)`: `\p{Latin}`
parts, override policy `AnyIcaoSpelling` (HMPO writes a documented spelling; kept also without a derived form), `given`
null for a person with one name (an override's lone `XXX` forename is dropped);
codec keys `family`, `given`, `latin`.

`Document\TR\*`: `IdentityNumber` (T.C. Kimlik No, 11 digits, format only: NVİ does not publish the check-digit
algorithm), `PassportNumber` (letter + 8 digits, PRADO composition). Sürücü belgesi number not covered (no published
layout). `Name\TR\PersonName($family, $given, ?$latin)`: letters of the Law 1353 alphabet plus `â î û`
(`Name\TR\AbstractTurkishNamePart`), space as the only separator, override policy `TransliterationVariant`; codec
keys `family`, `given`, `latin`.

`Document\US\*` (federal): `SocialSecurityNumber` (SSA refusals: area
000/666/9xx, group 00, serial 0000; `formatted()` `123-45-6789`), `Itin` (first digit 9, digits 4-5 in the IRM ranges;
string range bounds, no int cast), `PassportBookNumber` (9 digits, letter + 7 or 8 digits; mask 3 for 8 characters),
`PassportCardNumber` (`C` + 8 digits), `MedicareBeneficiaryIdentifier` (CMS position layout, `formatted()`
`1EG4-TE5-MK73`). SSN and ITIN mask with the TTIN rule (26 CFR 301.6109-4): `***-**-6789`.
`Name\US\PersonName($family, ?$given, ?$middle, ?$suffix, ?$latin)` (`given` null = one-word name; a middle name or
suffix without a given name is refused) extends `AbstractLatinPersonName` directly (like ES):
secondary identifiers are given, middle, `NameSuffix::icao()` (ordinals as Roman numerals); policy
`TransliterationVariant`. `Name\US\NameSuffix` is a string-backed enum of the 20 AAMVA `DCU` codes (`fromCode()`
case-insensitive, message "Invalid American name suffix."), not a value object: `PersonName::equals()` compares it
with `===`. `Name\US\AbstractAmericanNamePart` maps the ʻokina U+02BB to `'` before the shared parse. Codec keys
`family`, `given` (null for one name), `middle`, `suffix` (AAMVA code), `latin`.

`Document\US\*` DL/ID (plan 3 Task 3): `Jurisdiction` (51 cases, ISO 3166-2:US, the states and DC), `DlIdNumber`
interface, `DlId\AbstractDlIdNumber` (hooks `shapes(): NumberShapes`, `layout()`; final `masked()`/`parse()`/
`fitsLength()`) + one final `<Abbr>DlIdNumber` per covered jurisdiction (30), `DlIdNumberFactory` on
`SubdivisionResolver` over the covered cases only; `classFor()` lists the 21 uncovered cases explicitly (`=> null`), so
a new enum case breaks the `match`. FL, WI, WY add `formatted()` (published hyphen layout) and mask in it.

`Document\CA\*` (federal, plan 3 Task 4): `SocialInsuranceNumber` (9 digits, format only; `formatted()`
`123 456 789`, mask `*** **6 789`; `isTemporaryResident()` = first digit 9), `PassportNumber` (`A123456BC` since
2023-05-10 or `AB123456` before; mask 4, 3 for 8 characters). `Name\CA\PersonName($family, ?$given, ?$latin)` on
`AbstractFamilyGivenPersonName` like GB (one-word name = `given` null), policy `AnyIcaoSpelling`, no `XXX` handling;
codec keys `family`, `given`, `latin`.

`Document\CA\*` provincial (plan 3 Task 5b): `Jurisdiction` (13 cases, ISO 3166-2:CA), interfaces `DriverLicenceNumber`
and `HealthCardNumber`, bases `DriverLicence\AbstractDriverLicenceNumber` (hooks `shapes()`, `layout()`, `stored()`)
and `HealthCard\AbstractHealthCardNumber` (hooks `shapes()`, `stored()`; no layout: no health display form is
published). `stored(normalized, document)` is an instance hook run after the shape match: extra checks (BC MOD-11, ON
MOD 10, QC and NS month/day regexes), and BC/SK licence zero padding to 8 (ON refuses a trailing version code there).
Licences: AB, BC, NS, ON, QC, SK (AB and QC add `formatted()` and mask in
that layout); `DriverLicenceNumberFactory::classFor()` maps the other 7 to `null`. Health cards: all 13;
`HealthCardNumberFactory::classFor()` is non-nullable and the resolver runs over `Jurisdiction::cases()`.
`QcHealthCardNumber` implements `HasSex` (7th character `5`/`6` = female) with `birthDay()`, `birthMonth()`,
`birthYearDigits()`. Ontario (final review of plan 3): `OnHealthCardNumber` is the ten digits, readonly;
`OnHealthCardVersionCode` (1-2 letters, `Maskable`, masks every letter) is a string VO of its own;
`OnHealthCardNumber::fromCard($printed): OnHealthCard` splits the printed form (`OnHealthCard` is a `final readonly`
result with `number()`/`versionCode()`, not a value object, so no codec and no bridge type).

`Document\MX\*` (national, plan 3 Task 6a): `Curp` (`HasBirthDate` + `HasSex`; century from character 17, digit → 19YY,
`A-J` → 20YY; date validated with `checkdate()`; `birthStateCode()` string; check digit format only), `ElectorKey` (INE
clave de elector; two-digit year via `TwoDigitYearDate`, `birthStateCode()`/`sexCode()` raw strings, no `HasSex`),
`Rfc` (natural person, SAT `t_RFC_PF` regex with `Ñ`/`&`, multibyte-aware `masked()`), `SocialSecurityNumber` (NSS, 11
digits, format only). No passport class. `Sex::Unspecified` exists for the CURP `X`. MX state licences (plan 3 Task
6b): `Document\MX\Jurisdiction` (32 cases, ISO 3166-2:MX order, `Cmx` for Mexico City),
`Document\MX\DriverLicenceNumber`, `Document\MX\DriverLicence\{AbstractDriverLicenceNumber, MexDriverLicenceNumber}`
and `Document\MX\DriverLicenceNumberFactory` (CA pattern, resolver document "Mexican driver's licence"). Only MX-MEX
(12 digits) is covered, so `fromNumber()` reports any 12-digit number as MX-MEX; the base's `masked()` always keeps 4
(no covered shape is 8 characters or shorter: restore the CA `<= 8 ? 3 : 4` rule, with a test, when one is added).
`Name\MX\PersonName(?$first, ?$second, $given, ?$latin)` extends `AbstractLatinPersonName` like ES (parts on
`AbstractMexicanNamePart`: `/` separator, `.` after or between letters, both omitted in the ICAO form); no surnames →
the given names are the primary identifier (`JUAN`); policy `TransliterationVariant`; codec keys `first`, `second`,
`given`, `latin` (`first` and `second` nullable).

`Document\BR\*` (national, plan 3 Task 7a): `Cpf` (Receita "DV módulo 11": weights 1-9 then 0-9 from the left, `sum %
11 % 10`; dots stripped; `fiscalRegion()` = 9th digit string; `formatted()` `XXX.XXX.XXX-XX`; repeated digits
accepted), `VoterRegistrationNumber` (TSE art. 36: 5-12 digits left-padded to 12, state code `01`-`28` by regex,
`stateCode()`/`isAbroad()`, check digits format only), `Nis` (11 digits, ANS PIS/PASEP check: weights
`[3,2,9,8,7,6,5,4,3,2]`, `11 - r`, ≥10 → 0; `formatted()`), `Cns` (15 digits, ANS check reduced to `Σ d·(15..1) ≡ 0 mod
11` plus all-zero refusal), `DriverLicenceNumber` (CNH registro nacional, `NumberShapes('[0-9A-Z]{9}[0-9]{2}')`),
`PassportNumber` (`[A-Z]{2}[0-9]{6}`, `series()`, masks 3). BR RG (plan 3 Task 7b): `Document\BR\Jurisdiction` (27
cases, ISO 3166-2:BR code order, `Df` for the Distrito Federal; not used by `VoterRegistrationNumber`, whose TSE codes
are not one-to-one: `28` is abroad), `Document\BR\IdentityCardNumber`,
`Document\BR\IdentityCard\{AbstractIdentityCardNumber, SpIdentityCardNumber}` and
`Document\BR\IdentityCardNumberFactory` (CA HealthCard pattern: abstract `stored()` hook for the check and the canonical form, masks the last 4 but at most
the significant length (without the leading padding zeros) − 1, so a padded short RG never shows every real digit; `SpIdentityCardNumber::fromNumberAndCheckCharacter()` for a card
without a printed check character; dots stripped in the base and, for the resolver's length messages, in the factory;
resolver document "Brazilian identity card"). Named `IdentityCard…`, not `Rg…`, like the other English role names
(`DriverLicenceNumber`, `VoterRegistrationNumber`). Only BR-SP is covered. No `Sex` change.
`Name\BR\PersonName($family, $given, ?$latin)` on `AbstractFamilyGivenPersonName` (both required), parts on the plain
`AbstractLatinNamePart` (no extra punctuation), policy `AnyIcaoSpelling` (PF SINPA abbreviation); codec as IT (`given`
required).

`Document\CN\*` (plan 4): `CitizenIdentityNumber` (`Gb11643`; `HasBirthDate`, `HasSex` from the odd/even sequence code,
`administrativeDivisionCode()` string; lower-case `x` stored `X`; the 15-digit number refused by length; also the Hong
Kong/Macao/Taiwan resident permit number, address codes 810000/820000/830000), `ForeignerPermanentResidenceNumber` (2023
card, `Gb11643`, first digit 9, `applicationPlaceCode()`, `nationalityCode()`, `HasBirthDate`, no `HasSex`),
`LegacyForeignerPermanentResidenceNumber` (3 letters + 12 digits, format only), `OrdinaryPassportNumber` (named for the
ordinary `E`/`G` series only), `ExitEntryPermitToHongKongMacauNumber` (`C`), `ExitEntryPermitToTaiwanNumber` (`L`/`T`).
No driving licence class: the licence number is the citizen identity number.
`Name\CN\PersonName($family, ?$given, $latin)` on `RequiredLatin`; parts on `AbstractChineseNamePart` (`\p{sc=Han}` plus
U+00B7 between words; U+30FB and U+2022 folded to U+00B7).

`Document\JP\*` (plan 4): `MyNumber` (総務省令 Art. 5 check digit verified), `BasicPensionNumber`, `ResidenceCardNumber`,
`PassportNumber` (all format only), `DriverLicenceNumber` (12 digits from the NPA notice;
`publicSafetyCommissionCode()`, `yearSymbol()`, `serialNumber()`, `reissueSymbol()` strings; check digit not verified).
`Name\JP\PersonName(family, given, familyReading, givenReading, ?latin)`: not on a shared base (four parts), the
derived form is `Name\JP\PassportHepburn` (internal, MOFA table; katakana folded to hiragana for the derivation only),
policy `AnyIcaoSpelling`; readings on `AbstractJapaneseReading` (hiragana, katakana, `ー` U+30FC listed explicitly),
parts on `AbstractJapaneseNamePart` (`\p{sc=Han}`, kana, `ー`, `〆`). Codec keys `family`, `given`, `familyReading`,
`givenReading`, `latin` (override only).

`Document\KR\*` (plan 4): `ResidentRegistrationNumber` and `ForeignerRegistrationNumber` (`KoreanRegistrationNumber`;
`HasBirthDate`, `HasSex`; the resident class refuses 7th digits 5-8, the foreigner class 1-4, 9 and 0),
`MultipleEntryPassportNumber` (`M` series only), `DriverLicenceNumber` (12 digits or the region name + ten
digits; `regionCode()`/`formatted()` for the numbered form). `Name\KR\PersonName` mirrors CN (Hangul, `·`, hyphen, space
between words).

`Document\IN\*` (plan 4): `AadhaarNumber` (`Checksum::verhoeff()`), `PermanentAccountNumber`
(`holderType(): PanHolderType`), `PassportNumber` (8 ASCII letters/digits), `AbhaNumber`. Two name families: `Name\IN\*`
(Latin: `GivenName`, optional `Surname`, `PersonName` on `AbstractLatinPersonName`, `IndianFullName` internal helper)
and `Name\IN\Devanagari\*` (own `PersonName` with required Latin form, `\p{sc=Devanagari}` plus ZWJ/ZWNJ between two
letters; the Devanagari digits and the danda are not name characters).

`Document\ID\*` (plan 4): `NationalIdentityNumber` (NIK; `HasSex` with the woman's day +40, `regionCode()`,
`birthDay()`, `birthMonth()`, `birthYearDigits()`; date validated through `TwoDigitYearDate`), `TaxpayerNumber` (15 or
16 digits, all zeros refused, `isFifteenDigits()`, `formatted()` for 15 digits), `FamilyCardNumber`.
`Name\ID\PersonName` is one `FullName` (`wordWithAbbreviationDots()`; nothing enforced from Permendagri 73/2022) plus
the required Latin form.

`Document\SA\*` (plan 4): `AbstractIdentityNumber` (ten digits, `@internal`) with `NationalIdentityNumber` (first digit
1) and `IqamaNumber` (first digit 2); the driving licence carries the same number (no class).
`Name\SA\PersonName(given, ?father, ?grandfather, ?family, latin)` (own layout, parts on `AbstractArabicNamePart`:
`\p{sc=Arabic}` letters, harakat U+064B-0652 and U+0670 after a letter, tatweel U+0640 removed).
