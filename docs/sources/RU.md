# Russian Federation — sources

## Names

Sources (read 2026-11-13 UTC). The official publication comes first; the "quoted from" copies are secondary and
are where the quotes below were taken from (the official pages serve the signed text as a PDF/scan).

1. **Federal Law of 15.11.1997 No. 143-FZ "Об актах гражданского состояния"**, Art. 18 (as in force on 2026-11-13).
   Official publication: Собрание законодательства Российской Федерации, 1997, No. 47, item 5340. The Official
   Publication Portal (<http://publication.pravo.gov.ru>) starts in 2011 and has no entry for the 1997 act (searched by
   number and date on 2026-11-13 UTC), so it is cited by number and gazette.
   Quoted from: <https://legalacts.ru/doc/federalnyi-zakon-ot-15111997-n-143-fz-ob/>
2. **Order of the MVD of Russia of 31.12.2019 No. 996** — administrative regulation for passports for travel abroad
   *with an electronic chip* (registered by the Ministry of Justice 27.02.2020, No. 57627; in force since 10.03.2020).
   Appendix No. 9 "Алгоритм ввода персональных данных…" and its appendix "Транслитерация кириллических знаков
   (извлечение)".
   Official publication: <http://publication.pravo.gov.ru/document/0001202002280021> (28.02.2020).
   Quoted from: <https://base.garant.ru/73680659/e21d36dd59d557f67db63a47391d35b1/> (appendix 9),
   <https://base.garant.ru/73680659/58db58f32c2e5d0082ad4f804e086668/> (table).
3. **Order of the MVD of Russia of 31.03.2021 No. 186** — the same regulation for the passport *without* a chip
   (registered by the Ministry of Justice, No. 63515; repeals Order No. 864 of 16.11.2017; in force since 30.05.2021).
   §139.2, §139.3, appendix No. 12.
   Official publication: <http://publication.pravo.gov.ru/document/0001202105190040> (19.05.2021).
   Quoted from: <https://legalacts.ru/doc/prikaz-mvd-rossii-ot-31032021-n-186-ob-utverzhdenii/>
4. **Civil Code of the Russian Federation**, Part One, Art. 19 §1 (a person's name consists of the surname, the given
   name and the patronymic "если иное не вытекает из закона или национального обычая"). Official publication:
   Собрание законодательства Российской Федерации, 1994, No. 32, item 3301.

### 1. Letters and separators in the national form

143-FZ Art. 18 §1 (double surname of a child):

> Двойная фамилия ребенка может состоять не более чем из двух слов, соединенных при написании дефисом.

143-FZ Art. 18 §2 (given name of a child; Art. 58 §1 applies the same rule to a change of name):

> Запрещается запись имени ребенка, которое состоит из цифр, буквенно-цифровых обозначений, числительных, символов и
> не являющихся буквами знаков, за исключением знака "дефис", или их любой комбинации либо содержит бранные слова,
> указания на ранги, должности, титулы.

143-FZ Art. 18 §4: "Отчество ребенка записывается по имени отца, если иное не основано на национальном обычае."

Records are kept in Russian (the MVD orders write the name "на русском языке" in the first line), so the national form
uses the 33 letters of the Russian alphabet: А–Я, Ё (upper and lower case). Latin look-alikes (`a`, `o`, `e`, `c`, …)
and other Cyrillic letters (І, Ї, Ў, Ґ, …) are not Russian letters. Order 996 appendix 9 §14: "При транслитерации
кириллических знаков знак I (порядковый номер 11) в русском языке не используется."

How the package applies this (`Name\RU\*`):

| Part | Separators allowed inside | Why |
|---|---|---|
| `FamilyName` | hyphen, apostrophe, space | Art. 18 §1 joins double surnames by a hyphen; the law sets no character rule for surnames as such, and the MVD MRZ rule "апострофы исключаются" (below) shows that surnames with an apostrophe (`д'Артаньян`) are written in passports. |
| `GivenName` | hyphen, space | Art. 18 §2 allows only the hyphen as a non-letter sign. The space is kept because the MVD orders speak of "имя (имена)" (several given names), which are written separated by a space. No apostrophe. |
| `Patronymic` | hyphen, space | "по имени отца, если иное не основано на национальном обычае" (Art. 18 §4): patronymics of national custom are written in two words (`Гусейн оглы`) or hyphenated. No apostrophe. |

