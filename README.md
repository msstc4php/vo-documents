# msstc4php/vo-documents

Value objects for identity documents and personal names of the world's largest economies, with ICAO Doc 9303 Latin
names. Built on [`msstc4php/vo-base`](https://github.com/msstc4php/vo-base); PHP >= 8.4. Today: the Russian Federation,
Australia, Germany, France, Italy, Spain, the Netherlands, Switzerland, the United Kingdom and Türkiye.

Every format, checksum and table cites its official source on the class (`@see`); the full notes, with quotes and the
places where the sources disagree with the obvious guess, are in [`docs/sources/`](docs/sources).

## Install

Not on Packagist yet; register the GitHub repositories:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/msstc4php/vo-base" },
        { "type": "vcs", "url": "https://github.com/msstc4php/vo-documents" }
    ],
    "require": {
        "msstc4php/vo-documents": "^0.1"
    }
}
```

Runtime requirements: `msstc4php/vo-base ^1.1.1`, `ext-mbstring`, `symfony/polyfill-intl-normalizer`. No framework.
vo-base 1.1.1 (the mapper no longer reports interfaces as supported) must be tagged before this package is released.

## Document numbers

```php
use MSSTC4PHP\ValueObject\Documents\Document\RU\Snils;

$snils = Snils::from('112 233 445 95');   // spaces, hyphens, tabs, NBSP are stripped
(string) $snils;                          // "11223344595"  (the full value, always)
$snils->formatted();                      // "112-233-445 95"
$snils->masked();                         // "***-***-*45 95"
Snils::tryFrom('112-233-445-96');         // null (checksum)
```

`from()` throws a subclass of `InvalidValueObjectException` (`InvalidDocumentNumberException`) for invalid input. The
message names the document and the length or the reason ("Invalid SNILS (14 characters).", "Invalid SNILS
checksum."); **it never contains the input or any part of it**.

`__toString()` and `jsonSerialize()` return the full canonical value. Masking is an explicit, separate call,
`masked()` (interface `Contract\Maskable`). No masking rule is official unless the class says so; the default keeps the
last four characters, and a number of 8 characters or fewer keeps three (CH identity document, GB Northern Ireland
licence). The AU classes keep three (their numbers are at most 10 characters long, `docs/sources/AU.md`).

| Class | Document | Check | Source |
|---|---|---|---|
| `Document\RU\Snils` | SNILS, 11 digits | control number mod 101 (above 001-001-998) | SFR Order No. 1463 of 17.11.2025; PFR checking rules |
| `Document\RU\Inn` | INN of an individual, 12 digits | format only (no check digit verified) | FNS Order ЕД-7-14/559@ (algorithm not published) |
| `Document\RU\InternalPassportNumber` | Internal passport, series + number, 10 digits | shape only | Government Decree No. 2267 of 23.12.2023 |
| `Document\RU\PassportIssuingUnitCode` | Issuing unit code, `NNN-NNN` | shape only | MVD Order No. 773 of 16.11.2020 |
| `Document\RU\InternationalPassportNumber` | Passport for travel abroad, 9 digits | shape only | MVD Orders No. 186 (2021), No. 996 (2019) |
| `Document\RU\DriverLicenceNumber` | Driving licence, `00 00 000000` | shape only | MVD Order No. 365 of 13.05.2009 |
| `Document\RU\DriverLicenceCategory` | Licence categories (enum) | n/a | MVD Order No. 365; Federal Law 196-FZ |
| `Document\RU\OmsPolicyNumber` | OMS policy (ЕНП), 16 digits | HL7 Mod10 (Luhn) | FFOMS Order No. 79 of 07.04.2011 |
| `Document\AU\TaxFileNumber` | Tax file number, 9 digits | format only (no check digit verified) | ATO (algorithm not public) |
| `Document\AU\MedicareCardNumber` | Medicare card, 10 digits | format only (no check digit verified) | Australian Digital Health Agency (algorithm not public) |
| `Document\AU\DriverLicence\{Act,Nsw,Nt,Qld,Sa,Tas,Vic,Wa}DriverLicenceNumber` | Licence number per state or territory | shape only | Home Affairs DVS field rules |
| `Document\AU\Jurisdiction` | The eight states and territories (enum, ISO 3166-2:AU) | n/a | ISO 3166-2:AU |

### European documents

"Checksum verified" means the algorithm is quoted from an official source (given in the last column and on the class);
"format only" means the shape is checked and the check digit, if the number has one, is **not** verified because no
official algorithm is published. A number with the right shape is accepted, never refused for an unverifiable check.
"Encoded data" lists what the official source defines inside the number and what the class exposes.

| Class | Document | Check | Encoded data | Source |
|---|---|---|---|---|
| `Document\DE\IdentityCardNumber` | Personalausweis serial number, 9 characters | format only (no check digit of its own) | `authorityCode()` | PAuswG § 2 Abs. 8; PAuswV Anlage 3 |
| `Document\DE\PassportNumber` | Reisepass serial number, 9 characters | format only | none | PassG § 4; PassV Anlage 11 |
| `Document\DE\TaxIdentificationNumber` | Steuer-ID, 11 digits | format only | none | StIdV § 1; AO § 139b |
| `Document\DE\PensionInsuranceNumber` | Rentenversicherungsnummer, `NN DDMMYY L SS C` | checksum verified (VKVV § 2) | fields as strings, no typed date or sex | VKVV § 2 |
| `Document\FR\Nir` | NIR (numéro de sécurité sociale), 15 characters | key format only | `HasSex`, birth year digits, `birthMonth()`, `birthPlaceCode()` | INSEE; DSN cahier technique |
| `Document\FR\LegacyIdentityCardNumber` | CNI issued until 2021, 12 characters (digits; `2A`/`2B` in a Corsican place code) | format only | `issueYearDigits()`, `issueMonth()` (not validated), `issuePlaceCode()`, `serialNumber()` | NOR INTD0000001C |
| `Document\FR\PassportNumber` | Passport, `99AA99999` | format only | none | France Titres-ANTS |
| `Document\FR\TaxIdentificationNumber` | Numéro fiscal (SPI), 13 digits | format only | none | OECD TIN note (DGFiP) |
| `Document\FR\DriverLicenceNumber` | Permis de conduire, 12 digits | format only | none | France Titres-ANTS |
| `Document\IT\FiscalCode` | Codice fiscale, 16 characters | checksum verified (DM 23.12.1976 art. 7) | `HasSex`, birth year digits, month, day, `birthPlaceCode()`; omocodia read | DM 23 dicembre 1976 |
| `Document\IT\PassportNumber` | Passport, 2 letters + 7 digits | format only | none | DM 23 marzo 2010 n. 303/13, 303/14 |
| `Document\ES\Dni` | DNI, 8 digits + letter | checksum verified (mod 23) | none | Ministerio del Interior; RD 1065/2007 |
| `Document\ES\Nie` | NIE, `X/Y/Z` + 7 digits + letter | checksum verified; pre-2008 `X` + 8 digits: format only | none | Ministerio del Interior; Orden INT/2058/2008 |
| `Document\ES\SocialSecurityNumber` | Número de afiliación, 12 digits | format only | `provinceCode()` (not range-checked) | TGSS, Sistema RED |
| `Document\NL\Bsn` | Burgerservicenummer, 9 digits | checksum verified (elfproef) | none | Logisch Ontwerp BSN 2025.Q4 |
| `Document\NL\IdentityDocumentNumber` | Passport / ID card / emergency document, 9 characters | format only | none | RvIG Kenmerkenbrochure |
| `Document\CH\AhvNumber` | AHV-Nummer, 13 digits | checksum verified (BSV WL VA/IK Anhang 7) | none | BSV WL VA/IK |
| `Document\CH\IdentityDocumentNumber` | Passport / identity card, 8 characters | format only | none | SR 143.111 Art. 16; fedpol |
| `Document\CH\DriverLicenceNumber` | Führerausweis, 12 or 15 digits | format only | `faberPin()`, `sequenceNumber()` (12 digits only) | ASTRA Weisungen 2023 |
| `Document\GB\NationalInsuranceNumber` | NINO, `AB 12 34 56 C` | prefix rules (no check digit exists) | none | HMRC NIM39110 |
| `Document\GB\PassportNumber` | Passport, 9 digits | format only | none | HMPO Basic Passport Checks |
| `Document\GB\GreatBritainDriverLicenceNumber` | DVLA licence driver number, 16 characters | date validated; check characters not verified | `HasSex`, birth year digits, month, day | DVLA INS57P |
| `Document\GB\NorthernIrelandDriverLicenceNumber` | DVA licence driver number, 8 digits | format only | none | nidirect (DVA) |
| `Document\GB\NhsNumber` | NHS number (England, Wales), 10 digits | checksum verified (Modulus 11) | none | NHS Data Dictionary |
| `Document\GB\HealthAndCareNumber` | H&C number (Northern Ireland), 10 digits | checksum verified; range 320000001-399999999 | none | NHS Data Dictionary |
| `Document\GB\ChiNumber` | CHI number (Scotland), 10 digits | date validated; check digit format only | `HasSex`, birth year digits, month, day | NHS Data Dictionary; PHS |
| `Document\TR\IdentityNumber` | T.C. Kimlik No, 11 digits | format only | none (NVİ: encodes nothing) | NVİ; Nüfus Hizmetleri Yönetmeliği Madde 4 |
| `Document\TR\PassportNumber` | Passport, letter + 8 digits | format only | none | Council of the EU PRADO |

Names: `Name\<CC>\FamilyName`, `GivenName` and `PersonName` for DE, FR, IT, ES, NL, CH, GB and TR, like the RU and AU
ones. Spain's `PersonName` has two surnames (`first`, optional `second`); in the Dutch one the prefixes (`van der`) are
part of the family name; the Swiss, Dutch and British ones accept a person without a given name.

Contracts: `Contract\Maskable` (every number above), `Contract\Subdivision` and `Contract\IssuedBySubdivision` (AU
jurisdictions and licences). `Contract\HasSex` and the `Sex` enum are implemented by the documents that encode a sex
(`Nir`, `FiscalCode`, the DVLA licence number, `ChiNumber`). `Contract\HasBirthDate` is public API that no class
implements yet: every encoded birth date of these countries lacks a century, so the classes expose the year digits,
month and day instead, and impossible dates (30 February) are refused.

### AU driving licences and the jurisdiction factory

```php
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;

