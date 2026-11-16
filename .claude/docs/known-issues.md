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
- `LatinSpelling::find()` matches word by word (`IdentifierSpellings`: the variants of each ICAO word, split at spaces
  and hyphens, plus the national separators to rebuild the canonical form), so the cost is the sum of variant counts and
  the cap (`Transliterator::MAX_VARIANTS` = 4096, the one source; `LatinSpelling` has no cap of its own) is per word: 13
  × `Jürgen` with an override is accepted; one word with 13 two-way letters (`Ä`) or 8 three-way letters (`Ü` =
  `UE`/`UXX`/`U`) is still refused when an override differs from the derived form. `LatinOverridePolicy::resolve()`
  compares with the derived form **before** computing spellings, so the derived override of a name with too many
  spellings (13 × `Ä`) is accepted.
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
  "douze chiffres" per the instruction, but a Corsican place code holds `2A`/`2B` at the department positions (5-6), so
  `LegacyIdentityCardNumber` accepts those two letters there (controller ruling: never refuse a still-valid card).
- **FR names use the circular's letter list, not `\p{Latin}`** (Circulaire 23 juillet 2014, § 1): `ñ`, `ł`, `ß`, `å`
  are refused even in foreign names ("sans reproduire les éventuels signes diacritiques de la langue étrangère").
- `Document\FR\LegacyIdentityCardNumber` does not validate the issue-month field (the instruction defines the field,
  not its values); `issueMonth()` is `int<0, 99>` (digit lookup table, no clamp) and may lie outside 1-12.
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
- **GB sources:** `www.gov.uk`, `assets.publishing.service.gov.uk` and `www.nidirect.gov.uk` answer to curl (gov.uk
  asset URLs redirect: use `curl -L`). `www.datadictionary.nhs.uk` answers 403 to curl and WebFetch: read its Internet
  Archive captures (`id_` form, gzip body). The PHS data dictionary (`www.ndc.scot.nhs.uk`) redirects to its home page.
  The `@see` lines of `Name\GB\*` and `GreatBritainDriverLicenceNumber` exceed 120 characters (URLs cannot wrap).
- **GB NINO:** HMRC's own illustration `QQ 12 34 56 A` is invalid (Q is excluded); `PZ`/`PY` administrative prefixes are
  accepted (not in the exclusion list). **GB names use `AnyIcaoSpelling`**: HMPO writes a spelling from an official
  document or foreign authority when the ICAO table has none the holder uses, or drops the accent.
- **GB DVLA driver number:** the month field is month + 50 for women; 29 February only for a two-digit year divisible by
  four (no century), as IT. The INS57P check characters are "randomly generated", so they are not verified; a holder
  without first names has no published rule (initials must be a letter + letter or `9`).
- **GB CHI:** check digit format only — no official algorithm text, and PHS accepts modulus 11 or (from August 2026)
  modulus 10 check digits.
- Escaped equivalent mutants in `Document\GB\*`: the `max(1, min(…))` clamps of the day and month getters of `ChiNumber`
  and `GreatBritainDriverLicenceNumber`, `> 50` vs `>= 50` in the DVLA month field (50 is refused), and the `(int)` cast
  of the H&C range check (numeric strings compare numerically). Fixed-length NINO shape: the pattern is unanchored on
  purpose (anchors were equivalent mutants).
- **TR sources:** `www.mevzuat.gov.tr` and `www.nvi.gov.tr` answer to curl (allow the hosts in the sandbox); PRADO
  (`consilium.europa.eu`) is read through Internet Archive captures (`id_` form; pages come gzip-compressed). The PRADO
  passport specimens (images 265357, 306152) are the only official example numbers.
- **TR T.C. Kimlik No is format only:** NVİ says the two check digits come from "bir algoritma" shared with public
  bodies, not published. The widely copied formula is not used, nor the "first digit not 0" rule; both PRADO specimen
  numbers (`12345678901`, `12345678902`) fail that formula anyway.
- **TR names:** `Q W X` are refused (not in the Law 1353 table). The 2013 democratisation package lifted only the
  penal ban (TCK art. 222). Registry practice still refuses them under Law 1353 art. 3 (Nantes consulate note
  11.05.2026; Constitutional Court ruling reported 2022-04-25, secondary). No enacted amendment of 1353/5490 found.
  Hyphen and apostrophe are refused too. The dotted/dotless I are kept as typed; derivation relies on
  `mb_strtoupper()` being locale-independent (tested under `tr_TR.UTF-8`; the test is skipped where no Turkish locale
  is installed). `i` + U+0307 (what `mb_strtolower('İ')` returns) is refused, NFC does not compose it. The derived
  form spells `Ö`/`Ü` as `OE`/`UE` (table default) while the passport specimens print `ORNEK` for `ÖRNEK`: pass the
  passport's MRZ spelling as the override (`TransliterationVariant` accepts it; controller ruling: no TR-specific
  derivation).
