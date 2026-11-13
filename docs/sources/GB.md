# United Kingdom — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| National Insurance Manual, NIM39110 "National Insurance Numbers (NINOs): Format and Security: What a NINO looks like" (updated 12 November 2026) | HMRC | <https://www.gov.uk/hmrc-internal-manuals/national-insurance-manual/nim39110> |
| Basic Passport Checks (4 colour simple version, 1.1; passports introduced 1988-2025) | HM Passport Office | <https://assets.publishing.service.gov.uk/media/6979d8b4128d9c1d09a98c73/2025_BasicPassportChecks_1.1.pdf> |
| Names: names that cannot be used in passports, version 19.0 (published for Home Office staff on 27 July 2026, page updated 27 August 2026) | HM Passport Office | <https://www.gov.uk/government/publications/names-names-that-cannot-be-used-in-passports/names-names-that-cannot-be-used-in-passports-accessible> |
| Names: how to show uncommon names in passports, version 18.0 (page updated 4 November 2026) | HM Passport Office | <https://www.gov.uk/government/publications/how-to-show-uncommon-names-in-passports-caseworker-guidance/names-how-to-show-uncommon-names-in-passports> |
| Names: the names we use in passports, version 9.0 (27 July 2026) | HM Passport Office | <https://www.gov.uk/government/publications/the-names-we-use-in-passports-caseworker-guidance/names-the-names-we-use-in-passports> |
| INS57P "Information on driving licences" (leaflet 7/25), "How to check your driver number" | DVLA | <https://assets.publishing.service.gov.uk/media/688c6e8f6c7eb66caea94e04/ins57p-information-on-driving-licences.pdf> |
| "The photocard driving licence explained" | DVA (nidirect) | <https://www.nidirect.gov.uk/articles/photocard-driving-licence-explained> |
| "Check someone's driving licence information" | DVA (nidirect) | <https://www.nidirect.gov.uk/services/check-someones-driving-licence-information> |
| NHS Data Model and Dictionary, attribute NHS NUMBER | NHS England | <https://www.datadictionary.nhs.uk/attributes/nhs_number.html>, Internet Archive copy of 2025-09-14 05:33:12 UTC: <http://web.archive.org/web/20250914053312/https://www.datadictionary.nhs.uk/attributes/nhs_number.html> |
| NHS Data Model and Dictionary, attribute COMMUNITY HEALTH INDEX NUMBER | NHS England | <https://www.datadictionary.nhs.uk/attributes/community_health_index_number.html>, Internet Archive copy of 2025-09-13 14:34:28 UTC |
| NHS Data Model and Dictionary, attribute HEALTH AND CARE NUMBER | NHS England | <https://www.datadictionary.nhs.uk/attributes/health_and_care_number.html>, Internet Archive copy of 2025-09-10 10:41:19 UTC |
| phsmethods `chi_check()` reference (version 1.2.0.9000) | Public Health Scotland | <https://code.publichealthscotland.scot/phsmethods/reference/chi_check.html> (through WebFetch) |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | <https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf> |

Reachability: `www.gov.uk`, `assets.publishing.service.gov.uk` and `www.nidirect.gov.uk` answer to curl.
`www.datadictionary.nhs.uk` answers 403 to curl and WebFetch: its unmodified Internet Archive captures were read (`id_`
form; earlier ruling: such a copy counts as the official source). The PHS data dictionary
(`www.ndc.scot.nhs.uk/Dictionary-A-Z/…`) redirects to its home page, so the CHI definition is quoted from the NHS Data
Dictionary and the PHS reference page. The HMPO names guidance PDF v18.0 answers 410; the accessible HTML (v19.0) is
used. The DfI leaflet `motoring-information-driving-licences-2012_1.pdf` answers 404. Secondary only (never a rule):
Wikipedia "NHS number", python-stdnum `gb.nhs`, motoring press articles on the driver number.

## Names (`Name\GB\*`)

1. **HMPO, names that cannot be used, § 2.2** (verbatim): "The systems we use to issue passports, meet the passport
   standards set by the International Civil Aviation Organisation (ICAO) and does not allow us to issue a British
   passport in a name that contains: numbers / symbols / punctuation marks (except for hyphens or apostrophes in the
   name fields) / diacritical characters (a mark, point or sign added to a letter) or accents marks (there are
   alternate spellings of names to take these into account, known as transliterations)".
