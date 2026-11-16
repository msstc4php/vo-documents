# vo-documents, plan 4 of 4: Asia and Saudi Arabia (CN, JP, KR, IN, ID, SA), release 0.1.0

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Names and identity documents of the last six wave-1 countries, their Doctrine types, the items deferred from
plans 1-3, and the 0.1.0 release of `vo-documents` and `vo-documents-doctrine`.

**Architecture:** As plans 2 and 3: one research note per country, then names, then documents. New here: four
countries write names in a script whose Latin form the national form does not determine (spec: CN, KR, SA, and IN
for Devanagari), so their composites take a **mandatory Latin form**; Japan derives it from a **kana reading** by the
passport Hepburn rules. Numbers typed in fullwidth or Arabic-Indic digits are folded to ASCII before parsing.

**Tech Stack:** as plans 1-3 (PHP 8.4, `msstc4php/vo-base ^1.1.1`, `lib-pcre >=10.40`, PHPUnit, PHPStan max,
Infection).

**Spec:** `docs/brainstorms/2026-11-13-person-documents-and-names-requirements.md`; plans 1-3
(`docs/plans/2026-11-13-001-feat-foundation-plan.md`, `docs/plans/2026-11-13-002-feat-europe-plan.md`,
`docs/plans/2026-11-16-003-feat-americas-plan.md`): their Global Constraints and Decisions apply unchanged.

## Global Constraints (in addition to plans 1-3)

- Owner's rule: a checksum or any other structural rule is enforced only when quoted from an official source; any
  government body's own publication counts (including forms it links), and an unmodified Internet Archive copy of it;
  aggregators and generator sites only as "quoted from". Otherwise "format only". A still-valid number or name is
  never refused, including by a rule that applies only to new registrations (e.g. a minimum word count).
- Script classes use `\p{sc=...}`. Characters that belong to a name but have Script=Common or Inherited are listed
  explicitly (`ー` U+30FC, Arabic harakat U+064B-U+0652, `·` U+00B7), never reached through a wider class.
- Regions: a plainly named string accessor (`administrativeDivisionCode()`, `regionCode()`) unless the source's codes
  map one to one to ISO 3166-2; never a guessed mapping.
- Masks: last 4 kept, 3 when the number is 8 characters or shorter, unless the source publishes a rule.
- A name where the passport spelling is a personal choice never gets a guessed Latin form (spec anti-goal): CN, KR,
  SA, IN-Devanagari require it; nothing checks it against the national form beyond being a valid `Icao\PersonName`.
- Every new value object gets one ORM and one ODM type in `vo-documents-doctrine`.
- Tags and releases only with the owner's confirmation, per tag, and only after green CI; Packagist is the owner's.

## Decisions this plan makes

- **Digit folding (Task 1):** `Internal\Digits` maps fullwidth digits and letters (U+FF10-U+FF19, U+FF21-U+FF3A,
  U+FF41-U+FF5A) and Arabic-Indic digits (U+0660-U+0669, U+06F0-U+06F9) to ASCII before separators are stripped, for
  every document of the package (a widening only; no number accepted today changes value). The ideographic space
  U+3000 joins `Digits::SEPARATORS`.
- **Mandatory-Latin names (Task 1):** `Name\RequiredLatin\AbstractFamilyGivenPersonName` (family part, given part,
  `IcaoPersonName $latin`; `latin()` returns it; `full()` joins family and given with the subclass's
  `nationalSeparator()`) and `Name\RequiredLatin\AbstractFamilyGivenPersonNameCodec` (payload
  `{family, given, latin}`, `latin` as the `Icao\PersonName` string). Equality includes the Latin form. CN and KR use
  them; SA and IN-Devanagari have their own shapes and reuse `Internal\PayloadFields`.
- **CN names:** `Name\CN\{FamilyName, GivenName, PersonName, PersonNameCodec}`; letters `\p{sc=Han}`, and `·` (U+00B7;
  U+30FB and U+2022 folded to it) between words of a minority-nationality name; `nationalSeparator()` `''`.
  Uyghur, Tibetan, Mongolian scripts: not covered (spec).
