# ICAO Doc 9303 — names and transliteration

Sources (free PDFs published by ICAO, both "Eighth Edition, 2021"; fetched 2026-11-13 UTC):

- Doc 9303 Part 3, *Specifications Common to all MRTDs*:
  <https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf>
  (that URL answered 404 on 2026-11-13 UTC; the same file was fetched from
  `https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf`;
  its record of amendments lists No. 1 of 14/11/22 and No. 2 of 20/3/24, and the pages carry "No. 2" markers).
- Doc 9303 Part 4, *Specifications for Machine Readable Passports (MRPs) and other TD3 Size MRTDs*:
  <https://www.icao.int/sites/default/files/publications/DocSeries/9303_p4_cons_en.pdf>
  (record of amendments: No. 1 of 20/3/24, No. 2 of 20/2/26; the §4.2.3 pages carry the "20/2/26 No. 2" marker).

For the machine readable zone (section 5) two more parts were fetched on 2026-11-21 UTC:

- Doc 9303 Part 5, *Specifications for TD1 Size Machine Readable Official Travel Documents (MROTDs)*:
  <https://www.icao.int/sites/default/files/publications/DocSeries/9303_p5_cons_en.pdf>.
- Doc 9303 Part 6, *Specifications for TD2 Size Machine Readable Official Travel Documents (MROTDs)*:
  <https://www.icao.int/sites/default/files/publications/DocSeries/9303_p6_cons_en.pdf>.

Part 3 §4.6 delegates truncation to "the form factor specific Parts 4 to 7"; the truncation rule and the
examples below therefore come from **Part 4 §4.2.2 / §4.2.3** (TD3, 39-character name field).

## 1. Convention for writing the name (Part 3 §4.6)

> To achieve global interoperability, the primary and secondary identifiers in the MRZ shall be printed using upper-case
> OCR-B characters, illustrated in Figure 4, without diacritical marks, and conform to the number of character positions
> available. [...] The issuing State or organization shall transliterate national characters using only the allowed
> OCR-B characters and/or truncate, as specified in the form factor specific Parts 4 to 7 of Doc 9303. Transliteration
> tables for the most commonly used Latin, Cyrillic and Arabic families of languages are provided in Section 6.
>
> The primary identifier [...] shall be followed by two filler characters (<<). The secondary identifier [...] shall be
> written starting in the character position immediately following the two filler characters.
>
> If the primary or secondary identifiers have more than one name component, each component shall be separated by a
> single filler character (<).
>
> Filler characters (<) should be inserted immediately following the final secondary identifier (or following the
> primary identifier in the case of a name having only a primary identifier) through to the last character position in
> the machine readable line.
>
> [...] If the primary and secondary identifiers, written in the relevant machine readable line using the above
> procedure, exceed the available character positions, then truncation shall be carried out using the procedure set out
> in the form factor specific Parts 4 to 7 of Doc 9303. In all other cases, the name shall not be truncated.
>
> Numeric characters shall not be used in the name fields of the MRZ.
>
> Hyphen: Where a hyphen appears between two name components, it shall be represented in the MRZ by a single filler
> character (<). (i.e. hyphenated names shall be represented as separate components).
> Example VIZ: MARIE-ELISE MRZ: MARIE<ELISE

Comma (VIZ `ERIKSSON, ANNA MARIA` → MRZ `ERIKSSON<<ANNA<MARIA`) and apostrophe rules are in the same section:

> Apostrophe: This shall be omitted; name components separated by the apostrophe shall be combined, and no filler
> character shall be inserted in its place in the MRZ.
> Example VIZ: D’ARTAGNAN MRZ: DARTAGNAN

The same section ends with (verbatim):

> Other punctuation characters: All other punctuation characters shall be omitted from the MRZ (i.e. no filler
> character shall be inserted in their place in the MRZ).

