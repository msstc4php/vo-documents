# Known issues

- **Infection crashes on escaped mutants whose diff contains `<…`** ("Incorrectly nested style tag found"): the
  console formatter reads MRZ strings such as `'<<'` as style tags. `make infection` exits 1 even though the run
  finished. Kill every mutant in files with `<` literals (Icao), or inspect with
  `vendor/bin/infection --show-mutations=0` + `var/infection/infection.json`.
- **Equivalent mutants in `Icao\PersonName::mrzField()`** came from boundary branches that produce the same output;
  the code was restructured so `cut()` is called unconditionally (a shorter text is returned unchanged). Keep it that
  way rather than adding fast paths.
- **ICAO table 6.B has no Ь (soft sign)**: `Icao\Transliterator` still throws for `Игорь`. Russian names go through
  `Name\RU\RussianTransliteration`, which omits Ь (MVD Order 996 table has no row for it, `docs/sources/RU.md` §3);
  the Cyrillic table stays the verbatim ICAO table. Ъ → `IE`, so `Объедков` → `OBIEEDKOV` (the plan's `OBIEDKOV` was wrong).
- **PDF glyphs vs code points in Doc 9303 Part 3 §6**: rows 0402, 040E, 0110 print a different glyph than their
  code; the code column is used (see `docs/sources/ICAO.md`).
- **Russian passports omit the patronymic in Latin** (MVD Orders 996/186): `Name\RU\PersonName::latin()` derives
  `IVANOV<<IVAN`, not the plan's `IVANOV<<IVAN<IVANOVICH`; an override may include the patronymic. `equals()` compares
  the stored override, so an explicit override equal to the derived form is not equal to no override.
- `Name\RU\*` letters are the 33 Russian letters (`А-ЯЁа-яё`), not `\p{Cyrillic}`: Ukrainian/Belarusian letters are refused.
- php-cs-fixer rewrites `@link <url>` to `@see <url>` in class docblocks; source citations therefore use `@see`.
- `Transliterator` omits only U+0027 and U+2019 apostrophes (Part 3 §4.6); U+02BC and other punctuation are refused.
- `PersonName::fromParts()` refuses `<` inside a part (it would silently split identifiers); `from()` accepts the
  canonical `<`/`<<` form. `mrzField()` maps hyphens to `<` in both identifiers before cutting.