$licence = DriverLicenceNumberFactory::fromNumberIn('01234567', Jurisdiction::Nsw);   // NswDriverLicenceNumber
DriverLicenceNumberFactory::jurisdictionsMatching('01234567');                       // [Act, Nsw, Nt, Qld, Tas, Vic]
DriverLicenceNumberFactory::fromNumber('01234567');                                  // AmbiguousJurisdictionException
```

`fromNumber()` never picks one of several jurisdictions silently: it throws `AmbiguousJurisdictionException`, whose
message lists the ISO codes in enum order, or `InvalidDocumentNumberException` when none fits ("(fits no jurisdiction)"
when the length fits some jurisdiction, otherwise the length). `AmbiguousJurisdictionException` extends
`InvalidValueObjectException` like every input error: the bare number is not a valid input for `fromNumber()`, so a
caller that catches invalid input catches it too, and `jurisdictions()` tells which `fromNumberIn()` calls would work.
**With the formats published today every valid number fits two or more jurisdictions, so `fromNumber()` always throws
for AU**; use `fromNumberIn()` when the issuer is known (see `docs/sources/AU.md`).

## Personal names

A national name part (`Name\RU\FamilyName`, `GivenName`, `Patronymic`, and `FamilyName`, `GivenName` of `Name\AU`,
`Name\DE`, `Name\FR`, `Name\IT`, `Name\ES`, `Name\NL`, `Name\CH`, `Name\GB`, `Name\TR`) is
NFC-normalised, trimmed, has inner whitespace runs collapsed to one space, and keeps its letter case. Limits: 100
characters per part. Russian parts accept only the 33 Russian letters; a Cyrillic name typed with Latin look-alikes
(Latin `a`, `o`, `e`, `c`) is refused. A compatibility character (its NFKC form differs: fullwidth `Ｓｍｉｔｈ`, `ª`, the
ligature `ﬁ`) is refused in every country, because it would be a second stored spelling of the same name; the ones an
ICAO table lists itself (`Ĳ`, `Ŀ`) are kept. Messages say "(N characters)" for an empty or too long part,
"(compatibility character)", or "(invalid character)" for a letter or separator that is not allowed where it stands.

```php
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\{FamilyName, GivenName, Patronymic, PersonName};

