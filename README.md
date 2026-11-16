# msstc4php/vo-documents

Value objects for identity documents and personal names of the world's largest economies, with ICAO Doc 9303 Latin
names. Built on [`msstc4php/vo-base`](https://github.com/msstc4php/vo-base); PHP >= 8.4. Today: the Russian Federation,
Australia, Germany, France, Italy, Spain, the Netherlands, Switzerland, the United Kingdom, Türkiye and the United
States (names and federal documents).

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
| `Document\US\SocialSecurityNumber` | SSN, `123-45-6789` | SSA rules: area not 000/666/900-999, group not 00, serial not 0000 (no check digit exists); masked `***-**-6789` (26 CFR 301.6109-4) | none | SSA; IRM 3.13.5.21 |
| `Document\US\Itin` | ITIN, `9NN-NN-NNNN` | first digit 9, digits 4-5 in the IRS ranges; masked `***-**-1234` (26 CFR 301.6109-4) | none | IRM 3.13.5.22 |
| `Document\US\PassportBookNumber` | Passport book, 9 digits or letter + 7/8 digits | format only | none | State Department; USCIS E-Verify |
| `Document\US\PassportCardNumber` | Passport card, `C` + 8 digits | format only | none | USCIS E-Verify |
| `Document\US\MedicareBeneficiaryIdentifier` | MBI, `1EG4-TE5-MK73` | CMS position layout (no check digit exists) | none | CMS |
| `Document\US\DlId\{Al,Az,Co,Ct,Fl,Hi,Ia,Id,Il,In,Ks,Ky,Ma,Md,Me,Mi,Ms,Nd,Ne,Nj,Nm,Ny,Ok,Pa,Ri,Tx,Vt,Wa,Wi,Wy}DlIdNumber` | DL/ID number of 30 states (one class covers a state's licence and ID card where the source says so) | shape only (no check digit verified; HI, KS, MA, NE, VT, WA letters enforced as quoted) | none (the IL, WA, FL and MI name and birth data is not decoded) | state DMV and police publications; USCIS E-Verify RIDE fact sheets |
| `Document\US\Jurisdiction` | The 50 states and DC (enum, ISO 3166-2:US) | n/a | n/a | ISO 3166-2:US |
| `Document\CA\SocialInsuranceNumber` | SIN, `123 456 789` | format only (no check digit verified: no algorithm published) | `isTemporaryResident()` (first digit 9, ESDC) | ESDC; CRA |
| `Document\CA\PassportNumber` | Passport, letter + 6 digits + 2 letters (since 2023-05-10) or 2 letters + 6 digits | format only | none | IRCC |
| `Document\CA\DriverLicence\{Ab,Bc,Ns,On,Qc,Sk}DriverLicenceNumber` | Driver's licence number of 6 jurisdictions (NS and SK also cover the photo ID card): AB `123456-789`, BC and SK 1-8 digits stored zero-padded to 8, NS Master Number `FINCH220867001`, ON 15 alphanumerics, QC `L1531-171274-08` | shape only (no check digit published); NS day and month checked as calendar fields | none (the NS surname and birth fields are not decoded: two-digit year) | Service Alberta RDN; ICBC; Service Nova Scotia; MTO glossary; SAAQ; Government of Saskatchewan, SGI |
| `Document\CA\HealthCard\{Ab,Bc,Mb,Nb,Nl,Ns,Nt,Nu,On,Pe,Qc,Sk,Yt}HealthCardNumber` | Health card number of all 13 jurisdictions (8-12 characters; NT letter `D`/`N`/`H`/`M`/`T` + 7 digits; QC NAM `YUXJ75021213`) | BC leading 9 and MOD-11, ON leading digit 1-9 and MOD 10 verified; QC month (01-12, 51-62) and day checked; the rest format only | ON `versionCode()` (kept apart from the value); QC `HasSex`, birth year digits, month, day (GIU REQ-19-53: a reading aid, not a value to fill a record with) | Ontario Ministry of Health "Province and Territory Codes" and HCV/IHCS specifications; BC Teleplan; Québec MSSS GIU; provincial ministries |
| `Document\CA\Jurisdiction` | The 10 provinces and 3 territories (enum, ISO 3166-2:CA) | n/a | n/a | ISO 3166-2:CA |
| `Document\MX\Curp` | CURP, 18 characters (`HEGJ561231HDFRRN04`) | RENAPO layout: letters/digits per position, sex `H`/`M`/`X`, character 17 `0-9` (born up to 1999) or `A-J` (from 2000), birth date must exist; check digit format only (algorithm not published) | `HasBirthDate` (century from character 17), `HasSex` (`X` → `Sex::Unspecified`), `birthStateCode()` (RENAPO code, not ISO) | RENAPO Instructivo Normativo (DOF 2018, 2021); RENAPO Reglas 2021; INE DERFE |
| `Document\MX\ElectorKey` | Clave de elector of the INE voter card, 18 characters | INE layout: 6 letters, `AAMMDD` (day/month valid in some century), 2 state characters, a sex letter, 3 homoclave characters (no check verified) | birth year digits, month, day; `birthStateCode()` and `sexCode()` as written (no value list published) | INE lineamientos 2021; INE glossary |
| `Document\MX\Rfc` | RFC of a natural person, 13 characters (`HEGJ5612311A2`) | SAT `t_RFC_PF` pattern (`A-Z&Ñ`, month and day ranges, final digit or `A`); homoclave not verified | none | SAT CFDI data types (`tdCFDI.xsd`) |
| `Document\MX\SocialSecurityNumber` | NSS (IMSS), 11 digits | format only (check digit algorithm not published) | none | IMSS norm 3000-001-007 |

Names: `Name\<CC>\FamilyName`, `GivenName` and `PersonName` for DE, FR, IT, ES, NL, CH, GB and TR, like the RU and AU
ones. Spain's `PersonName` has two surnames (`first`, optional `second`); in the Dutch one the prefixes (`van der`) are
part of the family name; the Swiss, Dutch, British, US and Canadian ones accept a person without a given name. The
US one is `Name\US\PersonName($family, ?$given, ?$middle, ?$suffix)` (`given` null for a one-word name), the suffix a
`Name\US\NameSuffix` (the AAMVA list `JR`, `SR`, `1ST`/`I` … `9TH`/`IX`), written last among the secondary
identifiers of the ICAO form, ordinals as Roman numerals (`SMITH<<JOHN<MICHAEL<III`). The Canadian one is
`Name\CA\PersonName($family, ?$given)`, letters of the Latin script (English and French names). The Mexican one is
`Name\MX\PersonName(?$first, ?$second, $given)`: two surnames, one, or none (RENAPO records persons without
surnames; then the given names are the only ICAO identifier, `JUAN`).

Contracts: `Contract\Maskable` (every number above), `Contract\Subdivision` and `Contract\IssuedBySubdivision` (AU,
US and CA jurisdictions, AU licences, US DL/ID numbers, CA licence and health card numbers). `Contract\HasSex` and the `Sex` enum are implemented by the documents that encode a sex
(`Nir`, `FiscalCode`, the DVLA licence number, `ChiNumber`, the Québec NAM, the Mexican CURP); `Sex::Unspecified` is
the CURP's `X` (RENAPO, non-binary persons). `Contract\HasBirthDate` is implemented by the Mexican CURP only (its
17th character gives the century); the other encoded birth dates lack a century, so those classes expose the year
digits, month and day instead, and impossible dates (30 February) are refused.

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

### US DL/ID numbers and the jurisdiction factory

```php
use MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;

DlIdNumberFactory::fromNumberIn('123456789', Jurisdiction::Ny);   // NyDlIdNumber
DlIdNumberFactory::fromNumber('Y620-9305-7270');                  // IlDlIdNumber, the only match
DlIdNumberFactory::fromNumber('123456789');                       // AmbiguousJurisdictionException, 11 states
DlIdNumberFactory::fromNumberIn('123456789', Jurisdiction::Ca);   // "Invalid US-CA DL/ID number (format not covered)."
```

`DlIdNumberFactory` works like the AU factory, over the 30 jurisdictions with a published format only. Unlike AU,
some numbers fit exactly one state (10 digits: IN; a letter and 13 digits: WI; WA's `WDL` numbers). A plain 7-9 digit
number fits several, so pass the issuer when it is known. The 21 jurisdictions without a published format have no
class, and `fromNumberIn()` refuses them (`docs/sources/US.md` "DL/ID numbers").

### Canadian provincial documents

```php
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;

DriverLicenceNumberFactory::fromNumberIn('890562', Jurisdiction::Sk);   // SkDriverLicenceNumber, value 00890562
DriverLicenceNumberFactory::fromNumber('L1531-171274-08');            // QcDriverLicenceNumber, the only match
DriverLicenceNumberFactory::fromNumber('347631');                     // AmbiguousJurisdictionException: CA-BC, CA-SK
DriverLicenceNumberFactory::fromNumberIn('123', Jurisdiction::Mb);     // "Invalid CA-MB driver's licence number (format not covered)."

$card = HealthCardNumberFactory::fromNumber('9876543217 YM');          // OnHealthCardNumber, value 9876543217
$card->versionCode();                                                  // "YM"
HealthCardNumberFactory::fromNumber('123456789');                      // AmbiguousJurisdictionException, 6 jurisdictions
```

Both factories work like the AU and US ones. BC and SK licence numbers share every shape (1-8 digits, stored padded to
8), so a bare BC or SK number is always ambiguous: pass the issuer. All 13 health card formats are published (Ontario's
reciprocal-billing table, checked against each province's own text where one exists). The ON version code is part of
the card, not of the health number: two cards of one person have the same value and are equal, and the string, JSON
and storage forms carry the ten digits only.

## Personal names

A national name part (`Name\RU\FamilyName`, `GivenName`, `Patronymic`, and `FamilyName`, `GivenName` of `Name\AU`,
`Name\DE`, `Name\FR`, `Name\IT`, `Name\ES`, `Name\NL`, `Name\CH`, `Name\GB`, `Name\TR`, `Name\US`, `Name\CA`,
`Name\MX`, plus `Name\US\MiddleName`) is NFC-normalised, trimmed, has inner whitespace runs collapsed to one space, and keeps its letter
case. Limits: 100 characters per part. Russian parts accept only the 33 Russian letters; a Cyrillic name typed with
Latin look-alikes (Latin `a`, `o`, `e`, `c`) is refused. A compatibility character (its NFKC form differs: fullwidth
`Ｓｍｉｔｈ`, `ª`, the ligature `ﬁ`) is refused in every country, because it would be a second stored spelling of the same
name; the ones an ICAO table lists itself (`Ĳ`, `Ŀ`) are kept. Messages say "(N characters)" for an empty or too long
part, "(compatibility character)", or "(invalid character)" for a letter or separator that is not allowed where it
stands.

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
| AU, DE, FR, IT, ES, NL, CH, TR, US, MX | only an ICAO spelling of the name (`MULLER<<ZOE` for Müller Zoë); `<` for a hyphen is stored as the hyphen (policy `TransliterationVariant`) | Latin-script names: no source lets a passport keep another spelling, so the table spellings are the only ones |
| GB | any valid ICAO name (policy `AnyIcaoSpelling`) | HM Passport Office lets a passport carry an evidenced spelling (`docs/sources/GB.md`) |
| CA | any valid ICAO name (policy `AnyIcaoSpelling`) | the Passport Program points to "alternative spellings" and lets an adult drop or invert given names (`docs/sources/CA.md`) |

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
country: RU, AU, DE, FR, IT, ES, NL, CH, GB, TR, US, CA, MX) are read through a payload codec, so the mapper that the
bridge uses must be built with `Codecs::all()`:

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
    MSSTC4PHP\ValueObject\Documents\Name\US\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\CA\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec: ~
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
- **US:** a middle name or suffix beside a one-word name (no source defines it), the IRM's open list of "never issued"
  SSN examples (not refused), ATIN and IRSN; the DL/ID numbers of AK, AR, CA, DC, DE, GA, LA, MN, MO, MT, NC, NH, NV,
  OH, OR, SC, SD, TN, UT, VA and WV (no official format published), the ID cards of CT, HI, IN, KY, ME, NJ, NM, OK and
  RI (numbering not stated), the Maryland customer ID, and the decoding of name or birth data in IL, WA, FL and MI
  numbers (`docs/sources/US.md`).
- **CA:** the SIN check digit (the CRA names a "Mod 10" formula but no official text gives the algorithm), the
  province meaning of a SIN's first digit, the CRA individual and temporary tax numbers, the passport print form of a
  one-word name, special, diplomatic, temporary and emergency passport numbers (only the general format is
  published). Provincial documents: the driver's licence numbers of MB, NB, NL, NT, NU, PE and YT (their authorities
  name the number without a format; `fromNumberIn()` throws "(format not covered)"); the photo ID card numbers of AB,
  BC (BCID), ON (Photo Card) and QC; the encoded data of the ON and QC licence numbers (not defined in text) and the
  NS birth date (two-digit year); SK validation numbers, ICBC client numbers and the ON Driver Identification Number;
  the Manitoba six-character family registration number; prior health number formats (AB 11, MB 6, NS 11 numerics);
  the QC temporary NAM; the QC "chiffre valideur" and every health check digit other than BC and ON (no algorithm
  published); whether an ON version code is the current card's (`docs/sources/CA.md`).
- **MX:** the passport number (the 2021 e-passport agreement gives only the booklet number `N` + 8 digits; older
  passports have no published format), the RFC of a legal person, the CURP, RFC, NSS and elector-key check characters
  (no algorithm published), the RENAPO state catalogue and "palabras inconvenientes" list (not published with the
  Reglas: any two letters are accepted, substituted forms too), the INE sex and state code lists of the elector key,
  the fields of the NSS, other INE card numbers (CIC, OCR); `/` and `.` in names (no ICAO spelling). State driving
  licences: plan 3 Task 6b (`docs/sources/MX.md`).
- **No region value object for RU**: no field encodes a region with a published mapping to ISO 3166-2:RU (`RU.md`).
- **RU driving licences of the 1999 form** (Cyrillic series): expired by 2026 (`RU.md`).
- **RU old-form OMS policies** (series and number) and AU **11-digit Medicare individual numbers**.
- **AU driving licence card numbers**: a different number on the same licence, left for a later release (`AU.md`).
- **AU single names (mononyms)**: the constructor requires a given name (`AU.md`).
- **AU 8-digit TFNs of non-individuals**: no official format is published (`AU.md`).

## Limits to know about

- **Format only, check digit not verified:** INN, TFN, Medicare card number, CA SIN, the CA health card numbers
  other than BC and ON, and the MX CURP, RFC, NSS and clave de elector. No official algorithm is
  published (FNS says only "по алгоритму, определенному ФНС"; the ATO gives the TFN algorithm to registered developers
  only; the ADHA appendix is not reachable; the CRA names a "Mod 10" formula for the SIN without giving it), so a
  mistyped check digit is accepted. Only checksums quoted from an official source are verified: SNILS, OMS policy, DE
  pension insurance number, IT codice fiscale, ES DNI and NIE, NL BSN, CH AHV number, GB NHS and H&C numbers, BC and
  ON health card numbers.
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
  names use the BRP Teletex letters and refuse the `ĳ` ligature (written `ij`); TR names refuse `Q`, `W`, `X` (Law 1353
  art. 3), hyphens and apostrophes; derived `Ö`/`Ü` stay `OE`/`UE` (table 6.A); ES refuses the Catalan middle dot `l·l`;
  IT refuses the comma between given names; US parts store the Hawaiian ʻokina (U+02BB, U+2018) as an apostrophe, as the
  passport writes it; GB licence numbers of a holder with no first names would be refused (no published rule for them).
- **Source access:** FR passport and licence formats are France Titres-ANTS answers; CH passport length comes from
  the Basel-Stadt passport office and fedpol; TR passport format from the PRADO register; the ES NIF/NIE algorithm and
  several GB texts are cited through unmodified Internet Archive copies of the official page (original URL, archive URL
  and capture date in the source notes).
- Name parts are limited to 100 characters, `Icao\PersonName` to 255 (a very long real name is refused). Three long
  parts can exceed 255 in Latin (an ES name of three 100-character parts): the name is refused with
  "(Latin form too long)", unless the country accepts any ICAO spelling (RU, GB, CA) and an override is given.
- **RU, GB and CA accept an override without a derived form**: when a letter has no ICAO spelling or the derived form
  is too long, the override is kept as given (the passport authority writes a documented spelling); without an
  override the name is refused. An `AnyIcaoSpelling` override is stored as typed: `SMYTH<JONES<<MARY` and
  `SMYTH-JONES<<MARY` are two values. GB drops the `XXX` passport forename of a person with one name (`SMITH<<XXX` is
  `SMITH`); CA does not (no Passport Program text describes a placeholder).
- **The transliteration variant cap (4096) is per ICAO word**, so a long list of given names with umlauts is accepted
  with an override. A single word with more than twelve two-way letters (`Ä` = `AE`/`A`) or more than seven three-way
  letters (`Ü` = `UE`/`UXX`/`U`) is still refused when an override that differs from the derived form is given.

## Development

`make check` (php -l, PHPStan max, cs-fixer, Rector, deptrac), `make test`, `make infection`. See `CLAUDE.md`.
