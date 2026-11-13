# Known issues

- **Infection crashes on escaped mutants whose diff contains `<<`** ("Incorrectly nested style tag found"): the
  console formatter reads MRZ strings such as `'<<'` as style tags. `make infection` exits 1 even though the run
  finished. Kill every mutant in files with `<` literals (Icao), or inspect with
  `vendor/bin/infection --show-mutations=0` + `var/infection/infection.json`.
- **Equivalent mutants in `Icao\PersonName::mrzField()`** came from boundary branches that produce the same output;
  the code was restructured so `cut()` is called unconditionally (a shorter text is returned unchanged). Keep it that
  way rather than adding fast paths.
- **ICAO table 6.B has no Ь (soft sign)**: `Icao\Transliterator` still throws for `Игорь`. Russian names go through
  `Name\RU\RussianTransliteration`, which omits Ь (MVD Order 996 table has no row for it, `docs/sources/RU.md` §3); the
  Cyrillic table stays the verbatim ICAO table. Ъ → `IE`, so `Объедков` → `OBIEEDKOV` (the plan's `OBIEDKOV` was wrong).
- **PDF glyphs vs code points in Doc 9303 Part 3 §6**: rows 0402, 040E, 0110 print a different glyph than their
  code; the code column is used (see `docs/sources/ICAO.md`).
- **Russian passports omit the patronymic in Latin** (MVD Orders 996/186): `Name\RU\PersonName::latin()` derives
  `IVANOV<<IVAN`, not the plan's `IVANOV<<IVAN<IVANOVICH`; a RU override may be any valid ICAO name (MVD §32/§140,
  RU.md §4). An override equal to the derived form (also with `<` for a hyphen) is normalised to null, so it equals
  no override and the codec stores `latin: null`.
- `Name\RU\*` letters are the 33 Russian letters (`А-ЯЁа-яё`), not `\p{Cyrillic}`: Ukrainian/Belarusian letters are
  refused; no word starts with Ъ/Ь (`AbstractRussianNamePart::word()`), otherwise `PersonName` failed later with a
  transliteration message.
- `PersonName::jsonSerialize()` uses `effectiveLatin`, the codec payload `latin` (override only); the codec refuses
  unknown keys so the JSON view can never be decoded as "no override".
- Infection only runs tests that cover a mutated line: a base-class hook overridden in every real subclass needs a
  `tests/Fixture` subclass that uses the default (see `LatinNamePart`, `SingleWordNamePart`).
- php-cs-fixer rewrites `@link <url>` to `@see <url>` in class docblocks; source citations therefore use `@see`.
- `Transliterator` omits only U+0027 and U+2019 apostrophes (Part 3 §4.6); U+02BC and other punctuation are refused.
- `PersonName::fromParts()` refuses `<` inside a part (it would silently split identifiers); `from()` accepts the
  canonical `<`/`<<` form. `mrzField()` maps hyphens to `<` in both identifiers before cutting.
- **INN digits 3-4 changed meaning on 2026-01-01** (Order ЕД-7-14/559@): NN = FNS directorate, YY = FNS index, so there
  is no `taxOfficeCode()`; `Inn::taxAuthorityRegionCode()` returns digits 1-2.
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
- **TFN and Medicare: format only; the check digit is not verified because no official algorithm is published.** The
  ATO gives the TFN algorithm to registered developers only; the Medicare check digit is ADHA Appendix E, served by no
  reachable official host. Owner rule: a checksum is computed only when its algorithm is quoted from an official source.
- Sub-national pattern (copy for US/CA/BR): `Document\<CC>\Jurisdiction` enum, `DriverLicenceNumber` interface with a
  covariant `jurisdiction()`, an abstract base per country with `static format(): Internal\NumberFormat` (character
  class + length range; messages "(N characters)" / "(invalid character)") and shared `masked()`/`parse()`, one final
  class per jurisdiction, a factory with an exhaustive enum → class `match` delegating to
  `Internal\SubdivisionResolver`.
- National name codecs validate payloads through `Internal\PayloadFields` (closed key set, string fields, one shape
  message).
- **CI cannot pass until the `msstc4php/vo-documents` GitHub repository exists**: `composer-ci.json` and the
  `vo-documents-doctrine` CI pull it from `github.com/msstc4php/vo-documents` (only a local `origin` is configured, no
  push was made). Owner action: create the repository, push, tag `v0.1.x`.