Further rules of the package (designer's calls, not quoted from a source):

- **No word starts with Ъ or Ь** (`Ь`, `Ь-Иванов`, `Иван Ь` are refused): Russian orthography never begins a word with
  them, and Ь has no Latin spelling (§3), so such a word would leave an empty or hyphen-led Latin identifier. Ы may
  start a word (names of Turkic and Yakut origin) and transliterates as `Y`.
- **U+2019 (’) is stored as U+0027 (')** where the apostrophe is allowed (FamilyName): `д’Артаньян` → `д'Артаньян`.
  Where it is not allowed (GivenName, Patronymic) both are refused.

The patronymic is optional: Civil Code Art. 19 §1 ("если иное не вытекает из закона или национального обычая"), and the
MVD orders write "отчество (при наличии)".

### 2. Latin form in the passport for travel abroad

Order 996, appendix 9, §1 and §3 (the same wording in Order 186 §139.2–§139.3):

> 1. … в реквизите "Фамилия" (при наличии) указывается фамилия гражданина … в первой строке - на русском языке и через
> знак "/", во второй строке - способом транслитерации (простого замещения русских букв на латинские) (рекомендуемый
> образец приведен в приложении к Алгоритму).
>
> 3. В реквизите "Имя" указывается имя (имена (при наличии), отчество (при наличии) владельца паспорта … в первой
> строке - на русском языке, во второй дублируется способом транслитерации имя (имена), а отчество не указывается.

Order 186 §139.3: "… во второй строке - дублируется способом транслитерации (только имя (имена))."

MRZ, Order 996 appendix 9 §16.1.1 (identical in Order 186 appendix 12), positions 6–44:

> Двойные или сложные фамилии разделяются символом "<", апострофы исключаются. Разделителем фамилии и имени (имен)
> является два символа "<<".

**The patronymic is not part of the Latin name.** `Name\RU\PersonName::latin()` therefore derives
`FAMILY<<GIVEN` (Иванов Иван Иванович → `IVANOV<<IVAN`).

### 3. Transliteration table (Order 996, appendix to appendix 9)

"Транслитерация кириллических знаков (извлечение)", columns "Порядковый номер / Национальный знак / Рекомендуемая
транслитерация":

| No. | Letter | Latin | | No. | Letter | Latin |
|---|---|---|---|---|---|---|
| 1 | А | A | | 18 | П | P |
| 2 | Б | B | | 19 | Р | R |
| 3 | В | V | | 20 | С | S |
| 4 | Г | G | | 21 | Т | T |
| 5 | Д | D | | 22 | У | U |
| 6 | Е | E | | 23 | Ф | F |
| 7 | Ё | E | | 24 | Х | KH |
| 8 | Ж | ZH | | 25 | Ц | TS |
| 9 | З | Z | | 26 | Ч | CH |
| 10 | И | I | | 27 | Ш | SH |
| 11 | I | I | | 28 | Щ | SHCH |
| 12 | Й | I | | 29 | Ы | Y |
| 13 | К | K | | 30 | Ъ | IE |
| 14 | Л | L | | 31 | Э | E |
| 15 | М | M | | 32 | Ю | IU |
| 16 | Н | N | | 33 | Я | IA |
| 17 | О | O | | | | |

Transcription note: the copy on base.garant.ru prints several cells of the Latin column with Cyrillic look-alike
glyphs (`В`, `Е`, `К`, `М`, `О`, `Р`, `КН`, `СН`) and row 24 with a Latin `X` in the letter column; the table is an
extract of ICAO Doc 9303 Part 3 §6.B, so the cells are read as the Latin letters of that table (`B`, `E`, `K`, …, Х).

Every Russian row equals the recommended column of ICAO table 6.B (see `ICAO.md`); the package keeps using the
verbatim ICAO table (`Icao\Table\Cyrillic`) for those letters.

**Ь (soft sign) has no row.** The method is "простое замещение русских букв на латинские" and the table gives Ь no
Latin replacement, so it is **omitted**: Игорь → `IGOR`, Ольга → `OLGA`. `Name\RU\RussianTransliteration` removes Ь/ь
before handing the part to `Icao\Transliterator`; the ICAO Cyrillic table is not changed. Ъ stays `IE`
(Объедков → `OBIEEDKOV`). Apostrophes are omitted (Order 996 §16.1.1, ICAO Part 3 §4.6): д'Артаньян → `DARTANIAN`.

### 4. A Latin spelling other than the derived one

Order 996 / Order 186 §32 and §140: on an application ("заявление об изменении написания фамилии и (или) имени буквами
латинского алфавита", Order 186 appendix 4) with a supporting document (an earlier passport, a foreign civil-status
record, …), the surname and/or given name are written in Latin "так, как это указано в подтверждающем документе".
Such a spelling is not computable from the national form. The package accepts an override only when it is one of the
ICAO spellings of the national parts (requirements: designer's call); any other spelling is refused.

- An override may write a hyphen of the national form as the MRZ filler (`RIMSKII<KORSAKOV<<NIKOLAI`, Order 996
  §16.1.1: "Двойные или сложные фамилии разделяются символом «<»"); hyphen and filler inside an identifier are
  equivalent, and the override is stored in the national form's spelling (`RIMSKII-KORSAKOV<<NIKOLAI`).
- The search over the ICAO spellings of all parts is capped at 4096 spellings (`Name\LatinSpelling::MAX_SPELLINGS`),
  like `Icao\Transliterator::variants()`. Russian letters have one spelling each, so a Russian name always has 1–2.

### 5. Storage payload and JSON view

`PersonNameCodec` stores `{family, given, patronymic, latin}` where `latin` is the **override only** (`null` when the
Latin form is derived) and refuses any other key. `PersonName::jsonSerialize()` is a view
`{family, given, patronymic, effectiveLatin}` with the Latin form actually in use; because of the different key it is
refused by the codec instead of being read back as "no override".

### 6. Where this note departs from plan 1, task 4 (the source wins)

| Plan expectation | Source | Implemented |
|---|---|---|
| derived `latin()` = `IVANOV<<IVAN<IVANOVICH` | Orders 996/186: "отчество не указывается" | `IVANOV<<IVAN` |
| override "given + patronymic → secondary" | §32/§140 allow a documented spelling | an override with or without the patronymic is accepted when every part is an ICAO spelling (`ivanov<<ivan<ivanovich` and `IVANOV<<IVAN`) |
| Ь (absent from ICAO 6.B) | Order 996 table: no row, simple replacement | omitted |
| letters `\p{Cyrillic}` | Russian alphabet only (Order 996 §14 excludes even І) | `А-ЯЁа-яё` |