2. Same section: "Digital Application Processing (DAP) will accept an apostrophe anywhere in a name but does not accept
   names that include diacritical characters […]. In most languages, there are alternative spellings of names that take
   this into account. They have been agreed by the ICAO and are published in a transliteration table." "Some older
   passports may have diacritical characters or accent marks, as UK passports issued overseas before 1 April 2014
   included them."
3. Same section, "Name not found on the transliteration table": "you must ask the customer to provide written
   evidence. For example: an official document / a letter from a government authority in the country where the name is
   from / a letter from the relevant embassy or High Commission in the UK". And: "If you, the examiner, are told to
   issue the passport in the requested name, without the accent mark, you must: […]".
4. **HMPO, uncommon names, "When the customer only has one name"**: "we will issue their passport with 'XXX' as the
   forename […] Update the surname field on DAP with the customer's name. Update the forename field to 'XXX'. Add
   observation OBTZ […]: THE HOLDER IS [insert name] (THE HOLDER HAS ONE NAME AND REFERENCE TO XXX DOES NOT FORM PART
   OF THEIR NAME)."
5. Same guidance, "Welsh names that include diacritical characters": "The system cannot accept diacritical characters
   or accent marks. If a Welsh name has one, you must refer to Names: names that cannot be used in passports guidance."
   Welsh "ap surname" names and "Junior" go into the surname field.
6. § 2.2 also: numbers in a name are spelled out ("Thomas 7 Jones" → "Thomas Seven Jones").

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` (surname) | `\p{Latin}` | hyphen, apostrophe, space | the national form is the name of the birth certificate or change-of-name document, which may hold diacritics (Welsh `ŵ ŷ`, Irish `á`) (2), (5); hyphens and apostrophes (1); digits are refused (6) |
| `GivenName` (forenames) | the same | the same | the same |

- No UK act or agency publishes a letter list for the name itself, so the shared Latin base applies (as DE, IT, ES).
  Apostrophes are accepted between letters (`O'Brien`, `D'Arcy`); a leading or trailing apostrophe is refused by the
  shared word rule (the DAP remark "anywhere in a name" describes the input system, not a name form).
- **Forenames optional:** a person with one name (4) is `PersonName($family, null)`; the Latin form is the mononym
  `FAMILY`, since `XXX` "does not form part of their name".
- **Derived Latin:** `SURNAME<<FORENAMES` through ICAO table 6.A: `O'Brien, Siân` → `OBRIEN<<SIAN`, `Llŷr` → `LLYR`,
  `Smith-Jones, Mary Ann` → `SMITH-JONES<<MARY<ANN`.
- **Override policy `Name\AnyIcaoSpelling`** (3): when the table has no spelling the customer wants, HMPO writes the
  spelling shown by an official document or a foreign authority's letter, or the name without the accent mark; the
  passport therefore keeps a documented spelling that need not be a table 6.A variant. Any valid ICAO name is kept as
  the override; one equal to the derived form is not kept.

Payload (`PersonNameCodec`): `{family, given, latin}` with `given` null for a person with one name and `latin` the
override only; the JSON view is `{family, given, effectiveLatin}` and is refused by the codec.

## National Insurance number (`Document\GB\NationalInsuranceNumber`)

NIM39110 (verbatim):

> A NINO is made up of 2 letters, 6 numbers and a final letter, which is always A, B, C, or D.
> It looks something like this: QQ 12 34 56 A
> This is an example only and should not be used as an actual number.
>
> The Prefix — […] All prefixes are valid except:
> The characters D, F, I, Q, U, and V are not used as either the first or second letter of a NINO prefix.
> The letter O is not used as the second letter of a prefix.
> Prefixes BG, GB, KN, NK, NT, TN and ZZ are not to be used

The same page lists administrative numbers that look like NINOs: `OO` (refused by the second-letter rule), `FY`
(refused, `F`), `NC…m/f` (refused by the suffix), `PP999999P` (refused by the suffix), `TN` (refused by the list), and
`PZ`/`PY` ("not used since August 2002"; their prefixes are not in the exclusion list, so they are accepted). The TRN
(`11 a1 11 11`) "is not a NINO" and does not fit the shape.