- **JP names:** `Name\JP\{FamilyName, GivenName, FamilyNameReading, GivenNameReading, PersonName, PersonNameCodec}`.
  Name parts: `\p{sc=Han}`, `\p{sc=Hiragana}`, `\p{sc=Katakana}`, `々`, `ー`. Readings: hiragana or katakana plus `ー`,
  stored as given. `PersonName(FamilyName, GivenName, FamilyNameReading, GivenNameReading, ?IcaoPersonName
  $latinOverride = null)`; the Latin form is derived by an internal `Name\JP\PassportHepburn` from the readings
  (katakana folded to hiragana for the derivation only). `full()` joins with a space U+0020 (designer's call: MOFA
  forms keep 姓 and 名 in separate fields).
- **KR names:** `Name\KR\{FamilyName, GivenName, PersonName, PersonNameCodec}` on the mandatory-Latin base; letters
  `\p{sc=Hangul}`; Hanja forms not covered; `nationalSeparator()` `''`.
- **IN names:** two composites. `Name\IN\PersonName(GivenName $given, ?Surname $surname = null, ?IcaoPersonName
  $latinOverride = null)` on the Latin base (`\p{sc=Latin}`, policy `TransliterationVariant` unless the research
  quotes an MEA rule for another spelling); `Name\IN\Devanagari\PersonName(GivenName, ?Surname, IcaoPersonName
  $latin)` with `\p{sc=Devanagari}` parts (the virama and nukta are Script=Devanagari; the zero-width joiner and
  non-joiner U+200C/U+200D are allowed inside a word). A surname is optional: the Passport Seva form lets it be blank.
- **ID names:** `Name\ID\{FullName, PersonName, PersonNameCodec}`: one `FullName` part (Dukcapil records a single
  "Nama Lengkap"), `\p{sc=Latin}`, separators `-' .`. The Latin form is derived only if the research quotes the
  Imigrasi rule for splitting the name into primary and secondary identifiers; otherwise `PersonName` requires
  `IcaoPersonName $latin` like the mandatory-Latin group, and the source note says why.
- **SA names:** `Name\SA\{GivenName, FatherName, GrandfatherName, FamilyName, PersonName, PersonNameCodec}`;
  `PersonName(GivenName, ?FatherName, ?GrandfatherName, ?FamilyName, IcaoPersonName $latin)`, the optional parts
  becoming required only if a quoted rule makes them so. Letters `\p{sc=Arabic}` plus harakat; tatweel U+0640 is
  removed during normalisation (typographic; two stored values would denote one name).
- **Sub-national documents:** none expected. Where a national format carries the issuer's code (IN driving licence
  state code, JP licence public-safety-commission code) it is a string accessor, since neither maps one to one to
  ISO 3166-2 (IN uses `OD`, `TS`, `UK`; JP has five Hokkaido commissions). A state format older than the national
  one goes to "not covered" unless the state publishes it.
- **Driving licence = identity number** in CN and SA: no class; the README says to use the identity number class.

## Candidates per country

| CC | Candidates |
|---|---|
| CN | 公民身份号码 (GB 11643-1999: ISO 7064 MOD 11-2, birth date, sex, administrative division code; also on the HK/MO/TW residence permits), passport number, 港澳通行证 and 台湾通行证 numbers, foreigner's permanent residence ID card number (2017 and 2023 forms) |
| JP | 個人番号 (My Number: check digit by 総務省令), passport number, 運転免許証番号 (commission code, year, check digit if published), 在留カード番号, 基礎年金番号 |
| KR | 주민등록번호 (birth date and sex by digit 7; the check digit only for numbers issued before 2020-10 if at all, see Review Focus 3), 외국인등록번호, passport number (old `M12345678` and 2021 `M123A4567`), 운전면허번호 |
| IN | Aadhaar (UIDAI: Verhoeff if quoted; masked form `XXXX XXXX 1234`), PAN (holder type from the 4th character), passport number, EPIC voter ID, driving licence number (MoRTH/Sarathi national format), ABHA number |
| ID | NIK (region code, birth date with day + 40 for women, sex; century only if a rule is quoted), NPWP (15 digits; NIK for individuals from 2024 per PMK 112/2022), passport number, SIM number, KK number, BPJS Kesehatan number |
| SA | national ID and iqama numbers (first digit 1 / 2: two classes only if the rule is quoted, else one `IdentityNumber`; check digit only if quoted), passport number, border number |

