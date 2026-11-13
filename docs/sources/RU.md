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

## Documents

Sources read 2026-11-13 UTC. Official publications first; "quoted from" copies are the secondary texts the quotes were
taken from. No source found publishes an official masking rule for any of these numbers, so every `masked()` keeps the
last four digits of `formatted()` (`Mask::keepLastDigits(formatted(), 4)`). No source publishes an example number
except the SNILS rules (`112-233-445 95`); the other test numbers are synthetic and are computed with the rule given.
Messages give the document name and the character count after separators are removed, or "(too long)" above 40
characters, so a message never holds a run of three digits.

### SNILS (`Document\RU\Snils`)

1. **SFR Order of 17.11.2025 No. 1463** "Об определении форматов сведений для единой формы … (ЕФС-1)" (registered by the
   Ministry of Justice 18.12.2025, No. 84664). Official publication:
   <http://publication.pravo.gov.ru/document/0001202512190014> (19.12.2025). Quoted from the SFR copy:
   <https://sfr.gov.ru/files/branches/tomsk/Prikaz_SFR_ot_17-11-2025_No_1463_prilojenie-doc.docx>.
   - Type `ТипСтраховойНомер`: patterns `\d{3}-\d{3}-\d{3}\d{2}` and `\d{3}-\d{3}-\d{3}-\d{2}`, "в формате XXX-XXX-XXX CC
     или XXX-XXX-XXX-CC".
   - Check ВСЗЛ.ОП.1.1: "Последние 2 цифры Страхового номера должны быть числом, подсчитанным по Алгоритму формирования
     контрольного числа Страхового номера".
   - Check ВС.ЕФС-СЗВ-ТД.1.15: "Значение элемента СНИЛС не должно состоять из одних нулей".
2. **The algorithm the check names**: PFR "Правила проверки документов индивидуального (персонифицированного) учета",
   supplement 2, §8 "Алгоритм формирования контрольного числа Страхового номера" (information message of the PFR of
   21.02.2013, an agency document; not on the publication portal). Quoted from:
   <https://www.consultant.ru/document/cons_doc_LAW_142584/1d9155a863a5949b14b95ecbb536aa84856a2a2e/>

   > Проверка контрольного числа Страхового номера проводится только для номеров больше номера 001-001-998.
   > Контрольное число Страхового номера рассчитывается следующим образом:
   > - каждая цифра Страхового номера умножается на номер своей позиции (позиции отсчитываются с конца)
   > - полученные произведения суммируются
   > - сумма делится на 101
   > - последние две цифры остатка от деления являются Контрольным числом.
   >
   > Например: Указан страховой номер 112-233-445 95 … 1 x 9 + 1 x 8 + 2 x 7 + 2 x 6 + 3 x 5 + 3 x 4 + 4 x 3 + 4 x 2 +
   > 5 x 1 = 95 … Некоторые частные случаи: 99 / 101 = 99; 100 / 101 = 00; 101 / 101 = 00; 102 / 101 = 01

Implemented: control = (Σ dᵢ·(10−i)) mod 101 mod 100, checked when the first nine digits are above `001001998`; the
plan's wording (sum < 100 → sum, 100/101 → 00, > 101 → mod 101 with 100 → 00) is the same function. Eleven zeros are
refused. `formatted()` `112-233-445 95`; `masked()` `***-***-*45 95`.

### INN (`Document\RU\Inn`)

1. **FNS Order of 26.06.2025 No. ЕД-7-14/559@** "Об утверждении Порядка и условий присвоения, применения, а также
   изменения идентификационного номера налогоплательщика" (registered 08.10.2025, No. 83789; in force since
   01.01.2026; repeals Order No. ММВ-7-6/435@ of 29.06.2012). Official publication:
   <http://publication.pravo.gov.ru/document/0001202510090011> (09.10.2025). Quoted from:
   <https://pravo.ppt.ru/prikaz/fns/n-yed-7-14-559-319175>

   > 2. … 2) для физического лица - цифровой код: N N Y Y X X X X X X C C.
   > 3. … 1) NN (2 знака) - для российской организации и физического лица - код управления ФНС России по субъекту
   > Российской Федерации, на территории которого присвоен ИНН; YY (2 знака) - для российской организации и
   > физического лица - индекс, определяемый Федеральной налоговой службой; … 2) XXXXX (XXXXXX): … порядковый номер
   > записи о лице в Реестре налогоплательщиков, … 6 знаков - для физического лица; … 3) C (CC) - контрольное число
   > (1 знак - для организации, 2 знака - для физического лица), рассчитанное по алгоритму, определенному Федеральной
   > налоговой службой
2. SFR Order No. 1463 (above), check ВС.ЕФС-СЗВ-ТД.1.14: "Значение элемента ИНН не должно состоять из одних нулей"
   (twelve zeros are the "no INN" filler in FNS formats).

**The control-digit algorithm is not published** in the order ("по алгоритму, определенному ФНС"), and no official
publication of it was found (searched nalog.gov.ru, publication.pravo.gov.ru and the legal databases on 2026-11-13 UTC;
only aggregator descriptions exist). The package implements the plan's weights — `7,2,4,10,3,5,9,4,6,8` (digit 11) and
`3,7,2,4,10,3,5,9,4,6,8` (digit 12), sum mod 11 mod 10 — which every published description agrees on. This is the one
rule in this file without an official text; see the report/known issues.