Implemented: 9 characters after the separators are removed and letters upper-cased: two prefix letters under the three
exclusion rules, six digits, suffix `A`-`D`. **No check digit** exists. The example `QQ 12 34 56 A` is refused (`Q` is
excluded, and HMRC says it is an example only); the tests use `AB 12 34 56 C`. `formatted()` is the spaced form of
NIM39110 (`AB 12 34 56 C`); `masked()` keeps the last four characters in that layout (9 > 8): `** ** *4 56 C`.

## Passport number (`Document\GB\PassportNumber`)

Basic Passport Checks, every design from 1988 to the series D e-Passport of 2025: "Numbering — 9 digits, printed on
page 1" (2020 and 2025 designs: "9 numerical digits"). For the 2010 design onward: "The thin film patch contains a
unique serial number which consists of 3 alpha characters and 4 numeric followed by a check symbol. This number […]
should not be confused with the 9 digit passport number." "A check symbol is included within the laser perforated
passport number" — that symbol is part of the perforation, not of the number, and no algorithm is published.

Implemented: 9 digits; format only (no published check digit; the MRZ check digit is separate). No display form;
`masked()` keeps the last four digits: `*****6789`.

## Driving licence number, Great Britain (`Document\GB\GreatBritainDriverLicenceNumber`)

INS57P "How to check your driver number" (verbatim), example `MORGA 657054 SM 9IJ **`:

> A. This is the first 5 letters of your surname. If your surname has fewer than 5 letters, the remaining spaces will
> be made up using the number 9 (for example, MAN99).
> B. • The first and last numbers are the year of birth
> • The second and third numbers are the month of birth. (If you are a female, '5' is added to the second number and
> the total used as the second digit. So if you are a woman born in October the second and third numbers would be 60.)
> • The fourth and fifth digits show the day of your birth
> C. The first 2 initials of your first names. If you have only one initial then the second character will be a '9'.
> D. Computer check digits randomly generated as a secure measure.
> E. This is the licence issue number which will increase by one with every licence issued. This is not part of the
> driver number.

Implemented: 16 characters (A-D; the issue number E is not part of the number and is refused as extra length):

- characters 1-5: letters followed by zero or more `9` (A);
- 6-11: digits; the month field (7-8) is `01`-`12` or `51`-`62` (B); the day (9-10) must exist in that month, and
  29 February is accepted only when the two-digit year (6 and 11) is divisible by four — no century is encoded, so only
  dates impossible in every century are refused (same rule as IT);
- 12-13: a letter, then a letter or `9` (C);
- 14-16: letters or digits (D, "randomly generated": **no check**; the example `9IJ` mixes both).

Encoded data: `sex()` (`HasSex`: month field above 50 → female, otherwise male, (B)), `birthYearDigits()` (two digits,
no century, so no `HasBirthDate`), `birthMonth()`, `birthDay()`. Example `MORGA657054SM9IJ`: female, born 5 July of
a year ending in 64. `formatted()` is the leaflet's grouping `MORGA 657054 SM 9IJ`; `masked()` keeps the last four
characters in that layout: `***** ****** *M 9IJ`. Not covered: the person without first names (no rule for C).

## Driving licence number, Northern Ireland (`Document\GB\NorthernIrelandDriverLicenceNumber`)

nidirect "The photocard driving licence explained": "Driver number (5) Your unique eight digit NI driver number."
"Check someone's driving licence information": "You will need: the eight characters of their driving licence number".
The licence is issued by DVA (field 4c).

Implemented: 8 digits; format only. No official example is published (tests use synthetic numbers). `masked()` keeps
the last three digits (8 characters): `*****678`.

Why two classes, not the AU `SubdivisionResolver` pattern: the formats never overlap (16 characters vs 8 digits), so
a number always identifies its issuer. DVLA covers England, Scotland and Wales (three ISO 3166-2 subdivisions), so no
one-to-one `Subdivision` mapping exists for it.

## NHS number (`Document\GB\NhsNumber`)