`Transliterator` omits two apostrophe characters: U+0027 APOSTROPHE (`'`) and U+2019 RIGHT SINGLE QUOTATION MARK (`’`,
the one printed in the ICAO examples). Other punctuation is omitted only where a country's name parts accept it: the
`$omittedPunctuation` argument of `Name\Latin\AbstractLatinPersonName` lists it (MX: `.` and `/`, RENAPO records them)
and `LatinPersonNameBuilder` removes it before transliterating (`D/Amico` → `DAMICO`). Per §4.6 "All other
punctuation characters shall be omitted from the MRZ (i.e. no filler character shall be inserted in their place in
the MRZ)", so each such character simply disappears. ES lists the middle dot U+00B7 of the Catalan `l·l` (`Marcel·lí`
→ `MARCELLI`; final review of plan 3). `Transliterator` itself still refuses any other punctuation, as do the parts of
the other countries.

U+02BC MODIFIER LETTER APOSTROPHE is an apostrophe in use (Hawaiian, Ukrainian and some English texts type it):
`Name\AbstractNamePart::parse()` stores it as U+0027 wherever the apostrophe is an allowed separator, exactly as
U+2019 (GB `Oʼneill` → `O'neill`, MRZ `ONEILL`). A part without an apostrophe separator (TR, RU given names and
patronymics) still refuses it; `Transliterator` called directly refuses it too.

## 2. Transliteration tables (Part 3 §6)

"The following tables contain the most commonly used national characters of the Latin, Cyrillic and Arabic families
of languages." The tables list upper-case characters only.

### 6.A Transliteration of Multinational Latin-based Characters

| Unicode | National character | Description | Recommended transliteration |
|---|---|---|---|
| 00C0 | À | A grave | A |
| 00C1 | Á | A acute | A |
| 00C2 | Â | A circumflex | A |
| 00C3 | Ã | A tilde | A |
| 00C4 | Ä | A diaeresis | AE or A |
| 00C5 | Å | A ring above | AA or A |
| 00C6 | Æ | ligature AE | AE |
| 00C7 | Ç | C cedilla | C |
| 00C8 | È | E grave | E |
| 00C9 | É | E acute | E |
| 00CA | Ê | E circumflex | E |
| 00CB | Ë | E diaeresis | E |
| 00CC | Ì | I grave | I |
| 00CD | Í | I acute | I |
| 00CE | Î | I circumflex | I |
| 00CF | Ï | I diaeresis | I |
| 00D0 | Ð | Eth | D |
| 00D1 | Ñ | N tilde | N or NXX |
| 00D2 | Ò | O grave | O |
| 00D3 | Ó | O acute | O |
| 00D4 | Ô | O circumflex | O |
| 00D5 | Õ | O tilde | O |
| 00D6 | Ö | O diaeresis | OE or O |
| 00D8 | Ø | O stroke | OE |
| 00D9 | Ù | U grave | U |
| 00DA | Ú | U acute | U |
| 00DB | Û | U circumflex | U |
| 00DC | Ü | U diaeresis | UE or UXX or U |
| 00DD | Ý | Y acute | Y |
| 00DE | Þ | Thorn (Iceland) | TH |
| 0100 | Ā | A macron | A |
| 0102 | Ă | A breve | A |
| 0104 | Ą | A ogonek | A |
| 0106 | Ć | C acute | C |
| 0108 | Ĉ | C circumflex | C |
| 010A | Ċ | C dot above | C |
| 010C | Č | C caron | C |
| 010E | Ď | D caron | D |
| 0110 | Đ | D stroke | D |
| 0112 | Ē | E macron | E |
| 0114 | Ĕ | E breve | E |
| 0116 | Ė | E dot above | E |
| 0118 | Ę | E ogonek | E |
| 011A | Ě | E caron | E |
| 011C | Ĝ | G circumflex | G |
| 011E | Ğ | G breve | G |
| 0120 | Ġ | G dot above | G |
| 0122 | Ģ | G cedilla | G |
| 0124 | Ĥ | H circumflex | H |
| 0126 | Ħ | H stroke | H |
| 0128 | Ĩ | I tilde | I |
| 012A | Ī | I macron | I |
| 012C | Ĭ | I breve | I |
| 012E | Į | I ogonek | I |
| 0130 | İ | I dot above | I |
| 0131 | ı | I without dot (Turkey) | I |
| 0132 | Ĳ | ligature IJ | IJ |
| 0134 | Ĵ | J circumflex | J |
| 0136 | Ķ | K cedilla | K |
| 0139 | Ĺ | L acute | L |
| 013B | Ļ | L cedilla | L |
| 013D | Ľ | L caron | L |
| 013F | Ŀ | L middle dot | L |
| 0141 | Ł | L stroke | L |
| 0143 | Ń | N acute | N |
| 0145 | Ņ | N cedilla | N |
| 0147 | Ň | N caron | N |
| 014A | Ŋ | Eng | N |
| 014C | Ō | O macron | O |
| 014E | Ŏ | O breve | O |
| 0150 | Ő | O double acute | O |
| 0152 | Œ | ligature OE | OE |
| 0154 | Ŕ | R acute | R |
| 0156 | Ŗ | R cedilla | R |
| 0158 | Ř | R caron | R |
| 015A | Ś | S acute | S |
| 015C | Ŝ | S circumflex | S |
| 015E | Ş | S cedilla | S |
| 0160 | Š | S caron | S |
| 0162 | Ţ | T cedilla | T |
| 0164 | Ť | T caron | T |
| 0166 | Ŧ | T stroke | T |
| 0168 | Ũ | U tilde | U |
| 016A | Ū | U macron | U |
| 016C | Ŭ | U breve | U |
| 016E | Ů | U ring above | U |
| 0170 | Ű | U double acute | U |
| 0172 | Ų | U ogonek | U |
| 0174 | Ŵ | W circumflex | W |
| 0176 | Ŷ | Y circumflex | Y |
| 0178 | Ÿ | Y diaeresis | Y |
| 0179 | Ź | Z acute | Z |
| 017B | Ż | Z dot above | Z |
| 017D | Ž | Z caron | Z |
| 1E9E | ẞ | double s (Germany) | SS |