- **Length limits are a decision, not a source rule**: name parts 100 (`AbstractNamePart::MAX_LENGTH`),
  `Icao\PersonName` 255; Doctrine columns follow the constants.
- **OMS policy length (16) and the international passport 2 + 7 split are unverified** against an official text
  (ffoms.gov.ru fails TLS); see RU.md.
- **INN: format only** (12 digits, not all zeros); the FNS order does not publish the control-digit algorithm, so a
  wrong check digit is accepted. SNILS (PFR act) and OMS (FFOMS Order 79 citing ISO/HL7 27931) keep their checksums.
- `composer-local.json` is gitignored; `CLAUDE.md` says how to create it.
- Deferred minors (not fixed): `isInScript` passes the letter class straight into a regex (constants only today);
  the "no number" ambiguity test is trivial. `Subdivision` string backing is enforced by
  `tests/Unit/Contract/SubdivisionTest.php` (scans `src/`).
- Escaped Infection mutants (9) are all equivalent: `luhn()` `>= 9` and `-=` (same result mod 10), `CastInt` in
  `Checksum`/`Mask`/`Snils` (strict digit strings), the `?? throw` in `Mask::keepLastDigits()` (PCRE failure only) and
  `strlen($base) === 1 &&` in `LatinMultinational::baseLetterSpellings()` (a one-byte base of an upper-case Latin letter
  is always A-Z).
- **vo-base 1.1.0 reported interfaces as supported**:
  `ValueObjectMapper::supports(Document\AU\DriverLicenceNumber::class)` was true and reading it threw `\Error` ("Cannot
  call abstract method"). Fixed in vo-base 1.1.1 (CHANGELOG `## 1.1.1 - 2026-11-13`, local commits only).
  `composer.json`/`composer-ci.json` require `^1.1.1`, the local profile aliases `dev-main as 1.1.1`. **Release order
  (owner):** tag vo-base `v1.1.1` first, then vo-documents; until then the CI profile cannot resolve. Typed properties
  must use the concrete jurisdiction class.
- `Icao\PersonName::from()` drops spaces next to `<` and trailing `<` padding before the shape check, so `IVANOV<<` is
  the mononym `IVANOV`; `fromParts()` still refuses an empty part itself (the padding strip would hide it).
- `Digits::exactly()` checks the length first (`mb_strlen`), then the characters (`strspn`): a regex with `/D` left
  equivalent mutants because every line break is a separator and is stripped.
- `composer-ci.lock` is regenerated by `make install-ci` and not ignored (`.gitignore` is a byte-identical template):
  delete it after a CI-profile install, never commit it.
- **Latin letters outside table 6.A** (`Ễ`, `Ș`, `Ț`) are spelled through their base letter
  (`LatinMultinational::baseLetterSpellings()`: NFD, drop `\p{Mn}`, table again). This is the package's decision (AU.md,
  ICAO.md); `Ƒ`/`Ʒ` (no base) are still refused. The Cyrillic table and `RussianTransliteration` have no fallback.
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
- `SubdivisionResolver` no-match message: "(fits no jurisdiction)" when the length fits at least one subdivision
  (`AbstractDriverLicenceNumber::fitsLength()`, `@internal`), else the length.
- **Policy:** transliteration tables and override rules change only in a major version (stored overrides are
  re-validated on hydration).
- `rector.php` (template file) skips a deprecated rule (`PostIncDecToPreIncDecRector`, a Rector warning on every run);
  it comes from the bundle-standard template and is reported to the owner, not edited here.
- **Ĳ/Ŀ and their two-letter spellings are distinct stored values with the same ICAO form** (intended): `Ĳsselmeer`
  and `IJsselmeer` are different name parts, so they are not equal and do not collide in a unique index, while both
  derive `IJSSELMEER`. The table lists the ligature, so the national spelling is kept as
  typed.
- **www.gesetze-im-internet.de answers from this environment** (sandbox: pass it in `allowed_domains`); per-paragraph
  pages are `<act>/__<n>.html` and `anlage_<n>.html`; the full text is `BJNR….html`, ISO-8859-1. The PassVwV PDF
  (BR-Drs. 301/19) extracts as scrambled glyphs and cannot be searched.
- **DE Rentenversicherungsnummer: the DRV glossary example `65 170839 J 08 8` fails VKVV § 2 Abs. 6** (the algorithm
  gives 1): it is illustrative and the tests refuse it; the AOK example `65 230701 J008` is valid. The birth date
  field has no century and may hold non-dates (unpublished rules for unproven birth dates), and 50-99 is "female, no
  entry or divers", so the class exposes strings only (`birthDateField()`, `serialNumber()`), no `HasBirthDate`/
  `HasSex`, and does not validate the date.