- TR passport pattern `[A-Z][0-9]{8}` is unanchored on purpose (length fixed by `NumberFormat`; anchors were
  equivalent mutants).
- **Composite Latin form can exceed 255:** three name parts of 100 characters (ES first + second surname + given) give
  a Latin form above `Icao\PersonName::MAX_LENGTH`; the name is refused with "(Latin form too long)" (every
  `PersonName` documents it in `@throws`). RU and GB accept it when an override is given.
- **`AnyIcaoSpelling` accepts an override without a derived form** (controller ruling, RU and GB): a letter without an
  ICAO spelling (`Kĸ`) or a derived form above 255 is refused only when no override is given
  (`LatinOverridePolicy::withoutDerivedForm()`). The override is stored as typed: `SMYTH<JONES<<MARY` and
  `SMYTH-JONES<<MARY` are not equal (only an override equal to the derived form is normalised away).
- **GB `XXX` forename** (GB.md (4)): with `given === null`, an override `SMITH<<XXX` is read as the mononym `SMITH`
  (and dropped when it is the derived form); with a given name, `XXX` stays.
- **NL apostrophes:** `x' 'y` (a trailing and a leading apostrophe around one space) is refused; no table 36 prefix has
  that shape.
- **DE VSNR:** VKVV § 2 Abs. 3 Satz 2 (unproven birth date) and Abs. 5 Satz 3 (exhausted serial numbers) leave the
  formation of some numbers to unpublished rules; such a number could fail the format or the check digit although it
  was issued. None is known.
- Name codecs document `InvalidPersonNameException` next to `InvalidPayloadException` on `decode()` (a payload of the
  right shape with an invalid part); the shape message says a nullable key may be missing (read as null).
- The `Name\Latin` bases are `@internal`: public property types are the abstract part (`AbstractNamePart`) at runtime,
  PHPStan sees the country's classes through `@extends AbstractFamilyGivenPersonName<FamilyName, GivenName>`. The codec
  base has a second template (`TGivenValue`: `string` for AU DE FR IT TR, `string|null` for NL CH GB) filled by each
  country's `givenValue()`, so `encode()` keeps a precise `given` type.
- `LatinOverridePolicy::effective(Closure $derive, ?override): LatinForm` is the one place that derives, falls back to
  `withoutDerivedForm()` and resolves (RU and the Latin base). `overridePolicy()` runs inside the constructor: build the
  policy from the builder and constants only. `IdentifierSpellings` refuses separators ≠ words − 1 (`LogicException`).

## Script classes must be `\p{sc=...}`, not bare `\p{Latin}` (2026-11-16 UTC)

PCRE2 matches a bare script name (`\p{Latin}`) against Script_Extensions. Unicode 16 added Latn to the
Script_Extensions of U+00B7 MIDDLE DOT (Catalan `l·l`), so on PHP builds with a Unicode-16 PCRE2 (e.g. PHP 8.4.27 on
GitHub runners) `\p{Latin}` accepted `Marcel·lí`, while PHP 8.4.17 locally (PCRE2 10.44, Unicode 15) refused it. All
script classes use `\p{sc=Latin}` / `\p{sc=Cyrillic}` (Script property only); `composer.json` requires
`lib-pcre >=10.40` for the `sc=` syntax. A CI-only name test failure is the first thing to check against this.

## US sources and decisions (plan 3 Task 1, 2026-11-16 UTC)

- **Reachability:** `www.ssa.gov` answers 403 (Akamai) and `travel.state.gov` a Cloudflare block page to curl and
  WebFetch (recent Internet Archive captures hold the block page too: pick a capture with status 200 from the CDX API,
  `web.archive.org/cdx/search/cdx?url=…&filter=statuscode:200`). `fam.state.gov` fails TLS verification (use its
  archive capture; `curl -k` is refused by the auto-mode classifier). `www.irs.gov` (IRM), `www.ecfr.gov` (use the
  `api/renderer/v1/content/enhanced/current/title-26?part=…&section=…` endpoint), `www.cms.gov`, `www.aamva.org` and
  `www.icao.int/sites/default/files/publications/DocSeries/…` answer. The E-Verify ICA docs are a Docusaurus SPA: the
  page text is in `assets/js/<chunk>.js`, found through `main.*.js` and `runtime~main.*.js`.