### 6.B Transliteration of Cyrillic Characters

| Unicode | National character | Recommended transliteration |
|---|---|---|
| 0401 | Ё | E (except Belorussian = IO) |
| 0402 | Ђ | D |
| 0404 | Є | IE (except if Ukrainian first character, then =YE) |
| 0405 | Ѕ | DZ |
| 0406 | І | I |
| 0407 | Ї | I (except if Ukrainian first character, then =YI) |
| 0408 | Ј | J |
| 0409 | Љ | LJ |
| 040A | Њ | NJ |
| 040C | Ќ | K (except in the language spoken in the former Yugoslav Republic of Macedonia = KJ) |
| 040E | Ў | U |
| 040F | Џ | DZ (except in the language spoken in the former Yugoslav Republic of Macedonia = DJ) |
| 0410 | А | A |
| 0411 | Б | B |
| 0412 | В | V |
| 0413 | Г | G (except Belorussian, Serbian, and Ukrainian = H) |
| 0414 | Д | D |
| 0415 | Е | E |
| 0416 | Ж | ZH (except Serbian = Z) |
| 0417 | З | Z |
| 0418 | И | I (except Ukrainian = Y) |
| 0419 | Й | I (except if Ukrainian first character, then =Y) |
| 041A | К | K |
| 041B | Л | L |
| 041C | М | M |
| 041D | Н | N |
| 041E | О | O |
| 041F | П | P |
| 0420 | Р | R |
| 0421 | С | S |
| 0422 | Т | T |
| 0423 | У | U |
| 0424 | Ф | F |
| 0425 | Х | KH (except Serbian and in the language spoken in the former Yugoslav Republic of Macedonia = H) |
| 0426 | Ц | TS (except Serbian and in the language spoken in the former Yugoslav Republic of Macedonia = C) |
| 0427 | Ч | CH (except Serbian = C) |
| 0428 | Ш | SH (except Serbian = S) |
| 0429 | Щ | SHCH (except Bulgarian = SHT) |
| 042A | Ъ | IE |
| 042B | Ы | Y |
| 042D | Э | E |
| 042E | Ю | IU (except if Ukrainian first character, then =YU) |
| 042F | Я | IA (except if Ukrainian first character, then =YA) |
| 046A | Ѫ | U |
| 0474 | Ѵ | Y |
| 0490 | Ґ | G |
| 0492 | Ғ | G (except in the language spoken in the former Yugoslav Republic of Macedonia = GJ) |
| 04BA | Һ | C |

