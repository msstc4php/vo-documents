# Architecture

deptrac forbids every framework namespace (Doctrine, Symfony, Illuminate, yii, CodeIgniter, Cycle, Spiral), as in the
core.

| Area | Purpose |
|---|---|
| `Contract\`, `Exception\`, `Sex` | Shared interfaces, the three exception families, the sex enum |
| `Internal\` | `@internal` helpers: Checksum, Mask, Digits, UnicodeText |
| `Icao\PersonName` | MRZ-style Latin name (`A-Z`, `-`, `<`; `<<` between primary and secondary), `mrzField()` truncation |
| `Icao\Transliterator` | National name part → ICAO Latin (`toLatin()` default, `variants()` all spellings) |
| `Icao\Table\*` | `@internal` `TransliterationTable` per script (LatinMultinational, Cyrillic); add a class + register it in the `Transliterator` constructor for a new script |
| `Name\AbstractNamePart` | Base of every national name part: `letters()` (regex class), `label()` (message), `innerSeparators()` (default `-' `) |
| `Name\LatinSpelling` | `@internal`: finds a Latin override among the ICAO spellings of a name's forms (hyphen ≡ `<`, capped at 4096) |
| `Name\PersonName` | Interface of a national full name: `latin(): Icao\PersonName`, `full()` |
| `Name\RU\*` | `AbstractRussianNamePart` (letters, word pattern), `FamilyName`, `GivenName`, `Patronymic`, `PersonName` (+ `short()`, `initialsFirst()`), `PersonNameCodec`; `@internal RussianTransliteration` (drops Ь, then `Icao\Transliterator`) |
| `Codecs` | `Codecs::all()`: every payload codec, for `new ValueObjectMapper(Codecs::all())`; add each new composite's codec here |

The transliterator picks the one table whose `script()` covers every letter of the input; letters from two tables
→ "mixed scripts", none → "no ICAO transliteration".