- **SSN:** only the SSA refusals (area 000/666/900-999, group 00, serial 0000) are applied. The IRM 3.13.5.21 list of
  "never issued" examples (`123-45-6789`, `111-11-1111`, …, "etc.") is open-ended and not refused.
- **US passport book:** any letter is accepted (no letter set published), so a card number `C12345678` also parses as a
  book number. Old 9-digit books stay valid until expiry.
- **US one-word names** (8 FAM 403.1-3(D): printed in the last-name field) are `PersonName($family, null)` (controller
  ruling, GB/CH/NL precedent); a middle name or suffix without a given name is refused (no source defines it).
- **US name suffix in the ICAO form:** included as the last secondary component because 8 FAM 403.1-5(B) treats it as
  part of the name and Doc 9303 Part 3 §4.6 then puts it in the secondary identifier; an override without the suffix
  is refused (model a passport that dropped it as a name without suffix). Table 6.A's default for `Ö` is `OE`, so the
  derived form of `Möller` is `MOELLER`; the crossed-out passport spelling `MOLLER` is an accepted override.

## US DL/ID numbers (plan 3 Task 3, 2026-11-16 UTC)

- `docs/sources/US.md` "Overlap" is the factory test oracle; `DlIdNumberFactoryTest` has every worked example and one
  row per remaining overlap-table class (including the IL ID-card row `11 digits + letter` added with the one-class
  ruling). `123456789` fits 11 states (Review Focus 1).
- The WA legacy PIC keeps `*` as data (`SMITH**12*AB`), so its `masked()` output cannot tell a stored `*` from a
  masked one; `*` is never a separator.
- `class-string` statics cannot be called through `?->` (`$class?->tryFrom()` is a method call on a string); the factory
  resolves the class with `coveredClassFor()` (`?? throw`) instead.
- Infection: `array_values(array_filter(...))` handed to `SubdivisionResolver` escapes `UnwrapArrayValues` (the
  resolver only iterates); build the list with a `foreach` + `[] =`.

## CA sources and decisions (plan 3 Task 4, 2026-11-16 UTC)

- **Reachability:** `www.canada.ca` resets HTTP/2 streams and stalls HTTP/1.1 from curl (no answer in 90 s); WebFetch
  reads it. For verbatim text use the Internet Archive `id_` capture (`web.archive.org/web/2026id_/<url>` redirects to
  the latest; the body is gzip-encoded, `zcat` it). `ircc.canada.ca` help-centre pages answer WebFetch; the pre-2023
  version of qnum=1446 is under the old host `www.cic.gc.ca` in the CDX index.