### Transcription notes (read before changing the tables)

- **The Unicode column is authoritative.** In the PDF the glyph printed for three rows does not match its code and
  description: row 0402 prints Ћ (U+040B), row 040E prints the lower-case ў (U+045E), row 0110 "D stroke" prints Ð
  (U+00D0); rows 0131 and 0474 lose their glyph on text extraction (`I`, `V`). The tables above and in code use the
  code point of the Unicode column: Ђ U+0402, Ў U+040E, Đ U+0110, ı U+0131, Ѵ U+0474. U+040B Ћ is not in the table.
- **No soft sign.** Ь (U+042C) is **absent** from table 6.B (as are Ѐ, Ѓ, Ѝ, Ћ). Doc 9303 does not say whether an
  absent character is dropped; this package therefore treats it as uncovered (the transliterator throws). Russian names
  omit Ь per the MVD table (`RU.md` §3), outside this table.
- **Lower case** is not listed; the transliterator upper-cases its input (`mb_strtoupper`) before the lookup, so
  lower-case `ß` becomes `SS` (matching ẞ → SS) and `ı` becomes `I`.

### Letters outside table 6.A (package decision, not ICAO)

Table 6.A lists the common precomposed letters only; Vietnamese (`Ễ` in Nguyễn) and Romanian comma-below letters
(`Ș` U+0218, `Ț` U+021A) are missing, and Doc 9303 says nothing about them. `LatinMultinational` therefore spells a
`\p{sc=Latin}` letter that is not in the table as its base letter: canonical decomposition (NFD), combining marks
(`\p{Mn}`) dropped, then the table again (or the letter itself if it is A-Z). `Nguyễn` → `NGUYEN`, `Ș` → `S`,
`Ț` → `T`, `Ǿ` → `Ø` → `OE`. A letter without a canonical base (`Ƒ`, `Ʒ`) is still refused. The fallback applies to
the Latin table only; table 6.B and the Russian MVD table have none. Like the tables themselves it changes only in a
major version (stored overrides are re-validated on hydration).

### Characters with alternative spellings ("or" in table 6.A)

| Character | Spellings, in the table's order (the first is this package's default) |
|---|---|
| Ä | AE, A |
| Å | AA, A |
| Ñ | N, NXX |
| Ö | OE, O |
| Ü | UE, UXX, U |

Appendix B §B.4.1 (informative) explains the `XX` forms: "There are a group of nine characters that are treated
specially, for example, the character “Ñ” can be transliterated into the MRZ as “NXX”, thus preserving its uniqueness
and importance for database searches." Example: "Térèsa CAÑON" → `CANXXON<<TERESA`.

The Cyrillic "except …" entries are **language-specific rules, not alternatives**: they are recorded here but not
offered by `Transliterator::variants()` (a Russian name must not accept the Serbian `H` for Х). A later plan adds them
as language tables (UA, BY, RS, MK, BG).

## 3. Truncation (Part 4 §4.2.2, §4.2.3)

Data Element Directory, Part 4 §4.2.2, line 1, positions 6–44:

> Filler: When all components of the primary and secondary identifiers and required separators (filler characters) do
> not exceed 39 characters in total, all name components shall be included in the MRZ and all unused character
> positions shall be completed with filler characters (<) repeated up to position 44 as required.
>
> Truncation of the name: When the primary and secondary identifiers and required separators (filler characters)
> exceed the number of character positions available for names (i.e. 39), they shall be truncated as follows:
>
> Characters shall be removed from one or more components of the primary identifier until three character positions
> are freed, and two filler characters (<<) and the first character of the first component of the secondary identifier
> can be inserted. The last character (position 44) shall be an alphabetic character (A through Z). This indicates that
> truncation may have occurred.
>
> Further truncation of the primary identifier may be carried out to allow characters of the secondary identifier to be
> included, provided that the name field shall end with an alphabetic character (position 44). This indicates that
> truncation may have occurred.

