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

The transliterator picks the one table whose `script()` covers every letter of the input; letters from two tables
→ "mixed scripts", none → "no ICAO transliteration".
