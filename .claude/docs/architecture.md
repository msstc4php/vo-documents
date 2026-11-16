# Architecture

deptrac forbids every framework namespace (Doctrine, Symfony, Illuminate, yii, CodeIgniter, Cycle, Spiral), as in the
core.

| Area | Purpose |
|---|---|
| `Contract\`, `Exception\`, `Sex` | Shared interfaces, the three exception families, the sex enum |
| `Internal\` | `@internal` helpers: `Checksum`, `Mask`, `Digits`, `UnicodeText`/`Text`, `NumberFormat` (character class + length range of a licence), `NumberShapes` (several fixed shapes, length derived from the shape: US DL/ID; shares `Digits::upperCasedOfLength()` with `NumberFormat`), `SubdivisionResolver` (one match / none / ambiguous over an enum of subdivisions), `PayloadFields` (codec payload validation), `Optional` (`equal()` of two optional value objects), `TwoDigitYearDate` (day/month valid in some century: DVLA, CHI, IT codice fiscale) |
| `Icao\PersonName` | MRZ-style Latin name (`A-Z`, `-`, `<`; `<<` between primary and secondary), `mrzField()` truncation |
| `Icao\Transliterator` | National name part → ICAO Latin (`toLatin()` default, `variants()` all spellings) |
| `Icao\Table\*` | `@internal` `TransliterationTable` per script (LatinMultinational, Cyrillic); add a class + register it in the `Transliterator` constructor for a new script |
| `Name\AbstractNamePart` | Base of every national name part: `letters()` (regex class), `label()` (message), `innerSeparators()` (default `-' `) |
| `Name\LatinSpelling` | `@internal`: finds a Latin override among the ICAO spellings of a name, word by word (`Name\IdentifierSpellings`: variants per word + national separators; hyphen ≡ `<`; cap 4096 per word) |
| `Name\LatinOverridePolicy` | `@internal` abstract: `resolve(?override, derived)` (template method, drops the derived form); `withoutDerivedForm()` (refuses, except `AnyIcaoSpelling` with an override); subclasses `AnyIcaoSpelling` (RU, GB, CA) and `TransliterationVariant` (AU, DE, FR, IT, ES, NL, CH, TR, US; via `LatinSpelling`) |
| `Name\PersonName` | Interface of a national full name: `latin(): Icao\PersonName`, `full()` |
| `Name\Latin\*` | `AbstractLatinNamePart` (`\p{Latin}` part); `@internal`: `LatinPersonNameBuilder` (derived form, spellings per word for `TransliterationVariant`), `AbstractLatinPersonName` (effective Latin form + kept override, hook `overridePolicy()`), `AbstractFamilyGivenPersonName<TFamily, TGiven>` (public `family`/`given`, `full()`, `equals()`, JSON view; AU DE FR IT TR NL CH GB), `AbstractFamilyGivenPersonNameCodec<TName>` (keys `family`/`given`/`latin`; hooks `shape()`, `create()`) |
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
parts (primary identifier joined by a space); codec keys `first`, `second`, `given`, `latin`.

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
`SubdivisionResolver` over the covered cases only; `classFor()` lists the 21 uncovered cases explicitly (`=> null`), so a
new enum case breaks the `match`. FL, WI, WY add `formatted()` (published hyphen layout) and mask in it.

`Document\CA\*` (federal, plan 3 Task 4): `SocialInsuranceNumber` (9 digits, format only; `formatted()`
`123 456 789`, mask `*** **6 789`; `isTemporaryResident()` = first digit 9), `PassportNumber` (`A123456BC` since
2023-05-10 or `AB123456` before; mask 4, 3 for 8 characters). `Name\CA\PersonName($family, ?$given, ?$latin)` on
`AbstractFamilyGivenPersonName` like GB (one-word name = `given` null), policy `AnyIcaoSpelling`, no `XXX` handling;
codec keys `family`, `given`, `latin`.

`Document\CA\*` provincial (plan 3 Task 5b): `Jurisdiction` (13 cases, ISO 3166-2:CA), interfaces `DriverLicenceNumber`
and `HealthCardNumber`, bases `DriverLicence\AbstractDriverLicenceNumber` (hooks `shapes()`, `layout()`, `stored()`)
and `HealthCard\AbstractHealthCardNumber` (hooks `shapes()`, `stored()`; no layout: no health display form is
published). `stored(normalized, document)` is an instance hook run after the shape match: extra checks (BC MOD-11, ON
MOD 10, QC and NS month/day regexes), BC/SK licence zero padding to 8, and the ON version code split (it sets a private
write-once property guarded by `isset()`). Licences: AB, BC, NS, ON, QC, SK (AB and QC add `formatted()` and mask in that layout);
`DriverLicenceNumberFactory::classFor()` maps the other 7 to `null`. Health cards: all 13;
`HealthCardNumberFactory::classFor()` is non-nullable and the resolver runs over `Jurisdiction::cases()`.
`QcHealthCardNumber` implements `HasSex` (7th character `5`/`6` = female) with `birthDay()`, `birthMonth()`,
`birthYearDigits()`; `OnHealthCardNumber::versionCode(): ?string`.