Part 4 §4.2.3:

> The name field in the MRZ of the MRP allows for a maximum of 39 characters in the upper line. [...] If the total
> number of characters in the name, including filler characters, is 39 or fewer the name shall not be truncated.
>
> In truncating the name components, the last character of the name field shall be an alphabetic character (A to Z
> inclusive) as an indication that truncation has occurred [...].

Examples, §4.2.3.1–§4.2.3.4 (name field only, i.e. without the `PPUTO` prefix of the PDF; width 39):

| § | VIZ | MRZ name field |
|---|---|---|
| 4.2.3.1 a | ERIKSSON, ANNA MARIA | `ERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<` |
| 4.2.3.1 b | HENG, DEBORAH MING LO | `HENG<<DEBORAH<MING<LO<<<<<<<<<<<<<<<<<<` |
| 4.2.3.1 c | SMITH-JONES, SUSIE MARGARET | `SMITH<JONES<<SUSIE<MARGARET<<<<<<<<<<<<` |
| 4.2.3.1 d | O’CONNOR, ENYA SIOBHAN | `OCONNOR<<ENYA<SIOBHAN<<<<<<<<<<<<<<<<<<` |
| 4.2.3.1 e | VAN DER MUELLEN, MARTIN | `VAN<DER<MUELLEN<<MARTIN<<<<<<<<<<<<<<<<` |
| 4.2.3.1 e | AL-BASRI, HUDA MUHAMMAD JAWAD | `AL<BASRI<<HUDA<MUHAMMAD<JAWAD<<<<<<<<<<` |
| 4.2.3.1 e | VILARCHAO FERNANDEZ, JOSE RAMON | `VILARCHAO<FERNANDEZ<<JOSE<RAMON<<<<<<<<` |
| 4.2.3.1 f | ARKFREITH | `ARKFREITH<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<` |
| 4.2.3.1 f | SATRIYA SUDARPA | `SATRIYA<SUDARPA<<<<<<<<<<<<<<<<<<<<<<<<` |
| 4.2.3.2 a | NILAVADHANANANDA, CHAYAPA DEJTHAMRONG KRASUANG | `NILAVADHANANANDA<<CHAYAPA<DEJTHAMRONG<K` |
| 4.2.3.2 b | NILAVADHANANANDA, ARNPOL PETCH CHARONGUANG | `NILAVADHANANANDA<<ARNPOL<PETCH<CHARONGU` |
| 4.2.3.3 a | BENNELONG WOOLOOMOOLOO WARRANDYTE WARNAMBOOL, DINGO POTOROO | `BENNELONG<WOOLOOMOOLOO<WARRANDYTE<W<<DI` |
| 4.2.3.3 b | (same) | `BENNELONG<WOOLOOM<WARRAND<WARNAM<<DINGO` |
| 4.2.3.3 c | (same) | `BENNEL<WOOLOO<WARRAN<WARNAM<<DINGO<POTO` |
| 4.2.3.4 | PAPANDROPOULOUS, JONATHON WARREN TREVOR | `PAPANDROPOULOUS<<JONATHON<WARREN<TREVOR` |

§4.2.3.3 a–c are titled "One or more components truncated to initials", "… truncated", "… truncated to a fixed number
of characters": three valid results for one name, so the rule allows several truncations. §4.2.3.4 is a name of exactly
39 characters: "Even though there is an alphabetic character in the 44th position [...] this name has not been truncated
but it must be assumed that it has been truncated." Part 4 fixes the 39-character TD3 width; TD1 (30) and TD2 (31) are
in Parts 5 and 6 and use the same procedure.

### The deterministic procedure `PersonName::mrzField(width)` implements

1. Write the name: hyphens and spaces in every identifier become `<`, primary `<<` secondary.
2. When there is a secondary identifier and `width >= 4`, cut the primary identifier to `width - 3` characters (a
   shorter primary is unchanged), so `<<` and at least the first secondary character fit (§4.2.2, first paragraph of
   the rule).