## Review Focus

1. CN: a lowercase check character `x` is stored as `X`; a birth date of 1900-02-29 is refused and 2000-02-29
   accepted; the 15-digit pre-1999 number is refused by length (no longer valid since 2013, quote it).
2. JP Hepburn edge cases from MOFA's own examples: `っち` → `TCH` (HATCHI), `ん` before B/M/P → `M` (NAMBA, HOMMA,
   SAMPEI), long vowels as MOFA writes them (おおの → ONO), `ー` (Script=Common) in a katakana reading, a reading
   that starts with a small kana or `ん` refused.
3. KR: a 주민등록번호 issued after 2020-10 (random digits after the 7th) must be accepted whatever the old check
   digit gives; digits 5-8 (foreigners) and 9/0 (born 1800s) decode the century as the 시행규칙 says.
4. SA: a name with harakat (Script=Inherited) is accepted and kept, a tatweel is removed, an Arabic-Indic number
   `١٠١٢٣٤٥٦٧٨` parses as `1012345678`.
5. Script mixing: Latin letters in a CN or KR name, Han in a KR name, Devanagari in `Name\IN\PersonName` and Latin in
   `Name\IN\Devanagari\PersonName` are refused; fullwidth digits in a CN or JP number are folded.

---

### Task 1: Digit folding and the mandatory-Latin name base

**Files:** `src/Internal/Digits.php`, `src/Name/RequiredLatin/{AbstractFamilyGivenPersonName,
AbstractFamilyGivenPersonNameCodec}.php`, `tests/Unit/Internal/DigitsTest.php`,
`tests/Unit/Name/RequiredLatin/*` (with a test-only concrete subclass).

**Interfaces:** Produces the two base classes as decided above; `Digits::upperCasedOfLength()` and
`Digits::strippedLength()` keep their signatures and fold before counting.

- [ ] Tests: `１２３４` → `1234`, `ａｂ` → `AB`, `١٢٣` and `۱۲۳` → `123`, U+3000 stripped; one existing number class of
  each earlier plan accepts its value typed in fullwidth digits; the base: national form, `full()`, `latin()` is the
  given form, equality differs when only the Latin form differs, codec round trip, a missing or invalid `latin`
  payload refused without echoing it.
- [ ] `make check`, `make test`, `make infection`; commit `feat: fold fullwidth and Arabic-Indic digits; mandatory-Latin name base`.

### Tasks 2-8: one per country or part (same steps as plan 2 Tasks 2-9)

Each task: **research** into `docs/sources/<CC>.md` (official source per candidate with link or act number, format,
rules with their quotes, encoded data, mask rule, official examples; name rules: alphabet, parts, passport spelling),
**names** as decided above, **documents** for the covered set, `src/Codecs.php`, tests (official examples,
separators/NBSP/fullwidth, wrong length, wrong characters, every quoted rule, encoded data, `formatted()` only where a
display form is quoted, `masked()`, no input echoed), then `make check`, `make test`, `make infection` and the commit.