**Where the plan loses to the source:** the plan's `taxOfficeCode()` (first four digits) holds only for INNs assigned
under Order ММВ-7-6/435@ (NNNN = code of the assigning tax office). Since 01.01.2026 digits 3–4 are an "индекс,
определяемый ФНС", not part of a tax office code. The class therefore offers `taxAuthorityRegionCode()` (digits 1–2: the FNS code of
the constituent entity) instead. `formatted()` is the 12 digits (`500100732259`); `masked()` `********2259`.

### Internal passport (`Document\RU\InternalPassportNumber`)

**Government Decree of 23.12.2023 No. 2267** "Об утверждении Положения о паспорте гражданина Российской Федерации,
образца и описания бланка паспорта гражданина Российской Федерации". Official publication:
<http://publication.pravo.gov.ru/document/0001202312290097>. Quoted from:
<https://legalacts.ru/doc/postanovlenie-pravitelstva-rf-ot-23122023-n-2267-ob-utverzhdenii/>

Description of the blank, §3:

> Нумерация бланка паспорта состоит из 3 групп цифр. Первые две группы, состоящие из 4 цифр, обозначают серию бланка
> паспорта, третья группа, состоящая из 6 цифр, обозначает номер бланка паспорта.

No check digit and no meaning of the series digits is published: **shape only** (10 digits). `series()` = 4 digits,
`number()` = 6 digits, `formatted()` `45 08 123456`, `masked()` `** ** **3456`.

### Passport issuing unit code (`Document\RU\PassportIssuingUnitCode`)

**MVD Order of 16.11.2020 No. 773** (administrative regulation for issuing and replacing passports; registered
31.12.2020, No. 62009; as amended by Orders No. 854 of 08.11.2021 and No. 552 of 26.07.2022). Official publication:
<http://publication.pravo.gov.ru/Document/View/0001202012310102>. Quoted from:
<https://legalacts.ru/doc/prikaz-mvd-rossii-ot-16112020-n-773-ob-utverzhdenii/>. MVD Order No. 540 of 03.08.2026
(registered 06.10.2026, No. 88632) approves a new regulation; it was not yet in force on 2026-11-13 per the legal
databases and was not read.

- §183.2.2: "В реквизите "Код подразделения" указывается цифровой код, закрепленный за конкретным подразделением по
  вопросам миграции, которое осуществляет оформление паспорта."
- MRZ, lower line, positions 29–42: "Код подразделения по вопросам миграции, выдавшего паспорт (вносится единым
  6-значным числом без дефиса)"; the stamp forms print the code as `000-000`.

No published structure and no check digit: **shape only** (6 digits). `formatted()` `772-078`, `masked()` `**2-078`.

### International passport (`Document\RU\InternationalPassportNumber`)

MVD Order No. 186 of 31.03.2021 and Order No. 996 of 31.12.2019 (see "Names", sources 2 and 3). Order 186, MRZ lower
line: "1 - 9 … Номер документа. Записывается серия и номер единым 9-значным числом", position 10 is the MRZ check
digit of that field (ICAO 7-3-1). Government Decree No. 687 of 18.11.2005 (description of the chip passport blank,
СЗ РФ 2005, No. 48, art. 5037) describes the perforated "серия и номер" without giving the split.

The number has no check digit of its own: **shape only** (9 digits). The split into a two-digit series and a
seven-digit number (`75 1234567`) is the printed convention the plan uses; no source text fixing it was found.
`masked()` `** ***4567`.

### Driving licence (`Document\RU\DriverLicenceNumber`, `Document\RU\DriverLicenceCategory`)

**MVD Order of 13.05.2009 No. 365** "О введении в действие водительского удостоверения" (registered 10.07.2009,
No. 14302; amended by MVD Orders No. 974 of 29.08.2011, No. 6 of 09.01.2014, No. 995 of 20.10.2015, No. 905 of
01.12.2017, No. 676 of 28.09.2020 and No. 1 of 09.01.2024). **Cited by number; predates the portal**
(publication.pravo.gov.ru starts in 2011): no official gazette reference (Российская газета issue or БНА number) for
the 2009 publication was found on 2026-11-13 UTC. Quoted from: <https://legalacts.ru/doc/prikaz-mvd-rf-ot-13052009-n-365/>
Appendix 1 "Описание образца водительского удостоверения":

> В нижней правой части оборотной стороны красным цветом выполнены четырехзначная цифровая серия и шестизначный номер
> удостоверения.
>
> В разделе 5 указываются серия и номер удостоверения, которые должны соответствовать серии и номеру на оборотной
> стороне удостоверения.
>
> [section 9] Указанные категории и подкатегории печатаются в отдельных рамках, которые располагаются в одну строку с
> соблюдением следующей последовательности: A, A1, B, B1, C, C1, D, D1, BE, CE, C1E, DE, D1E, M, Tm, Tb.