3. Cut the whole name to `width` and pad with `<`. A name that fits is unchanged (§4.2.3.1, §4.2.3.4).
4. A cut is the first N characters. When the cut would end on a filler, the last letter of the nearest earlier
   component that has two or more letters is removed and the cut is retried.

Step 4 makes the field end with A–Z **unless every component before the cut is a single letter** (e.g. `A<<B<C` at
width 5 → `A<<B<`): no letter can then be removed without dropping a component, the cut keeps its trailing filler
stripped and the field is padded. ICAO gives no rule for that case.

Steps 2–4 reproduce §4.2.3.2 a and b exactly. For the §4.2.3.3 name they give
`BENNELONG<WOOLOOMOOLOO<WARRANDYTE<WA<<D`, which satisfies the rule (primary shortened until `<<` and the first
secondary
character fit, last character alphabetic) but is none of the three PDF results.

Derived examples (computed with the procedure above, not printed by ICAO; used in `PersonNameTest`):

| Name | Width | `mrzField()` |
|---|---|---|
| BENNELONG WOOLOOMOOLOO WARRANDYTE WARNAMBOOL, DINGO POTOROO | 20 | `BENNELONG<WOOLOOM<<D` |
| ERIKSSON, ANNA MARIA | 15 | `ERIKSSON<<ANN<M` |
| IVANOV, JEAN-PIERRE | 13 | `IVANOV<<JEA<P` |

## 4. Where this note departs from plan 1, task 3 (the source wins)

| Plan expectation | Source | Implemented |
|---|---|---|
| `toLatin('Объедков') === 'OBIEDKOV'` | 6.B: Ъ = IE, Е = E | `OBIEEDKOV` |
| `variants('Müller') === ['MUELLER', 'MULLER']` | 6.A: Ü = "UE or UXX or U" | `['MUELLER', 'MUXXLLER', 'MULLER']` |
| truncation "by the Doc 9303 Part 3 rule" | Part 3 §4.6 delegates to Parts 4–7 | Part 4 §4.2.2–§4.2.3 procedure above |
| `mrzField(20)` "equals the truncation example" | ICAO examples are all width 39 | width-39 examples verbatim; width 20 from the derived table |
| `Ь` (needed by Russian names) | absent from 6.B | uncovered: the transliterator throws |

## 5. Machine readable zone (Part 3 §4.8-§4.10, §5; Part 4 §4.2; Part 5 §4.2; Part 6 §4.2)

`Icao\Mrz\{Td3, Td2, Td1}` read and check an MRZ; `MachineReadableZone::fromString()` picks the format by the length.

### 5.1 Formats

TD3 (Part 4): 2 lines of 44. TD2 (Part 6): 2 lines of 36. TD1 (Part 5): 3 lines of 30. Part 3 §4.10: "Machine readable
data shall be arranged from left to right in fixed-length fields in two lines (upper and lower) except for TD1 size
travel documents where there are three lines (upper, middle and lower)", unused positions "completed with filler
characters (<)". Positions (1-based):

| Field | TD3 (Part 4 §4.2.2) | TD2 (Part 6 §4.2.2) | TD1 (Part 5 §4.2.2) |
|---|---|---|---|
| Document code | 1: 1-2 | 1: 1-2 | 1: 1-2 |
| Issuing state | 1: 3-5 | 1: 3-5 | 1: 3-5 |
| Name | 1: 6-44 | 1: 6-36 | 3: 1-30 |
| Document number, check digit | 2: 1-9, 10 | 2: 1-9, 10 | 1: 6-14, 15 |
| Nationality | 2: 11-13 | 2: 11-13 | 2: 16-18 |
| Date of birth, check digit | 2: 14-19, 20 | 2: 14-19, 20 | 2: 1-6, 7 |
| Sex | 2: 21 | 2: 21 | 2: 8 |
| Date of expiry, check digit | 2: 22-27, 28 | 2: 22-27, 28 | 2: 9-14, 15 |
| Optional data (TD3: personal number) | 2: 29-42, check digit 43 | 2: 29-35 | 1: 16-30, and 2: 19-29 |
| Composite check digit | 2: 44 | 2: 36 | 2: 30 |