- **SIN check digit not enforced:** ESDC publishes none; the CRA rejection codes name a "Modulus 10 (Mod 10) check
  digit formula" without the algorithm, and the CRA's own sample SINs `123 456 789` and `111 111 111` fail Luhn. Do not
  add `Checksum::luhn()` to the SIN without an official text giving the algorithm (owner's rule). No leading digit is
  refused (no ESDC "0 or 8 not assigned" text; the CRA ITN starts `08`/`09` and is indistinguishable by shape).
- **CA override policy is `AnyIcaoSpelling`**, not the plan default: the Passport Program's change-name page lets an
  adult drop or invert given names and points to "alternative spellings" for letters it cannot print, so a passport
  name need not be a table spelling. An override equal to the derived form with `<` for a hyphen is dropped (test
  examples for "kept as typed" must differ in a letter, `BERUBE<LEVESQUE<<ANNIE`).
- **Adding a country codec** touches every `tests/Unit/Name/*/PersonNameCodecTest.php` (`#[UsesClass]` for the new
  codec, since each test builds the mapper from `Codecs::all()`) and `CodecsTest`.

## CA provincial documents (plan 3 Task 5b, 2026-11-16 UTC)

- **BC and SK licences are always ambiguous** for `fromNumber()` (both 1-8 digits, both stored padded to 8): test them
  through `fromNumberIn()`. `347631`, `0347631` and `00347631` are one BC number (`value()` `00347631`).
- **ON health version code is not in `value()`**: `OnHealthCardNumber::from('9876543217YM')` has value `9876543217` and
  `versionCode()` `YM`; `equals()`, `__toString()` and `jsonSerialize()` ignore the code, so a round trip through the
  string (or a Doctrine column holding `value`) loses it. Task 8 must decide whether the type stores it.
- **ON MOD 10 is plain Luhn** over the ten digits (`Checksum::luhn()`); the AB-PRG illustration `5584486674` fails it
  and is refused as ON (fits NS only). "Subtract from ten" leaving 10 is read as check digit 0.
- **BC MOD-11:** a remainder of 0 or 1 gives 11 or 10, equal to no digit, so every number with such a prefix is
  refused (`912345678x`, `913579246x`).
- **QC NAM:** no 30-February check (the year has no century); the temporary NAM (`M` at position 11) fails the shape.
  The `birthDay()`/`birthMonth()` clamps leave equivalent Increment/Decrement mutants, as in `ChiNumber`.
- **NS licence:** the padding spaces of a short surname are separators (`FOY  220867001` → `FOY220867001`); a 10-14
  digit input fits the NS length, so the factory reports it as "(fits no jurisdiction)", not by length.
- **Extra state set in `parse()` is write-once, not readonly:** `readonly` there fails PHPStan
  (`property.readOnlyAssignNotInConstructor` and `property.uninitializedReadonly`). `OnHealthCardNumber::$versionCode`
  is an uninitialized `string` ('' = none) and `stored()` throws `LogicException` when `isset()` shows it already set,
  so `$card->__construct(...)` called again cannot change it (the readonly `$value` would throw only after `parse()`).

## MX sources and decisions (plan 3 Task 6a, 2026-11-16 UTC)

- **Reachability:** `dof.gob.mx` (`nota_detalle_popup.php?codigo=…`), `www.gob.mx/cms/uploads/…` and `www.sat.gob.mx`
  answer curl. `repositoriodocumental.ine.mx` serves a Cloudflare challenge, `reposipot.imss.gob.mx` an Incapsula page,
  `ayudarepresentantes.ine.mx` fails with a proxy 502 and `pautas.ine.mx` does not resolve for WebFetch: read their
  Internet Archive `id_` copies (CDX with `filter=statuscode:200`). The DOF popup HTML is UTF-8 with the `Ú` of "Única"
  dropped by the converter (`Clave nica`); table cells use `rowspan` for the "Naturaleza" column (rows 11-16 are all
  "Alfabética").
- **CURP `X` sex** comes from an INE DERFE report quoting RENAPO practice, not from the Instructivo (which still says
  "H para hombre y M para mujer"); `Sex::Unspecified` was added for it. No other class returns it.
- **CURP century letter** is limited to `A-J` (Instructivo and Reglas both say so): a CURP with `K`-`Z` at position 17 is
  refused. If RENAPO ever issues one (more than ten letters needed in a homonymy run), widen it with a quote.
- **RENAPO annexes** (state catalogue, "palabras altisonantes") are listed in the Reglas but not published in them; no
  state list is applied and `NE` is not quoted from an official text.
- **MX names accept `/` and `.`** as RENAPO captures them (`D/AMICO`, `MA. GUADALUPE`; `Name\MX\AbstractMexicanNamePart`:
  `/` a separator, `.` after or between letters). Doc 9303 Part 3 §4.6 omits "all other punctuation characters", so
  `MX\PersonName` passes `['.', '/']` as `$omittedPunctuation` and `LatinPersonNameBuilder` strips it before transliterating
  (`DAMICO`, `MA<GUADALUPE`). `Icao\Transliterator` is unchanged (still refuses them), so no other country's accepted
  characters changed. Package-wide re-check (U+02BC, the ES `l·l`) is deferred to the final review.
- **MX clave de elector positions 1-6 are `A-Z`**: a key with `Ñ` there (INE says only "primeras letras") would be
  refused; open risk, also in the README limits.
- **`ElectorKey` clamps** in `birthMonth()`/`birthDay()` leave equivalent Increment/Decrement mutants (as `ChiNumber`).
  The CURP date avoids them: `isoDate()` builds `YYYY-MM-DD` and `parse()` calls `checkdate()` with int casts (a removed
  cast is a `TypeError` under strict types, so CastInt mutants die).
- **RFC `Ñ`** is multibyte: `Digits::upperCasedOfLength()` uses ASCII `strtoupper()`, so `Rfc::parse()` upper-cases with
  `mb_strtoupper()` and NFC first; `Mask::keepLastCharacters()` is byte-based and is not used for the RFC.

## MX state licences (plan 3 Task 6b, 2026-11-16 UTC)

- **Verdict 1/32 (MX-MEX)**: state laws name the licence number with no layout (NL Ley de licencias art. 18 "b) Número
  de licencia", Jalisco LMT art. 58 "El número de registro de dicha licencia", Tabasco LGTV art. 21 "Número de
  identificación del Titular"); LGMSV art. 29 has no national format. Vendor guides (Kavak "Sonora 14 dígitos") are
  not sources. **Look at online validators' form fields**: the Edomex rule was found only there (`maxlength`,
  `data-val-regex-pattern="^\d{12}$"` of the validator linked from `smovilidad.edomex.gob.mx/validacion_de_licencias`,
  on a `dev-…run.app` host; the older validator `187.237.233.54/ValidadorLicencias` is in the IA). The first pass
  missed it by grepping the gob.mx page only, not the page its button links to.
- **Equivalent mutant**: with one covered entity, Infection's `ArrayOneItem` on
  `MX\DriverLicenceNumberFactory::coveredJurisdictions()` escapes (a one-item list sliced to one item); it dies once a
  second entity is covered.
- **Reachability (that session)**: `semovi.cdmx.gob.mx`, `appsextssl.ebajacalifornia.gob.mx`, `hcnl.gob.mx` (TLS
  failure), `icvnl.gob.mx`, `michoacan.gob.mx`, `tramites.yucatan.gob.mx`, `ventanilla.puebla.gob.mx`,
  `diputados.gob.mx` did not answer (twice); the Internet Archive was offline at first, then answered but had no
  capture of those licence pages. Working copies: NL law on `galeana.gob.mx`,
  CDMX reglamento on `paot.org.mx`, Guanajuato reglamento on `leon.gob.mx`. Retry the dead hosts before concluding a
  state is still unpublished.

## BR sources and decisions (plan 3 Task 7a, 2026-11-16 UTC)

- **CPF algorithm source**: the Receita's own text is the REGRA_VALIDA_CPF of its SPED manuals (ADE COFIS nº 41/2017
  Anexo II on `sped.rfb.gov.br`; same text in the e-Financeira Anexo II 2.1.1 of 2026). It writes the weights "9, 8, …, 1
  e 0, a partir da unidade" with "O resto 10 é considerado 0" — equivalent to the usual `11 - r` formulation (weights
  10..2), but implemented as written. `normas.receita.fazenda.gov.br` is an Angular SPA: curl/WebFetch get an empty
  shell (API under `/api`, not explored).