The sample blank prints the number as `00 00 000000`. §10 repealed MVD Order No. 120 of 19.02.1999 (whose forms had
Cyrillic letters in the series) from 01.03.2011, and §8 keeps those licences "действительны до окончания указанного в
них срока действия". A licence is issued for ten years (196-FZ Art. 25 §6), so the last 1999-form licence expired by
01.03.2021 — before the 2022–2023 extension of Government Decree No. 353 of 12.03.2022 — and none is valid on
2026-11-13. Letters are therefore refused. No check digit:
**shape only** (10 digits). `formatted()` `99 99 123456`, `masked()` `** ** **3456`. The categories are those of
Federal Law of 10.12.1995 No. 196-FZ "О безопасности дорожного движения", Art. 25 §1 (СЗ РФ 1995, No. 50, art. 4873);
the enum keeps the print order above, which equals the plan's set.

### OMS policy (`Document\RU\OmsPolicyNumber`)

1. **FFOMS Order of 07.04.2011 No. 79** "Об утверждении Общих принципов построения и функционирования информационных
   систем и порядка информационного взаимодействия в сфере обязательного медицинского страхования" (as amended).
   Official text on the agency site: <https://www.ffoms.gov.ru/upload/iblock/56d/56d1f4edbcb3a5d37d20328a012d4e17.pdf>
   ("Приказ ФФОМС от 07.04.2011 N 79 (ред. от 05.03.2020)"; an FFOMS order is not registered by the Ministry of Justice
   and is not on publication.pravo.gov.ru). **Not reachable from this environment** on 2026-11-13 UTC: www.ffoms.gov.ru
   serves a certificate that does not verify (curl error 60), so the quotes come from
   <https://legalacts.ru/doc/prikaz-ffoms-ot-07042011-n-79-ob/>; table of the register data, row
   "Единый номер полиса ОМС <1>", note 1:

   > Контрольный разряд номера полиса обязательного медицинского страхования, вычисляется арифметически в соответствии
   > с методикой расчета, описанной в международном стандарте ISO/HL7 27931:2009 (алгоритм Mod10).
2. **Minzdrav Order of 21.08.2025 No. 496н** "Об утверждении Правил обязательного медицинского страхования"
   (registered 29.08.2025, No. 83407), §35: "Полис представляет собой уникальную последовательность символов в
   машиночитаемом виде…". Quoted from: <https://legalacts.ru/doc/prikaz-minzdrava-rossii-ot-21082025-n-496n-ob-utverzhdenii/>
3. **Length: no official text reachable.** The FFOMS describes the ЕНП as "единый номер полиса из 16 цифр" on
   <https://www.ffoms.gov.ru/news/monitoring-smi/patsient-v-seti-pyat-glavnykh-voprosov-pro-elektronnyy-polis-oms/>
   (seen only as a search snippet; same certificate failure). Neither Order 79 nor Order 496н states the length in
   the text read; 16 digits is the plan's value.

HL7 v2.5 Mod10 (ISO/HL7 27931:2009 is HL7 v2.5; data type CX, check digit scheme `M10` of table 0061; recalled from
the standard, which is not freely published): take the digits in odd positions counting from the right, read them
as a number and double it, prepend the even-position digits, add all digits, subtract from the next multiple of 10
(example `12345` → `5`). Doubling the number gives the same digit sum as doubling each digit and taking the digit sum
of each product (each digit ≥ 5 gives exactly one carry that never cascades, since 2d mod 10 ≤ 8), which is the Luhn
rule; so the policy number is valid when the 16 digits pass `Checksum::luhn()`. The old-form policies (series and
number, before 2011) are out of scope. `formatted()` is the 16 digits (no official grouping found); `masked()`
`************3453`.

### Region (`Document\RU\Region`) — not created

The plan creates `Region implements Subdivision` only if a field encodes a region with a published mapping to
ISO 3166-2:RU. None does:

| Field | What the source says |
|---|---|
| internal passport series | "серию бланка паспорта" — no regional meaning published (Decree 2267) |
| issuing unit code | "цифровой код, закрепленный за конкретным подразделением" — no structure published (Order 773) |
| INN digits 1–2 | "код управления ФНС России по субъекту" — an FNS code; no FNS ↔ ISO 3166-2:RU mapping is published |
| driving licence series | "четырехзначная цифровая серия" — no regional meaning published (Order 365) |
| OMS policy | no published structure besides the control digit |

### Where this section departs from plan 1, task 5 (the source wins)

| Plan | Source | Implemented |
|---|---|---|
| `Inn::taxOfficeCode()` = first 4 digits | since 01.01.2026 NN = FNS directorate, YY = FNS index (ЕД-7-14/559@) | `taxAuthorityRegionCode()` (2 digits) |
| SNILS rule wording | PFR §8 "mod 101, last two digits" | same function; zeros only refused (SFR 1.15) |
| INN checksum "per source" | the algorithm is not published | plan weights, flagged |
| DL "older series with Cyrillic letters only if still recognised" | Order 365 §8: valid until expiry; all expired by 2021 | digits only |
| `Region` | no published ISO mapping | not created |
