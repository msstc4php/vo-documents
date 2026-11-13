# Türkiye — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| 5490 sayılı Nüfus Hizmetleri Kanunu (RG 29/4/2006, 26153): Madde 8, 19, 46, 47, Geçici Madde 8 and 11 | mevzuat.gov.tr (Cumhurbaşkanlığı Mevzuat Bilgi Sistemi) | <https://www.mevzuat.gov.tr/MevzuatMetin/1.5.5490.pdf> |
| Nüfus Hizmetleri Kanununun Uygulanmasına İlişkin Yönetmelik (RG 23/11/2006, 26355): Madde 4 (p) | mevzuat.gov.tr | <https://www.mevzuat.gov.tr/MevzuatMetin/3.5.200611081.pdf> |
| Nüfus Hizmetleri Uygulama Yönetmeliği (RG 9/5/2020, 31122): Madde 24 (married woman's surname) | mevzuat.gov.tr | <https://www.mevzuat.gov.tr/MevzuatMetin/21.5.2505.pdf> |
| 1353 sayılı Türk Harflerinin Kabul ve Tatbiki Hakkında Kanun (RG 3/11/1928, 1030): Madde 1, 3, Merbut Cetvel | mevzuat.gov.tr | <https://www.mevzuat.gov.tr/MevzuatMetin/1.3.1353.pdf> |
| "T.C. Kimlik No İşlemleri" (04.04.2013), questions 2 and 3 | NVİ (Nüfus ve Vatandaşlık İşleri Genel Müdürlüğü), Tokat İl Nüfus ve Vatandaşlık Müdürlüğü | <https://www.nvi.gov.tr/tokat/tc-kimlik-no-islemleri> |
| Sürücü Belgesi İşlemlerine Dair Talimat, 09/03/2018, E.31712: Madde 10, Ek-1 | NVİ | <https://www.nvi.gov.tr/kurumlar/nvi.gov.tr/mevzuat/nufusmevzuat/talimat/pasaportvesurucuislemleri/09032018_31712_surucu_belgesi_islemlerine_dair_talimat.pdf> |
| 5682 sayılı Pasaport Kanunu (checked for number and spelling rules: none) | mevzuat.gov.tr | <https://www.mevzuat.gov.tr/MevzuatMetin/1.3.5682.pdf> |
| PRADO TUR-AO-02001 (ordinary passport, first issued 01/06/2010), biodata page "Numbering", specimen image 265357 | Council of the EU | <https://www.consilium.europa.eu/prado/en/TUR-AO-02001/index.html>, Internet Archive copy of 2026-08-18 08:47:38 UTC (`web.archive.org/web/20260818084738/…`); image `…/prado/images/265357.jpg` (copy of 2017) |
| PRADO TUR-AO-03001 (ordinary passport, first issued 01/04/2018), specimen image 306152 | Council of the EU | <https://www.consilium.europa.eu/prado/en/TUR-AO-03001/index.html>, Internet Archive copy of 2019-03-19 10:38:01 UTC; image `…/prado/images/306152.jpg` (copy of 2019) |
| PRADO TUR-FO-01001 (driving licence, first issued 01/01/2016) | Council of the EU | Internet Archive copy of 2022-02-09 11:56:21 UTC |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | <https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf> |

Reachability: `www.mevzuat.gov.tr` and `www.nvi.gov.tr` answer to curl. `www.consilium.europa.eu` (PRADO) is read
through its unmodified Internet Archive copies (earlier ruling). An NVİ circular on Law 7039
(`…/genelge/7039 Sayili_Kanunun_Uygulanmasi20172.pdf`) answered with an HTML page instead of the PDF and is not used.
Secondary only (never a rule): Wikipedia "Turkish Identification Number", python-stdnum `stdnum.tr.tckimlik`, and
many blogs and generators that print the check-digit formula below.

## Names (`Name\TR\*`)

1. **Law 1353, Madde 1**: the letters "Latin esasından alınan ve merbut cetvelde şekilleri gösterilen harfler (Türk
   harfleri)" are adopted. **Madde 3**: "nüfus ve evlenme cüzdanları ve kayıtları […] Türk harfleriyle yazılacaktır."
   **Merbut Cetvel** (verbatim, printed letters): `A B C Ç D E F G Ğ H I İ J K L M N O Ö P R S Ş T U Ü V Y Z` and
   `a b c ç d e f g ğ h ı i j k l m n o ö p r s ş t u ü v y z` — 29 letters, no `Q`, `W` or `X`; dotless `I ı` and
   dotted `İ i` are two letters.
