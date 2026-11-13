# ICAO Doc 9303 — names and transliteration

Sources (free PDFs published by ICAO, both "Eighth Edition, 2021"; fetched 2026-11-13 UTC):

- Doc 9303 Part 3, *Specifications Common to all MRTDs*:
  <https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf>
  (that URL answered 404 on 2026-11-13 UTC; the same file was fetched from
  `https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf`;
  its record of amendments lists No. 1 of 14/11/22 and No. 2 of 20/3/24, and the pages carry "No. 2" markers).
- Doc 9303 Part 4, *Specifications for Machine Readable Passports (MRPs) and other TD3 Size MRTDs*:
  <https://www.icao.int/sites/default/files/publications/DocSeries/9303_p4_cons_en.pdf>
  (record of amendments: No. 1 of 20/3/24, No. 2 of 20/2/26; the §4.2.3 pages carry the "20/2/26 No. 2" marker).

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

`Transliterator` omits two apostrophe characters: U+0027 APOSTROPHE (`'`) and U+2019 RIGHT SINGLE QUOTATION MARK (`’`,
the one printed in the ICAO examples). Others (e.g. U+02BC MODIFIER LETTER APOSTROPHE) are not covered and are refused.

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
`BENNELONG<WOOLOOMOOLOO<WARRANDYTE<WA<<D`, which satisfies the rule (primary shortened until `<<` and the first secondary
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
