# msstc4php/vo-documents

Value objects for identity documents and personal names of the world's largest economies, with ICAO Doc 9303 Latin
names. Built on [`msstc4php/vo-base`](https://github.com/msstc4php/vo-base); PHP >= 8.4. Today: the Russian Federation
and Australia.

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

Runtime requirements: `msstc4php/vo-base ^1.1`, `ext-mbstring`, `symfony/polyfill-intl-normalizer`. No framework.

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
last four digits.

| Class | Document | Check | Source |
|---|---|---|---|
| `Document\RU\Snils` | SNILS, 11 digits | control number mod 101 (above 001-001-998) | SFR Order No. 1463 of 17.11.2025; PFR checking rules |
| `Document\RU\Inn` | INN of an individual, 12 digits | two control digits | FNS Order ЕД-7-14/559@; weights quoted from practice (see below) |
| `Document\RU\InternalPassportNumber` | Internal passport, series + number, 10 digits | shape only | Government Decree No. 2267 of 23.12.2023 |
| `Document\RU\PassportIssuingUnitCode` | Issuing unit code, `NNN-NNN` | shape only | MVD Order No. 773 of 16.11.2020 |
| `Document\RU\InternationalPassportNumber` | Passport for travel abroad, 9 digits | shape only | MVD Orders No. 186 (2021), No. 996 (2019) |
| `Document\RU\DriverLicenceNumber` | Driving licence, `00 00 000000` | shape only | MVD Order No. 365 of 13.05.2009 |
| `Document\RU\DriverLicenceCategory` | Licence categories (enum) | n/a | MVD Order No. 365; Federal Law 196-FZ |
| `Document\RU\OmsPolicyNumber` | OMS policy (ЕНП), 16 digits | HL7 Mod10 (Luhn) | FFOMS Order No. 79 of 07.04.2011 |
| `Document\AU\TaxFileNumber` | Tax file number, 9 digits | weighted sum mod 11 | ATO (algorithm not public, see below) |
| `Document\AU\MedicareCardNumber` | Medicare card, 10 digits | check digit | Australian Digital Health Agency (algorithm not public) |
| `Document\AU\DriverLicence\{Act,Nsw,Nt,Qld,Sa,Tas,Vic,Wa}DriverLicenceNumber` | Licence number per state or territory | shape only | Home Affairs DVS field rules |
| `Document\AU\Jurisdiction` | The eight states and territories (enum, ISO 3166-2:AU) | n/a | ISO 3166-2:AU |

### AU driving licences and the jurisdiction factory

```php
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;

$licence = DriverLicenceNumberFactory::fromNumberIn('01234567', Jurisdiction::Nsw);   // NswDriverLicenceNumber
DriverLicenceNumberFactory::jurisdictionsMatching('01234567');                       // [Act, Nsw, Nt, Qld, Tas, Vic]
DriverLicenceNumberFactory::fromNumber('01234567');                                  // AmbiguousJurisdictionException
```

`fromNumber()` never picks one of several jurisdictions silently: it throws `AmbiguousJurisdictionException`, whose
message lists the ISO codes in enum order, or `InvalidDocumentNumberException` when none fits. **With the formats
published today every valid number fits two or more jurisdictions, so `fromNumber()` always throws for AU**; use
`fromNumberIn()` when the issuer is known (see `docs/sources/AU.md`).

## Personal names

A national name part (`Name\RU\FamilyName`, `GivenName`, `Patronymic`, `Name\AU\FamilyName`, `GivenName`) is
NFC-normalised, trimmed, has inner whitespace runs collapsed to one space, and keeps its letter case. Limits: 100
characters per part. Russian parts accept only the 33 Russian letters; a Cyrillic name typed with Latin look-alikes
(Latin `a`, `o`, `e`, `c`) is refused.

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

### Canonical form and the override rule

A person's Latin name is derived from the national one. A different official spelling (for example one printed in an
existing passport) can be given as an override. It is accepted when it is one of the ICAO spellings of the name (case,
spacing and
`<` for a hyphen are normalised first) and refused otherwise:

```php
$name = new PersonName(
    FamilyName::from('Иванов'),
    GivenName::from('Иван'),
    null,
    IcaoPersonName::from('ivanov<<ivan'),   // normalised to IVANOV<<IVAN; must be a valid spelling
);
$name->latinOverride();   // null when the Latin form is derived
```

The storage payload of a composite name stores only the override (`latin: null` otherwise), never the derived form.

## Bridges

The value objects are plain `vo-base` value objects, so every
[`vo-base-*`](https://github.com/msstc4php/vo-base#framework-integration)
bridge handles them without extra code:

| Need | Where |
|---|---|
| Validation, request input, route parameters | Works as is in every vo-base bridge (Symfony bundle, Laravel, Yii 2, CodeIgniter 4, Spiral) |
| Storage of string numbers and name parts | As is in Laravel (`AsValueObject`), Yii 2 (behavior), CodeIgniter 4 (casts) and Cycle (typecast) |
| Storage and validation of composite names | Only with a mapper built from `Codecs::all()`, handed to the cast, rule or behavior (next section) |
| Doctrine ORM / MongoDB ODM | [`msstc4php/vo-documents-doctrine`](https://github.com/msstc4php/vo-documents-doctrine): one type per class, named `vod_<cc>_<snake_name>` |

### Composite names need the codecs

Document numbers and name parts are strings and need nothing. The composite names (`Name\RU\PersonName`,
`Name\AU\PersonName`) are read through a payload codec, so the mapper that the bridge uses must be built with
`Codecs::all()`:

```php
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;

$mapper = new ValueObjectMapper(Codecs::all());
```

Binding per bridge. The shared container binding is read by request input, route parameters and the Spiral/Yii 2
validators, but some storage and validation classes build their own mapper, so give them yours.

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
# Symfony (vo-base-bundle): every PayloadCodec service is autoconfigured and added to the mapper
services:
    _defaults: { autoconfigure: true }
    MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec: ~
    MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec: ~
```

```php
// Cycle without a container: one typecast per role, with your mapper
S::TYPECAST_HANDLER => [new ValueObjectTypecast('account', new ValueObjectMapper(Codecs::all())), Typecast::class],
```

CodeIgniter 4 calls cast handlers statically with the core's built-in codecs only, so **composite names are not
supported there** unless the application builds its own mapper; the string classes work.

## Not covered

Collected from the source notes; each is a deliberate omission with its reason in `docs/sources/`.

- **No region value object for RU**: no field encodes a region with a published mapping to ISO 3166-2:RU (`RU.md`).
- **RU driving licences of the 1999 form** (Cyrillic series): expired by 2026 (`RU.md`).
- **RU old-form OMS policies** (series and number) and AU **11-digit Medicare individual numbers**.
- **AU driving licence card numbers**: a different number on the same licence, left for a later release (`AU.md`).
- **AU single names (mononyms)**: the constructor requires a given name (`AU.md`).
- **AU 8-digit TFNs of non-individuals**: weights are not published anywhere official (`AU.md`).

## Limits to know about

- **Checksums without a public official text.** The INN control-digit weights (FNS says only "по алгоритму,
  определенному
  ФНС"), the TFN weights (ATO gives them to registered developers only) and the Medicare check digit (ADHA appendix not
  reachable) are implemented from the algorithm used in practice. Each is flagged on its class and in the source note;
  blogs appear there only as "quoted from".
- The 16-digit length of the OMS policy and the 2 + 7 split of the international passport number are not stated in
  a text read (`RU.md`).
- Name parts are limited to 100 characters, `Icao\PersonName` to 255 (a very long real name is refused).

## Development

`make check` (php -l, PHPStan max, cs-fixer, Rector, deptrac), `make test`, `make infection`. See `CLAUDE.md`.