NHS Data Dictionary, NHS NUMBER (verbatim): "the primary identifier of a PERSON, is a unique identifier for a PATIENT
within the NHS in England and Wales." "The NHS NUMBER is 10 numeric digits in length. The tenth digit is a check digit
used to confirm its validity. The check digit is validated using the Modulus 11 algorithm and the use of this algorithm
is mandatory. There are 5 steps in the validation of the check digit:

> Step 1 Multiply each of the first nine digits by a weighting factor as follows: [position 1-9 → factor 10, 9, 8, 7,
> 6, 5, 4, 3, 2]
> Step 2 Add the results of each multiplication together.
> Step 3 Divide the total by 11 and establish the remainder.
> Step 4 Subtract the remainder from 11 to give the check digit. If the result is 11 then a check digit of 0 is used.
> If the result is 10 then the NHS NUMBER is invalid and not used.
> Step 5 Check the remainder matches the check digit. If it does not, the NHS NUMBER is invalid."

HEALTH AND CARE NUMBER: "in the same format as the NHS NUMBER in England (3 3 4 format with the tenth digit being a
modulus 11 check digit)".

Implemented: 10 digits; **the check digit is verified** (steps 1-5; a result of 10 is refused). No range rule is
published for NHS numbers, so none is applied. No official example; tests use numbers computed with the algorithm
(`943 476 5919`, `401 023 2137`, `100 000 0060` for the "11 → 0" case). `formatted()` is the `3 3 4` form;
`masked()` keeps the last four digits: `*** *** 5919`.

## Health and Care number, Northern Ireland (`Document\GB\HealthAndCareNumber`)

NHS Data Dictionary, HEALTH AND CARE NUMBER (verbatim): "uniquely identifies a PATIENT within the NHS in Northern
Ireland. […] is ten numeric digits in length, and is in the same format as the NHS NUMBER in England (3 3 4 format with
the tenth digit being a modulus 11 check digit). HEALTH AND CARE NUMBERS are however unique from NHS NUMBERS allocated
in England as they are taken from a range of numbers reserved for Northern Ireland (320 000 001 to 399 999 999 plus
check digit)."

Implemented: 10 digits, the first nine in `320000001`-`399999999` ("(range)" otherwise), check digit verified with the
NHS steps. `formatted()` `320 000 0015`, `masked()` `*** *** 0015`. An H&C number is also a valid `NhsNumber` (no NHS
range rule refuses it).

## CHI number, Scotland (`Document\GB\ChiNumber`)

NHS Data Dictionary, COMMUNITY HEALTH INDEX NUMBER (verbatim): "uniquely identifies a PATIENT on the Community Health
Index (Scotland) within the NHS in Scotland. It is the equivalent of the NHS NUMBER in England and Wales. The COMMUNITY
HEALTH INDEX NUMBER is ten numeric digits in length, and is constructed as follows: PERSON BIRTH DATE (in format
DDMMYY); two further digits; a ninth digit which is always an even number for females and an odd number for males; an
arithmetical check digit."

PHS `chi_check()`: the first six digits must be a valid date; "starting in August 2026, some CHIs will only pass if
they meet the modulo 10 criteria" (a CHI passes either a modulus 11 or a modulus 10 check). Neither algorithm is
published as text by PHS or ISD Scotland (the reference page gives only the function's switches).

Implemented: 10 digits; the date `DDMMYY` must exist (month 01-12, day of that month; 29 February only for a two-digit
year divisible by four, no century encoded); **format only for the check digit** (no official algorithm text, and two
algorithms are in use since August 2026). Encoded data: `sex()` (`HasSex`, ninth digit), `birthDay()`, `birthMonth()`,
`birthYearDigits()` (no `HasBirthDate`: no century). No published display form; `masked()` keeps the last four
digits: `******6719` (for `1203456719`). No official example; tests use synthetic numbers.

## Masking rule

None published for any number; the package rule applies (8 characters → keep 3, longer → keep 4).

## Not covered

- The HMRC Temporary Reference Number (TRN): "it is not a NINO".
- The passport book serial number of the thin film patch (3 letters, 4 digits, check symbol): a security feature, not
  the passport number.
- The GB licence issue number (E): not part of the driver number.
