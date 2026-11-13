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
- `Name\RU\*` letters are the 33 Russian letters (`А-ЯЁа-яё`), not `\p{Cyrillic}`: Ukrainian/Belarusian letters are refused;
  no word starts with Ъ/Ь (`AbstractRussianNamePart::word()`), otherwise `PersonName` failed later with a transliteration message.
- `PersonName::jsonSerialize()` uses `effectiveLatin`, the codec payload `latin` (override only); the codec refuses
  unknown keys so the JSON view can never be decoded as "no override".
- Infection only runs tests that cover a mutated line: a base-class hook overridden in every real subclass needs a
  `tests/Fixture` subclass that uses the default (see `LatinNamePart`, `SingleWordNamePart`).
- php-cs-fixer rewrites `@link <url>` to `@see <url>` in class docblocks; source citations therefore use `@see`.
- `Transliterator` omits only U+0027 and U+2019 apostrophes (Part 3 §4.6); U+02BC and other punctuation are refused.
- `PersonName::fromParts()` refuses `<` inside a part (it would silently split identifiers); `from()` accepts the
  canonical `<`/`<<` form. `mrzField()` maps hyphens to `<` in both identifiers before cutting.
- **INN control-digit weights have no official text**: FNS Order ЕД-7-14/559@ only says the control number is computed
  "по алгоритму, определенному ФНС". `Document\RU\Inn` uses the universally described weights (RU.md "INN").
- **INN digits 3-4 changed meaning on 2026-01-01** (Order ЕД-7-14/559@): NN = FNS directorate, YY = FNS index, so there is
  no `taxOfficeCode()`; `Inn::taxAuthorityRegionCode()` returns digits 1-2.
- **HL7 Mod10 (OMS policy, FFOMS Order 79) is Luhn**: doubling the odd-position number has the same digit sum as
  doubling each digit; `OmsPolicyNumber` uses `Checksum::luhn()` over all 16 digits.
- `Checksum` refuses `''` (was accepted before task 5: `luhn('')` returned true).
- Equivalent mutants in number formatting: give the last `substr()` no length (`substr($v, 9)`), otherwise
  `substr($v, 9, 3)` mutants escape on a fixed-length value.
- **www.ffoms.gov.ru fails TLS verification** (curl error 60) from this environment: FFOMS sources are cited by URL and
  quoted from legalacts.ru copies.
- **AU licence formats always overlap**: per the Home Affairs DVS field rules (AU.md) every valid licence number fits
  2-6 jurisdictions, so `Document\AU\DriverLicenceNumberFactory::fromNumber()` is always ambiguous or invalid today.
  Use `fromNumberIn()`. The resolution lives in `Internal\SubdivisionResolver` (fixture-tested single-match path).
- **No AU issuing authority publishes its licence format**; the source is the Commonwealth DVS rules on
  immi.homeaffairs.gov.au (the only AU gov site that reliably answers from this environment; most others reset HTTP/2
  or return 403).
- **TFN and Medicare checksums: no public official text; algorithm as used in practice.** The ATO gives the TFN
  algorithm to registered developers only; the Medicare check digit is ADHA Appendix E, served by no reachable official
  host. Weights are quoted from secondary sources in AU.md only; the classes cite the official pages.
- Sub-national pattern (copy for US/CA/BR): `Document\<CC>\Jurisdiction` enum, `DriverLicenceNumber` interface with a
  covariant `jurisdiction()`, an abstract base per country with `static format(): Internal\NumberFormat` (character
  class + length range; messages "(N characters)" / "(invalid character)") and shared `masked()`/`parse()`, one final
  class per jurisdiction, a factory with an exhaustive enum → class `match` delegating to `Internal\SubdivisionResolver`.
- National name codecs validate payloads through `Internal\PayloadFields` (closed key set, string fields, one shape
  message).