| Task | Scope | Specific tests | Commit |
|---|---|---|---|
| 2 | CN | Review Focus 1 and 5; minority name `阿沛·阿旺晋美` with each folded dot; `administrativeDivisionCode()` of an ID and of a residence permit (`81`/`82`/`83` prefix) | `feat: China names and documents` |
| 3 | JP names: `docs/sources/JP.md` name part, `Name\JP\*`, `PassportHepburn` from MOFA's ヘボン式ローマ字綴方表 (every row of the table is a data-provider case) and its rules for ん, っ and long vowels; the override policy is `AnyIcaoSpelling` if the research quotes MOFA's 非ヘボン式 permission, else `TransliterationVariant` over the long-vowel spellings MOFA lists (OH, OO, OU) | Review Focus 2; reading with mixed hiragana/katakana; a kanji given name with a katakana reading | `feat: Japan names` |
| 4 | JP documents | My Number check digit with the ministry's own example; licence commission code accessor; residence card letters | `feat: Japan documents` |
| 5 | KR | Review Focus 3 and 5; `birthDate()` and `sex()` of a resident and of a foreigner number; passport old and 2021 forms | `feat: Korea names and documents` |
| 6 | IN | Review Focus 5; a mononym (no surname) in both composites; Aadhaar masked `XXXX XXXX 1234` if UIDAI's rule is quoted; PAN `holderType()` for `P` | `feat: India names and documents` |
| 7 | ID | NIK of a woman (day 41-71) gives `sex()` Female, day 70 (30 February + 40) refused; a one-word name accepted although Permendagri 73/2022 asks two for new registrations; NPWP 15 and 16 digits | `feat: Indonesia names and documents` |
| 8 | SA | Review Focus 4; a name without father/grandfather parts; national ID vs iqama as the research rules | `feat: Saudi Arabia names and documents` |

### Task 9: Doctrine types

- [ ] In `vo-documents-doctrine`: one ORM and one ODM type per new value object (`vod_<cc>_<snake_name>`), samples,
  registrar entries; the completeness test passes. `make check`, `make test`, `make infection`; commit
  `feat: Doctrine types for the Asian documents`.

### Task 10: Items deferred from plans 1-3

**Files:** as each item needs; `docs/sources/{MX,BR,ES}.md`, `src/Document/CA/HealthCardNumberFactory.php`.

- [ ] Re-try the hosts unreachable in plan 3 (MX: BCN, CMX, MIC, NLE, PUE, YUC; BR: BA, CE, MG, PE, RJ, RN, RS);
  cover a state whose format is now readable, as Task 3 of plan 3 (class, factory entry, overlap row, completeness
  test, Doctrine type), and record the date and result per host otherwise.
- [ ] `HealthCardNumberFactory`'s no-match message for an input that carries a version code (Ontario) names
  `OnHealthCardNumber::fromCard()`, without the input.
- [ ] Look for a Spanish registry text (RRCC, DGSJFP instrucción) on `l·l`; quote it in `ES.md`, or record that none
  was found and the acceptance rests on the never-refuse rule.
- [ ] Decide whether `ŀ`/`Ŀ` folding moves from ES to `AbstractNamePart` (it does if any other wave-1 country's
  source allows them; otherwise ES only, recorded in `known-issues.md`).
- [ ] Commit `fix: items deferred from plans 1-3`.

### Task 11: Documentation

- [ ] `README.md` class tables for the six countries and a complete "not covered" list for all 20 countries,
  `CHANGELOG.md` `[Unreleased]`, `.claude/docs/*` in both repos; commit `docs: Asian documents and names`.

### Task 12: Release 0.1.0

The controller runs this task itself (subagents cannot run the review tools).

- [ ] PhpStorm `get_inspections` on every `src/` file of both repos if the MCP is reachable (otherwise say so);
  `/acc:code-review high` on each whole package; fix every finding; gates and push.
- [ ] ODM integration against a real MongoDB: run the bridge's skipped tests locally against `mongo:7` if Docker is
  reachable; otherwise confirm with the owner that the CI "PHPUnit" job (it has a `mongo:7` service) ran them.
- [ ] `vo-documents`: `CHANGELOG.md` `[Unreleased]` → `[0.1.0] - <date UTC>`, commit, push, green CI; **ask the owner**,
  then tag `v0.1.0` and push the tag; wait for its CI.
- [ ] `vo-documents-doctrine`: re-run CI; the three jobs that needed the tag must now pass. `CHANGELOG.md` 0.1.0,
  commit, push, green CI; **ask the owner**, then tag `v0.1.0`. Packagist and the GitHub release page are the
  owner's.