2. **5490, Geçici Madde 8 and 11**: names "ile yazım ve imla hatası veya düzeltme işareti kullanılmamasından
   kaynaklanan anlam değişiklikleri bulunan ad ve soyadları" may be changed once — the register writes names with the
   circumflex (düzeltme işareti). Turkish puts it on `â î û` (`Â Î Û`).
3. **Nüfus Hizmetleri Uygulama Yönetmeliği (2020), Madde 24 (1)**: "Evlenen kadın, kocasının soyadını alır. Kocasının
   soyadı ile birlikte önceki soyadını da kullanmak isteyen kadın, bu isteğini evlenme sırasında yazılı olarak […]" —
   a family name can hold two surnames. **5490, Madde 19 (3)**: for a foundling whose record gives no names,
   "nüfus müdürlüğünce ad ve soyad ile ana ve baba adı verilir" — every person has both.
4. No act read (5490, the two regulations, 5682 Pasaport Kanunu) has a rule on the Latin spelling in the
   machine-readable zone or lets a passport keep another spelling.
5. **PRADO specimens** (both passport models): surname `ÖRNEK`, given name `ZEYNEP`; MRZ `P<TURORNEK<<ZEYNEP<<…` —
   the issuing office writes `Ö` as `O`, one of the two table 6.A spellings (`OE or O`). No specimen shows `Ü`.

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` | the 29 letters of (1) in both cases, and `Â â Î î Û û` | space | (1), (2); two surnames side by side (3) |
| `GivenName` | the same | space | several given names (`Ahmet Can`) |

- No act names a hyphen or an apostrophe inside a name; they are refused. `Q`, `W`, `X` are refused: Law 1353 does not
  list them and no act read adds them (a recorded limitation: a name registered with them, if any exists, is refused).
- **Dotted and dotless I** are kept as typed: `Işık`, `İnci`, `ılgaz`, `irmak` are four different strings and never
  re-cased. A lower-case `i` followed by U+0307 (what full Unicode lower-casing makes of `İ`) is not one letter of the
  table and is refused; NFC does not compose it.
- **Derived Latin:** `FAMILY<<GIVEN` through ICAO table 6.A. `İ`, `ı`, `I`, `i` all become `I` (table rows 0130
  "I dot above" = I and 0131 "I without dot (Turkey)" = I). The transliterator upper-cases with `mb_strtoupper()`,
  which applies the Unicode default case mapping and does not depend on the locale (`i` → `I`, never `İ`); the
  table is then read on upper-case letters. `Ç` → C, `Ğ` → G, `Ş` → S, `Â Î Û` → A I U, `Ö` → OE, `Ü` → UE (the
  first spelling of each row, as for every country): `Öztürk Ayşe` → `OEZTUERK<<AYSE`, `Işık İnci` →
  `ISIK<<INCI`.
- **Override policy `Name\TransliterationVariant`** (4): an override must be another table 6.A spelling of both parts.
  `ORNEK<<ZEYNEP` for `Örnek Zeynep` (the PRADO specimen's MRZ, (5)) and `OZTURK<<AYSE` are accepted; a different
  name is refused; an override equal to the derived form is not kept.

Payload (`PersonNameCodec`): `{family, given, latin}` with `latin` the override only; the JSON view is `{family, given,
effectiveLatin}` and is refused by the codec.

## T.C. Kimlik No (`Document\TR\IdentityNumber`)

1. **NVİ, "T.C. Kimlik No İşlemleri", question 2** (verbatim): "T.C. Kimlik Numarası 11 basamaktan oluşan bir
   numaradır. Son iki rakamı doğrulama sayısıdır. Bu son iki basamak ilk dokuz basamaktan bir algoritma ile
   hesaplanmaktadır. Doğrulama sayısı algoritması, sadece bir numaranın tarafımızdan verilen bir T.C. Kimlik Numarası
   olup olmadığı hakkında bilgi vermektedir. Bu algoritma T.C. Kimlik numaralarının doğruluğunu kontrol etmeleri için
   diğer kamu kurum ve kuruluşları ile de paylaşılmaktadır." […] "Ayrıca kişilerin T.C. Kimlik Numaralarında kişinin
   kim olduğu, cinsiyeti, yaşı, dini ve doğum yeri gibi anlamlı bilgiler yoktur."
2. **Regulation 2006, Madde 4 (p)**: "Kimlik numarası: […] elektronik ortamda verilen, onbir rakamdan oluşan Türkiye
   Cumhuriyeti kimlik numarasını". **5490, Madde 46 (1)**: "Türkiye Cumhuriyeti kimlik numarası kişiye bir defa verilir
   ve değiştirilemez." **Madde 47 (1)**: the number appears on "kimlik kartı, vergi kimlik kartı, sürücü belgesi,
   pasaport gibi bütün tanıtıcı belgelerde".
3. PRADO specimens print `12345678901` (2010 passport) and `12345678902` (2018 passport).

Implemented: 11 ASCII digits after the separators of `Internal\Digits::SEPARATORS` are removed. **Format only:** the
check digits exist (1) but NVİ shares the algorithm with public bodies and does not publish it, so they are not
verified (owner's rule). The formula found in secondary sources ("10th = (7 × sum of the odd positions 1-9 − sum of the
even positions 2-8) mod 10, 11th = sum of the first ten mod 10") is not used; both PRADO specimen numbers fail it,
which shows why a specimen cannot be taken as proof either way. The rule "the first digit is never 0" is likewise only
in secondary sources and is not checked. No encoded data (1). The foreigners' identity number (YKN, 5490 Madde 8)
is a separate number; it is not covered. No display form; `masked()` keeps the last four digits (no official masking
rule; 11 > 8): `*******8901`.

## Passport number (`Document\TR\PassportNumber`)

1. **PRADO TUR-AO-02001**, biodata page "Numbering" (verbatim): "Composition: A NNNNNNNN Numbering position: top
   right-hand corner"; the inner pages "Composition: ANNNNNNNN Technique: laser perforation".
2. **PRADO TUR-AO-02001 specimen**: "Pasaport No./Passport No." `U 00000261`, MRZ document number `U00000261`.
3. **PRADO TUR-AO-03001 specimen** (2018 model): "PASAPORT NO./PASSPORT NO." `U20041774`, MRZ `U20041774`. The page
   gives no composition text for this model; the specimen has the same shape.
4. No Turkish act (5682 and its regulations) publishes the number layout.

Implemented: one letter `A-Z` and eight digits after the separators are removed and the letter upper-cased (the space
of `U 00000261` is a separator). No letter is singled out, since (1) writes the letter as `A` (any letter) and other
passport types (service, diplomatic) are not described. **No check digit** (the MRZ check digit is a separate ICAO
field). No display form (the two models print it differently); `masked()` keeps the last four characters (no official
masking rule; 9 > 8): `*****0261`.

## Not covered

- **Sürücü belgesi number**: NVİ instruction of 09/03/2018, **Madde 10** (verbatim): "Sürücü belgesinin verildiği il
  ve ilçe esas alınarak Genel Müdürlük tarafından oluşturulacak elektronik ortamdaki teselsül eden sıraya göre sürücü
  belgesi belge numarası verilir ve bu numara sürücü belgesine de yazılır." **Ek-1** lists field "5. Belge numarası"
  and field "4 .(d) TC kimlik numarası" of the 2016 card. No length or character set is published (PRADO TUR-FO-01001
  shows none either), so the belge numarası has no class. The holder's T.C. Kimlik No in field 4(d) is an
  `IdentityNumber`.
- **Identity card (kimlik kartı) serial number** and the YKN: not candidates of this plan.