- **DE Steuer-ID: format only** (StIdV § 1: ten digits + check digit, no algorithm). The "no leading zero / one
  repeated digit" rules come from a BZSt paper released under the IFG (FragDenStaat), not a publication: not applied.
- **DE Führerschein number: not covered** (FeV Anlage 8 gives only the composition, no length or check algorithm).
- Escaped equivalent mutant: `CastInt` in `Document\DE\PensionInsuranceNumber::checkDigit()` (numeric strings
  multiply the same). The character-class check uses `strspn` instead of an anchored regex because anchors on a
  fixed-length value left equivalent mutants.
- **FR NIR key: format only.** INSEE, service-public, the ministries' leaflet and the DSN norm say "clé de contrôle
  de 2 chiffres" but publish no computation; `97 − (n mod 97)` with `2A`→19, `2B`→18 is only on secondary sites (the
  INSEE example `1 21 12 77 131 232 - 50` happens to satisfy it). Any two-digit key is accepted. Field rules (sex 1/2,
  month 01-12/20/30-42/50-99, department 01-99/2A/2B, commune and order 001-999) come from the DSN cahier technique
  S21.G00.30.001. No century in the NIR, so no `HasBirthDate` and the DSN rules tied to 1968/1976 are not applied.
- **FR sources reachable from this environment:** `www.insee.fr`, `www.service-public.gouv.fr` (not `.fr`),
  `www.net-entreprises.fr`, `www.plus.transformation.gouv.fr` (ANTS answers), `caf.fr`, `www.oecd.org`. Légifrance,
  interieur.gouv.fr and PRADO answer 403 to curl; the Légifrance circular PDF (`/download/file/pdf/cir_38565/CIRC`)
  came through the WebFetch tool. The CAF-hosted ministries' leaflet is a scan (render with `pdftoppm`). Also: the
  `ec.europa.eu` TIN pages answer 429/500, `affairesjuridiques.aphp.fr` times out, `www.service-public.fr` is refused by
  the sandbox (use `www.service-public.gouv.fr`), `ants.gouv.fr` serves only a script shell.
- **FR passport, licence and CNI formats come from ANTS answers on Services Publics+ and a 2000 Interior instruction**
  (no format in a decree that could be read). The 2021 CNI (9 characters) is not covered; the old CNI is
  "douze chiffres" per the instruction, so a Corsican place code with a letter is refused (unverified case).
- **FR names use the circular's letter list, not `\p{Latin}`** (Circulaire 23 juillet 2014, § 1): `ñ`, `ł`, `ß`, `å`
  are refused even in foreign names ("sans reproduire les éventuels signes diacritiques de la langue étrangère").
- `Document\FR\LegacyIdentityCardNumber` does not validate the issue-month field (the instruction defines the field,
  not its values); `issueMonth()` is a plain `int` that may lie outside 1-12.
- **IT codice fiscale source:** the DM 23 dicembre 1976 is not in the online index of its own Gazzetta (n. 345 of
  1976); it is read from its official reprints as an annex of later decrees (GU 27-4-1999 S.O. 83, `099A3188`, and
  the 1995/1997/1998 ones). The 1997 reprint misprints `4 = O` (omocodia) and `r = 19`; the others read `4 = Q` and
  `T = 19`. `def.finanze.it` and `www.mit.gov.it` fail TLS verification; `www.cartaidentita.interno.gov.it` resets
  connections (the CIE number `CA00000AA` form could not be quoted, so the CIE is not covered).
- **IT omocodia:** any combination of the seven numeric positions may hold the letter `L M N P Q R S T U V`; the art. 6
  "a partire da quella di destra" is read as the office's order, not a validity rule. The check letter is computed on
  the characters as written, the fields through `FiscalCode::DIGITS`.
- **IT February 29** is accepted when the two-digit year is divisible by four (`00` too, 2000), refused otherwise: no
  century is encoded, so only dates impossible in every century are refused.
- Escaped equivalent mutants in `Document\IT\FiscalCode`: the `max(1, min(31, …))` clamp of `birthDay()` (types only;
  the day is validated in `parse()`) and `+` → `-` in the year of the leap check (`10a + b` and `10a - b` have the same
  remainder mod 4 for every digit `b`). The lookup tables (`DIGITS`, `MONTHS`, `CHECK_VALUES`) replaced `strpos()`
  arithmetic whose casts and clamps left more equivalents; unreachable `?? throw` branches were dropped (PHPStan max
  accepts the string offsets).