$name = new PersonName(FamilyName::from('Иванов'), GivenName::from('Иван'), Patronymic::from('Иванович'));
$name->full();                  // "Иванов Иван Иванович"
$name->short();                 // "Иванов И. И."
(string) $name->latin();        // "IVANOV<<IVAN"
$name->latin()->mrzField();     // "IVANOV<<IVAN" padded with "<" to 39 characters
```

### ICAO Latin name

`Icao\PersonName` is the canonical Latin form of ICAO Doc 9303 Part 3 §4.6: characters `A-Z`, `-` and `<`; `<<`
separates the primary identifier from the secondary ones and a single `<` stands for a space; maximum 255 characters.
Hyphens are kept (`RIMSKY-KORSAKOV`) and become `<` only in `mrzField()`.

```php
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName;

$latin = PersonName::fromParts('van der Berg', ['Jan', 'Pieter']);
(string) $latin;             // "VAN<DER<BERG<<JAN<PIETER"
$latin->visualZone();        // "VAN DER BERG, JAN PIETER"
$latin->mrzField(39);        // padded with "<" or truncated per Doc 9303 Part 4 §4.2.3
```

`Icao\Transliterator` turns a national name part into its ICAO spelling (`toLatin()`, or `variants()` for every
spelling the table allows). Two rules that follow the sources, not the obvious guess:

- The Russian derived Latin name **omits the patronymic** (`IVANOV<<IVAN`): MVD Orders 996 and 186 do not write it in
  Latin in the passport.
- The soft sign **Ь is omitted** (`Игорь` is `IGOR`): the ICAO table 6.B has no row for it, the MVD table has none
  either. The hard sign Ъ is `IE`.
- A Latin letter **missing from table 6.A is spelled as its base letter** (`Nguyễn` is `NGUYEN`, Romanian `Ș`/`Ț` are
  `S`/`T`): the package's fallback, not an ICAO rule (`docs/sources/ICAO.md`). A letter with no base in the table
  (`ƒ`) is still refused.
- Transliteration tables and override rules change **only in a major version**: a stored override is checked again
  when it is read, so a narrower rule would make stored names unreadable.

### Canonical form and the override rule

A person's Latin name is derived from the national one. A different official spelling (for example one printed in an
existing passport) can be given as an override. `Icao\PersonName::from()` normalises it first: letter case, spaces
next to a `<` (`IVANOV << IVAN`) and the trailing `<` padding of an MRZ field (`IVANOV<<IVAN<<<<`). What is accepted
then depends on the country's sources:

| Country | Accepted override | Why |
|---|---|---|
| RU | any valid ICAO name (`IVANOVA<<YULIYA` for Иванова Юлия, where the table gives `IVANOVA<<IULIIA`) | MVD Orders 996/186 (§32, §140) write the name "так, как это указано в подтверждающем документе" |
| AU, DE, FR, IT, ES, NL, CH, TR | only an ICAO spelling of the name (`MULLER<<ZOE` for Müller Zoë); `<` for a hyphen is stored as the hyphen (policy `TransliterationVariant`) | Latin-script names: no source lets a passport keep another spelling, so the table spellings are the only ones |
| GB | any valid ICAO name (policy `AnyIcaoSpelling`) | HM Passport Office lets a passport carry an evidenced spelling (`docs/sources/GB.md`) |

An override that is the derived form (also when it writes a hyphen as `<`) is not kept, so `latinOverride()` is null
and the name equals the one built without it. Text that is no ICAO name (`IVANOV1<<IVAN`) is refused by
`Icao\PersonName::from()` in every country.

```php
$name = new PersonName(
    FamilyName::from('Иванова'),
    GivenName::from('Юлия'),
    null,
    IcaoPersonName::from('Ivanova << Yuliya'),   // normalised to IVANOVA<<YULIYA
);
$name->latin();           // IVANOVA<<YULIYA
$name->latinOverride();   // the same; null when the Latin form is derived
```

The storage payload of a composite name stores only the override (`latin: null` otherwise), never the derived form.

## Bridges

The value objects are plain `vo-base` value objects, so every
[`vo-base-*`](https://github.com/msstc4php/vo-base#framework-integration) bridge handles them without extra code:

| Need | Where |
|---|---|
| Validation, request input, route parameters | Works as is in every vo-base bridge (Symfony bundle, Laravel, Yii 2, CodeIgniter 4, Spiral) |
| Storage of string numbers and name parts | As is in Laravel (`AsValueObject`), Yii 2 (behavior), CodeIgniter 4 (casts) and Cycle (typecast) |
| Storage and validation of composite names | Only with a mapper built from `Codecs::all()`, handed to the cast, rule or behavior (next section) |
| Doctrine ORM / MongoDB ODM | [`msstc4php/vo-documents-doctrine`](https://github.com/msstc4php/vo-documents-doctrine): one type per class, named `vod_<cc>_<snake_name>` |

Typed properties, route parameters and casts must name the concrete class (`NswDriverLicenceNumber`, not the
`Document\AU\DriverLicenceNumber` interface): the mapper cannot build an interface, so since vo-base 1.1.1 it reports
it as unsupported (a configuration error, not a 500 on request input). The interface is the type of
`DriverLicenceNumberFactory` results.

### Composite names need the codecs

Document numbers and name parts are strings and need nothing. The composite names (`Name\<CC>\PersonName` of every
country: RU, AU, DE, FR, IT, ES, NL, CH, GB, TR) are read through a payload codec, so the mapper that the bridge uses
must be built with `Codecs::all()`:

```php
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;