- **Repeated-digit CPFs are accepted** (Review Focus 4): DATASUS (CADSUS spec RN001.3) and Previdência SIPREV
  (RV_01 "Recusar todos os dígitos iguais") refuse them for their own intake; SIRC lists "Dígitos repetidos" as an
  invalidity reason. None is the Receita Federal, so per the owner's rule they pass. If the owner wants the stricter
  intake rule, it is a one-line refusal plus the test provider flip.
- **TSE**: `www.tse.jus.br` returns 403 to curl and WebFetch; the Internet Archive `id_` copy of 2026-11-10 22:48:11
  UTC of Res. 23.659/2021 was read. Art. 36 gives the table 01-28 and "Módulo 11" without weights (the widely copied
  SP/MG special case is unquoted), so check digits are format only.
- **CNH**: CONTRAN Res. 1.020/2025 art. 10 says "nove caracteres e dois dígitos verificadores" (not "algarismos"), so
  letters are accepted in the first nine. Res. 886/2021 (not revoked by 1.020's art. 140 list) gives "módulo 11" and
  "resto … 0 ou 1 → 0", no weights.
- **CNS**: DATASUS only states "15 dígitos e conter somente números"; the weighted-sum routine (`Σ d·(15..1) ≡ 0 mod
  11`) is published by the ANS (SIB-XML algorithms), not by the Ministério da Saúde/DATASUS; `rni-docs.anvisa.gov.br`
  (says "algoritmo do Ministério da Saúde (em anexo)") answers 403. Not applied; re-check if a DATASUS text surfaces.
- **NIS**: eSocial MOS 2.4 "11 dígitos numéricos, incluído o dv"; no issuer text for the DV algorithm found (the
  DATASUS spec says "algoritmo de verificação (modulo 11) do PIS/NIS/PASEP" without weights).
- **BR names use `AnyIcaoSpelling`**: the PF FAQ ("Nome abreviado no passaporte") describes SINPA abbreviation at 30
  characters per field (particles first, then the penultimate word to one letter). A Brazilian `PersonName` cannot
  exceed `Icao\PersonName::MAX_LENGTH` (two parts of ≤100 characters), so "too long" never triggers here.
- **New codec ⇒ every codec test**: `Codecs::all()` instantiates every codec, and `beStrictAboutCoverageMetadata`
  needs `#[UsesClass(<Cc>PersonNameCodec::class)]` in each test that builds the mapper (14 files for BR).