- **ES `@see` lines** for the Interior page exceed 120 characters: the URL cannot be wrapped (controller ruling).
- **ES sources:** `www.interior.gob.es` (the NIF/NIE check-letter page) answers 403 with a Cloudflare challenge to
  curl and WebFetch; it was read from the Internet Archive's unmodified capture (`/web/20260228172549id_/…`).
  `revista.seg-social.es` and the Council's PRADO register also answer 403 (PRADO has no capture, so the Spanish
  passport number `AAANNNNNN` could not be quoted and is not covered). The BOE PDFs are reachable.
- **ES NUSS** is format only: the `(province + number) mod 97` rule appears only on secondary sites, and the TGSS AFI
  message's "Provincia n..2 1..56" is that message's value list, not a validity rule (codes above 56 exist), so the
  province is not range-checked.
- **ES NIE before 2008** (`X` + eight digits + letter, ten characters) stays valid per Orden INT/2058/2008 but has no
  published check algorithm; `Nie` accepts it with any letter `A-Z` (format only). The DNI requires eight digits
  (leading zeros kept).
- **ES names:** the table default for `Ü` is `UE` (`Argüelles` → `ARGUEELLES`), not the Spanish `U`; `ARGUELLES` is an
  accepted override. Catalan `l·l` with U+00B7 is refused (no ICAO row); `ŀ` (U+0140) is accepted.
- Escaped equivalent mutants in `Document\ES\*`: the `(int)` cast in `ControlLetter::of()` (a numeric string divides
  the same), and the `substr()` length / unwrap in
  `Dni`/`Nie` (an `(int)` cast stops at the trailing check letter).
- **NL names refuse `ĳ`/`Ĳ`**: LO BRP § 5.1.2.1 forbids the Hollandse IJ, so `Name\NL\*` uses the BRP letter tables,
  not `\p{Latin}` (unlike DE/IT/ES). `İ`/`ı` are accepted, `Ð`, `Ș`, `Ț` refused; `ĸ` passes the part but has no ICAO
  spelling, so `PersonName` refuses it. Apostrophe prefixes (`'t Hart`, `d' Ancona`) need the custom `word()` of
  `AbstractDutchNamePart`: a leading `'` only at the start or after a space, a trailing `'` only before a space.
- **NL BSN**: `000000000` passes the elfproef and is accepted (the LO BSN "ingestelde interval" is unpublished).
- **NL sources:** `wetten.overheid.nl` fails from curl (HTTP/2 PROTOCOL_ERROR, TLS bad record mac on HTTP/1.1); read
  it through WebFetch. LO BRP 2025.Q4 PDF is 404 at its expected URL; 2025.Q2 is cited. RDW publishes no
  rijbewijsnummer format (not covered). The `@see` lines of `IdentityDocumentNumber` exceed 120 characters (URLs cannot
  wrap), as for ES.
- **CH sources:** `www.fedlex.admin.ch` needs JavaScript; read act texts from the filestore HTML found through the
  SPARQL endpoint `fedlex.data.admin.ch/sparqlendpoint` (`jolux:isMemberOf <…/eli/cc/…>`, DEU, `html`). fedpol, BJ and
  ASTRA PDFs answer 404 at their published paths and PRADO answers 403: their Internet Archive copies are cited (use
  the `id_` form for the raw bytes). The `@see` lines of `Document\CH\IdentityDocumentNumber` and
  `DriverLicenceNumber` exceed 120 characters (URLs cannot wrap).
- **CH AHV**: the UPI handbook's illustration rows `756.1234.5678.90` / `756.0123.4567.89` fail the official check
  digit; use `756.3047.5009.62` (Rz 1201) and `756.1234.5678.97` (Anhang 7) as examples. Dots are stripped only in
  `AhvNumber`, not added to `Digits::SEPARATORS`.
- **CH driving licence**: the 15-digit 2003 layout (`000123456789003`) is known only from the ASTRA specimen, so it is
  accepted format-only without `faberPin()`; no check digit is published for either layout.
- **CH names**: `µ` (U+00B5) is a compatibility character and is refused although it is in ISO 8859-1; `ſ`, `ŉ` the
  same. `Ŀ ŀ` stay (ICAO lists them).