Document code: Part 4 §4.4 "The first letter P shall identify the document as an MRP"; Part 5 and Part 6 note k "The
first character shall be A, C or I". Sex (Part 4 table, position 21; Part 5 table, line 2 position 8; Part 6 table,
position 21; 8th ed. 2021, checked again 2026-11-21 UTC): "F = female; M = male; < = unspecified." (the VIZ uses `X`;
Part 4 note p, Part 5 and Part 6 note f: "Where an issuing State or organization does not want to identify the sex, the
filler character (<) shall be used in this field in the MRZ and an X in this field in the VIZ."). The VIZ field
description (Part 4 field 11, Parts 5 and 6 field 05) reads "the capital letter F for female, M for male, or X for
unspecified". Decision (wave 3 review CR-015): `<` is defined as "unspecified", so `sex()` returns `Sex::Unspecified`
for it and `MachineReadableZone` implements `HasSex`; `X` is a VIZ value only, not an MRZ one, and stays refused.
The second character is, per Part 5 and
Part 6 note k, "at the discretion of the issuing State or organization except that i) V shall not be used, ii) I shall
not be used after A (i.e. AI), and iii) C shall not be used after A (i.e. AC) except in the crew member certificate".
`Td1` and `Td2` refuse a second character `V` and `AI`; `AC` stays allowed, because the crew member certificate is a
valid use and the code cannot tell it apart. `Td3` checks only the first character `P` (Part 4 §4.4).

### 5.2 Check digit (Part 3 §4.9)

> The check digits shall be calculated on modulus 10 with a continuously repetitive weighting of 731 731 ...
> [...] the symbol < shall be used to complete vacant positions and shall be given the value of zero [...] the
> characters A to Z shall have the values 10 to 35 consecutively.

Covered characters (Part 4 §4.2.4, Part 5 §4.2.4, Part 6 §4.2.4): the document number, the birth date, the expiry
date, the TD3 personal number, and the composite digit over TD3 lines 2 positions 1-10, 14-20, 22-43; TD2 line 2
positions 1-10, 14-20, 22-35; TD1 line 1 positions 6-30 with line 2 positions 1-7, 9-15, 19-29. Part 4 for the TD3
personal number check: "When the personal number field is not used and filler characters (<) are used in
positions 29 to 42, the check digit may be zero or the filler character (<) at the option of the issuing State or
organization." `Td3` accepts `<` only for a blank field. Part 3 Appendix A examples: `520727` has check digit 3,
`AB2134<<<` has 5; the specimens below are used in the tests.

### 5.3 Long document numbers (Part 5 note j, Part 6 note j)

> if the document number has more than 9 characters, the 9 principal characters shall be shown in the MRZ [...]. They
> shall be followed by a filler character instead of a check digit to indicate a truncated number. The remaining
> characters of the document number shall be shown at the beginning of the field reserved for optional data elements
> [...] followed by a check digit and a filler character.

Part 5 §4.2.4 fixes where the number ends: "Long document number check digit | 6 – 14, 16 – 28 | 17 – 18 (one digit
only). Note: Position 15 contains '<' and is excluded from the check digit calculation. The position of the last
digit of a long document number is in the range of 16 – 28. [...] Since the check digit follows the last digit of the
document number, its position is in the range of 17 – 29. The check digit is followed by '<'." Fillers only complete
vacant positions (Part 3 §4.10), so the number has none after its ninth character: the overflow runs from the first
optional-data position to the first `<`, its last character is the check digit (a digit, with at least one number
character before it) and the optional data starts after that `<`. Exactly one reading exists; a value where it does
not hold (no `<`, no digit before it, wrong check digit, or a `<` among the nine principal characters) is refused. TD3 has no long form; a `<` check digit there is
refused. Part 6 note j says the same for TD2 (overflow at positions 29 to 35).

### 5.4 Dates (Part 3 §4.8)

> Dates in the MRZ of the MRTD shall be shown as a six-digit string consisting of the last two digits for the year (YY)
> immediately followed by two digits for the number of the month (MM) and by two digits for the day (DD). [...] If all
> or part of the date of birth is unknown, the relevant character positions shall be completed with filler characters
> (<).