$mapper = new ValueObjectMapper(Codecs::all());
```

Binding per bridge. The shared container binding is read by request input and route parameters, by the Yii 2
validator, and in Spiral by the caster, the interceptor and the checker; some storage and validation classes build
their own mapper, so give them yours.

```php
// Laravel: the container binding covers request input and route parameters.
$this->app->singleton(ValueObjectMapper::class, static fn () => new ValueObjectMapper(Codecs::all()));

// Eloquent casts and the validation rule do NOT read it; hand them the mapper.
final readonly class AsRuPersonName implements Castable
{
    public static function castUsing(array $arguments): AsValueObject
    {
        return new AsValueObject(PersonName::class, new ValueObjectMapper(Codecs::all()));
    }
}
// casts(): 'name' => AsRuPersonName::class
// rule:    new ValidValueObject(PersonName::class, mapper: new ValueObjectMapper(Codecs::all()))

// Spiral (inside a Bootloader class listed after ValueObjectBootloader; the Cycle typecast reads the same container)
public function defineSingletons(): array
{
    return [ValueObjectMapper::class => static fn () => new ValueObjectMapper(Codecs::all())];
}

// Yii 2: the container binding covers the validator, ActiveRecord model values and action parameters
Yii::$container->setSingleton(ValueObjectMapper::class, static fn () => new ValueObjectMapper(Codecs::all()));
// ValueObjectAttributeBehavior ignores the container; pass the mapper to it:
'mapper' => new ValueObjectMapper(Codecs::all()),
```

Imports: `MSSTC4PHP\ValueObject\Documents\Codecs`, `MSSTC4PHP\ValueObject\Payload\ValueObjectMapper`,
`MSSTC4PHP\ValueObject\Laravel\Casts\AsValueObject`, `MSSTC4PHP\ValueObject\Laravel\Rules\ValidValueObject`.

```yaml
# Symfony (vo-base-bundle): every PayloadCodec service is autoconfigured and added to the mapper; register the codec
# of every country, the same list as Codecs::all()
services:
    _defaults: { autoconfigure: true }
    MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec: ~
