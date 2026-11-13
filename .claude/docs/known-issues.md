# Known issues

- **Infection crashes on escaped mutants whose diff contains `<<`** ("Incorrectly nested style tag found"): the
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
  `IVANOV<<IVAN`, not the plan's `IVANOV<<IVAN<IVANOVICH`; a RU override may be any valid ICAO name (MVD §32/§140,
  RU.md §4). An override equal to the derived form (also with `<` for a hyphen) is normalised to null, so it equals
  no override and the codec stores `latin: null`.
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
- **CI cannot pass until the `msstc4php/vo-documents` GitHub repository exists**: `composer-ci.json` and the
  `vo-documents-doctrine` CI pull it from `github.com/msstc4php/vo-documents` (only a local `origin` is configured, no
  push was made). Owner action: create the repository, push, tag `v0.1.x`.
- **Length limits are a decision, not a source rule**: name parts 100 (`AbstractNamePart::MAX_LENGTH`),
  `Icao\PersonName` 255; Doctrine columns follow the constants.
- **OMS policy length (16) and the international passport 2 + 7 split are unverified** against an official text
  (ffoms.gov.ru fails TLS); see RU.md.
- **A checksum without a public text is still shipped** (INN, TFN, Medicare): dropping it would accept
  most typos. The owner should confirm this against the spec's anti-goal.
- `composer-local.json` is gitignored; `CLAUDE.md` says how to create it.
- Deferred minors (not fixed): `isInScript` passes the letter class straight into a regex (constants only today);
  the "no number" ambiguity test is trivial. `Subdivision` string backing is enforced by
  `tests/Unit/Contract/SubdivisionTest.php` (scans `src/`).
- Escaped Infection mutants (9) are all equivalent: `luhn()` `>= 9` and `-=` (same result mod 10), `CastInt` in
  `Checksum`/`Mask`/`Snils` (strict digit strings), the `?? throw` in `Mask::keepLastDigits()` (PCRE failure only) and `strlen($base) === 1 &&` in
  `LatinMultinational::baseLetterSpellings()` (a one-byte base of an upper-case Latin letter is always A-Z).
- **vo-base 1.1.0 reported interfaces as supported**: `ValueObjectMapper::supports(Document\AU\DriverLicenceNumber::class)`
  was true and reading it threw `\Error` ("Cannot call abstract method"). Fixed in vo-base 1.1.1 (CHANGELOG
  `## 1.1.1 - 2026-11-13`, local commits only). `composer.json`/`composer-ci.json` require `^1.1.1`, the local
  profile aliases `dev-main as 1.1.1`. **Release order (owner):** tag vo-base `v1.1.1` first, then vo-documents;
  until then the CI profile cannot resolve. Typed properties must use the concrete jurisdiction class.
- `Icao\PersonName::from()` drops spaces next to `<` and trailing `<` padding before the shape check, so
  `IVANOV<<` is the mononym `IVANOV`; `fromParts()` still refuses an empty part itself (the padding strip would hide it).
- `Digits::exactly()` checks the length first (`mb_strlen`), then the characters (`strspn`): a regex with `/D` left
  equivalent mutants because every line break is a separator and is stripped.
- `composer-ci.lock` is regenerated by `make install-ci` and not ignored (`.gitignore` is a byte-identical template):
  delete it after a CI-profile install, never commit it.
- **Latin letters outside table 6.A** (`Ễ`, `Ș`, `Ț`) are spelled through their base letter
  (`LatinMultinational::baseLetterSpellings()`: NFD, drop `\p{Mn}`, table again). This is the package's decision
  (AU.md, ICAO.md); `Ƒ`/`Ʒ` (no base) are still refused. The Cyrillic table and `RussianTransliteration` have no fallback.
- **Compatibility characters are refused in every name part** (`AbstractNamePart`: NFKC ≠ NFC per character), except
  the ones an ICAO table lists itself (`Transliterator::lists()`: today `Ĳ`/`ĳ` and `Ŀ`/`ŀ` of 6.A), which keep their
  table spelling (`Ĳsselmeer` → `IJSSELMEER`). The Transliterator is built only for a compatibility character, so
  tests of ordinary parts need no `UsesClass` for it.
- Name part messages: "(N characters)" for empty or > 100, "(compatibility character)", "(invalid character)" for
  any pattern failure (also a misplaced separator).
- `LatinSpelling::find()` matches part by part: the override's secondary words are split by each part's word count
  (all variants of a part have the same words), so the cost is the sum of variant counts and the cap
  (`MAX_SPELLINGS` = 4096) is per part. `LatinOverridePolicy::resolve()` compares with the derived form **before**
  computing spellings, so the derived override of a name with too many spellings (13 × `Ä`) is accepted.
- `SubdivisionResolver` no-match message: "(invalid character)" when the length fits at least one subdivision
  (`AbstractDriverLicenceNumber::fitsLength()`, `@internal`), else the length.
- **Policy:** transliteration tables and override rules change only in a major version (stored overrides are
  re-validated on hydration).
- `rector.php` (template file) skips a deprecated rule (`PostIncDecToPreIncDecRector`, a Rector warning on every run);
  it comes from the bundle-standard template and is reported to the owner, not edited here.