So an unknown part of the birth date is `<<` per year, month or day; the class returns a null `birthDate()` and keeps
`birthDateDigits()`. The expiry date has no unknown form and must be six digits.

**Not in Doc 9303:** the century of the two-digit years (the word does not occur in Part 3; the Part 3 Appendix A TD3
specimen even has the expiry `960108`). Chosen, conservatively and with the caller supplying the reference date (no
hidden clock):

- `birthDate($reference)`: the most recent date not after `$reference` (UTC date), taking the current century or the one
  before; null when neither holds the date, which only happens for 29 February `00` before 29 February 2000 (1900 is
  not a leap year, and 1600 is no birth date);
- `birthDate($reference)` searches from the reference year clamped to 1-9999 and compares year, month and day as integers,
  so a reference after 9999 still finds the date (`991231` with a reference in 10050 is 9999-12-31);
- `expiryDate($reference)` (wave 3 review CR-016, controller ruling): the century that puts the year in
  [reference year - 84, reference year + 15] (UTC year, clamped to 1-9999). Documents are valid for about 10 to 15
  years, so a far past date is an archived document rather than one valid for decades: `760101` with the reference
  2026-11-21 is 1976 (the earlier "nearest century" rule gave 2076). A 29 February `00` whose year in that window is not
  a leap year (2100) takes the nearest century that has it among the four either side, the earlier one on a tie
  (1600, 2000, 2400); the year stays in 1-9999.

### 5.5 Codes (Part 3 §5)

> Two- and three-letter codes shall be obtained from the [ISO 3166] maintenance agency [...] Codes not included in
> [ISO 3166-1], such as extensions for other States and organizations, or other exceptions, are outlined in the
> following table:

The table lists Germany as `DE` / `D` (the MRZ field is `D` padded with two fillers, `D<<`, by §4.10), Kosovo `KS` / `RKS`, British categories `GBD`, `GBN`, `GBO`, `GBS`, `GBP`, the EU `EUE`, United
Nations `UNO`, `UNA`, `UNK`, other issuing authorities (`XBA`, `XIM`, `XCC`, `XCE`, `XCO`, `XEC`, `XPO`, `XES`, `XMP`,
`XOM`, `XDC`) and the persons without a defined nationality `XXA`, `XXB`, `XXC`, `XXX`. `issuingState()` and
`nationality()` return the code as printed without its fillers; `issuingCountry()` and `nationalityCountry()` give
an ISO 3166-1 `CountryCode` for an alpha-3 code and for `D` (Germany, from that table), and null for every other
code outside ISO 3166.

### 5.6 Specimens used in the tests

| Format | Source | Lines |
|---|---|---|
| TD3 | Part 4 Appendix A | `PPUTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<` / `L898902C36UTO7408122F3404159ZE184226B<<<<<16` |
| TD2 | Part 6 Appendix A | `I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<` / `D231458907UTO7408122F1204159<<<<<<<6` |
| TD1 | Part 5 Appendix A | `I<UTOD231458907<<<<<<<<<<<<<<<` / `7408122F1204159UTO<<<<<<<<<<<6` / `ERIKSSON<<ANNA<MARIA<<<<<<<<<<` |

The Part 4 Figure A-2 second TD3 line (`L898902C36UTO7408122F2504155184226<<<<<<<<<18`) is not used: the extracted PDF
text of that line does not have 44 characters. The Part 5 and Part 6 name examples at full width
(`PAPANDROPOULOUS<<JONATHOON<ALEC`, `NILAVADHANANANDA<<CHAYAPA<DE<K`) and Part 4 §4.2.3.4
(`PAPANDROPOULOUS<<JONATHON<WARREN<TREVOR`) are used as full-width name fields. There is no Doc 9303 specimen of a
long document number or of a birth date with unknown parts; the tests build those with the check digits computed by
an independent script.

### 5.7 Masking

`masked()` replaces the document number, the optional data, the birth date and their check digits (and the composite
digit) with `*`. Doc 9303 has no masking rule; `*` is not an MRZ character, so a masked MRZ never parses.