```

```php
// Cycle without a container: one typecast per role, with your mapper
S::TYPECAST_HANDLER => [new ValueObjectTypecast('account', new ValueObjectMapper(Codecs::all())), Typecast::class],
```

CodeIgniter 4 calls cast handlers statically with the core's built-in codecs only, so **composite names are not
supported there** unless the application builds its own mapper; the string classes work.

## Not covered

Collected from the source notes; each is a deliberate omission with its reason in `docs/sources/`.

- **DE:** Führerschein number (FeV composition without length or check digit); provisional passports and
  identity cards (`docs/sources/DE.md`).
- **FR:** the identity card issued since August 2021 (no official format); the pre-1975 driving licence form
  (`docs/sources/FR.md`).
- **IT:** carta d'identità elettronica number and patente number (no reachable official format); the eleven-digit
  provisional codice fiscale (`docs/sources/IT.md`).
- **ES:** passport number (the official register could not be read); the driving licence number is the DNI or NIE and
  has no class of its own; the NIF of legal persons and minors (`K`, `L`, `M`) (`docs/sources/ES.md`).
- **NL:** rijbewijsnummer (no published format) (`docs/sources/NL.md`).
- **CH:** a per-canton licence format (the licence is federal); the Allianzname as a separate field
  (`docs/sources/CH.md`).
- **GB:** the HMRC Temporary Reference Number, the passport thin-film serial number, the licence issue number
  (`docs/sources/GB.md`).
- **TR:** sürücü belgesi number (a running sequence per province, no format), the identity card serial number
  (`docs/sources/TR.md`).
- **No region value object for RU**: no field encodes a region with a published mapping to ISO 3166-2:RU (`RU.md`).
- **RU driving licences of the 1999 form** (Cyrillic series): expired by 2026 (`RU.md`).
- **RU old-form OMS policies** (series and number) and AU **11-digit Medicare individual numbers**.
- **AU driving licence card numbers**: a different number on the same licence, left for a later release (`AU.md`).
- **AU single names (mononyms)**: the constructor requires a given name (`AU.md`).
- **AU 8-digit TFNs of non-individuals**: no official format is published (`AU.md`).

## Limits to know about

- **Format only, check digit not verified:** INN, TFN and Medicare card number. No official algorithm is published
  (FNS says only "по алгоритму, определенному ФНС"; the ATO gives the TFN algorithm to registered developers only;
  the ADHA appendix is not reachable), so a mistyped check digit is accepted. Only checksums quoted from an official
  source are verified: SNILS, OMS policy, DE pension insurance number, IT codice fiscale, ES DNI and NIE, NL BSN, CH
  AHV number, GB NHS and H&C numbers.
- **Not verified against the official PDF:** the 16-digit length of the OMS policy and the FFOMS Order 79 text it is
  checked against (www.ffoms.gov.ru is unreachable from here, the text is quoted from a copy), and the 2 + 7 split of
  the international passport number (no text read states it; `RU.md`).
- **Format only in Europe** (see the table above): DE Steuer-ID (no leading-zero or repeated-digit rule either, those
  are in an unpublished BZSt paper), FR NIR key and numéro fiscal, the identity card, passport and licence numbers of
  every country, ES social security number, TR T.C. Kimlik No (NVİ shares the algorithm only with public bodies; the
  popular formula fails the official PRADO specimens) and the CHI check digit. ES pre-2008 NIE letters are accepted as
  any `A-Z`.
- **Encoded data only where the source defines it:** the DE pension insurance number exposes strings (no century, no
  published rule for unproven birth dates); Italian codice fiscale accepts any omocodia substitution over the seven
  numeric positions and `29` February when the two-digit year is divisible by four (no century is encoded).
- **Name limits by country:** FR names use the 2014 civil-status circular's letter list (`ñ`, `ł`, `ß` refused); NL
  names use the BRP Teletex letters and refuse the `ĳ` ligature (written `ij`); TR names refuse `Q`, `W`, `X` (Law
  1353 art. 3), hyphens and apostrophes; derived `Ö`/`Ü` stay `OE`/`UE` (table 6.A); ES refuses the Catalan middle dot
  `l·l`; IT refuses the comma between given names; GB licence numbers of a holder with no first names would be refused
  (no published rule for them).
- **Source access:** FR passport and licence formats are France Titres-ANTS answers; CH passport length comes from
  the Basel-Stadt passport office and fedpol; TR passport format from the PRADO register; the ES NIF/NIE algorithm and
  several GB texts are cited through unmodified Internet Archive copies of the official page (original URL, archive URL
  and capture date in the source notes).
- Name parts are limited to 100 characters, `Icao\PersonName` to 255 (a very long real name is refused). Three long
  parts can exceed 255 in Latin (an ES name of three 100-character parts): the name is refused with
  "(Latin form too long)", unless the country accepts any ICAO spelling (RU, GB) and an override is given.
- **RU and GB accept an override without a derived form**: when a letter has no ICAO spelling or the derived form is
  too long, the override is kept as given (the passport authority writes a documented spelling); without an override
  the name is refused. An `AnyIcaoSpelling` override is stored as typed: `SMYTH<JONES<<MARY` and `SMYTH-JONES<<MARY`
  are two values. GB drops the `XXX` passport forename of a person with one name (`SMITH<<XXX` is `SMITH`).
- **The transliteration variant cap (4096) is per ICAO word**, so a long list of given names with umlauts is accepted
  with an override; a single word with more than twelve two-way letters is still refused with an override.

## Development

`make check` (php -l, PHPStan max, cs-fixer, Rector, deptrac), `make test`, `make infection`. See `CLAUDE.md`.
