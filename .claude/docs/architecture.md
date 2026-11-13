# Architecture

deptrac forbids every framework namespace (Doctrine, Symfony, Illuminate, yii, CodeIgniter, Cycle, Spiral), as in the
core.

| Area | Purpose |
|---|---|
| `Contract\`, `Exception\`, `Sex` | Shared interfaces, the three exception families, the sex enum |
| `Internal\` | `@internal` helpers: `Checksum`, `Mask`, `Digits`, `UnicodeText`/`Text`, `NumberFormat` (character class + length range of a licence), `SubdivisionResolver` (one match / none / ambiguous over an enum of subdivisions), `PayloadFields` (codec payload validation) |
| `Icao\PersonName` | MRZ-style Latin name (`A-Z`, `-`, `<`; `<<` between primary and secondary), `mrzField()` truncation |
| `Icao\Transliterator` | National name part → ICAO Latin (`toLatin()` default, `variants()` all spellings) |
| `Icao\Table\*` | `@internal` `TransliterationTable` per script (LatinMultinational, Cyrillic); add a class + register it in the `Transliterator` constructor for a new script |
| `Name\AbstractNamePart` | Base of every national name part: `letters()` (regex class), `label()` (message), `innerSeparators()` (default `-' `) |
| `Name\LatinSpelling` | `@internal`: finds a Latin override among the ICAO spellings of a name's forms (hyphen ≡ `<`, capped at 4096) |
| `Name\LatinOverridePolicy` | `@internal` abstract: `resolve(?override, derived)` (template method, drops the derived form); subclasses `AnyIcaoSpelling` (RU) and `TransliterationVariant` (AU, via `LatinSpelling`) |
| `Name\PersonName` | Interface of a national full name: `latin(): Icao\PersonName`, `full()` |
| `Name\RU\*` | `AbstractRussianNamePart` (letters, word pattern), `FamilyName`, `GivenName`, `Patronymic`, `PersonName` (+ `short()`, `initialsFirst()`), `PersonNameCodec`; `@internal RussianTransliteration` (drops Ь, then `Icao\Transliterator`) |
| `Document\RU\*` | Number VOs (`Snils`, `Inn`, `InternalPassportNumber`, `PassportIssuingUnitCode`, `InternationalPassportNumber`, `DriverLicenceNumber`, `OmsPolicyNumber`): `final`, extend `AbstractStringValueObject`, implement `Maskable`, store digits only, `formatted()`; enum `DriverLicenceCategory`. No `Region` (no published ISO mapping, RU.md) |
| `Codecs` | `Codecs::all()`: every payload codec, for `new ValueObjectMapper(Codecs::all())`; add each new composite's codec here |

The transliterator picks the one table whose `script()` covers every letter of the input; letters from two tables
→ "mixed scripts", none → "no ICAO transliteration".

`Document\AU\*`: `Jurisdiction` (enum, ISO 3166-2), `DriverLicenceNumber` interface, `AbstractDriverLicenceNumber` + one final class per jurisdiction in `DriverLicence\`, `DriverLicenceNumberFactory`, `TaxFileNumber`, `MedicareCardNumber`. `Name\AU\*` mirrors `Name\RU\*`.

`Document\DE\*`: `IdentityCardNumber` (+ `authorityCode()`), `PassportNumber`, `TaxIdentificationNumber` (format
only), `PensionInsuranceNumber` (VKVV checksum; fields as strings, no `HasBirthDate`/`HasSex`). `Name\DE\*` mirrors
`Name\AU\*` (Latin base, `TransliterationVariant`).

`Document\FR\*`: `Nir` (`HasSex`; DSN field rules; key format only; `birthMonth()` nullable, `birthPlaceCode()`
string), `LegacyIdentityCardNumber` (12-digit card issued until 2021; the 2021 card is not covered), `PassportNumber`
(`99AA99999`), `TaxIdentificationNumber` (SPI, format only), `DriverLicenceNumber` (12 digits, format only).
`Name\FR\*`: `AbstractFrenchNamePart` overrides `letters()` with the 2014 civil-status circular's list (no `\p{Latin}`).
