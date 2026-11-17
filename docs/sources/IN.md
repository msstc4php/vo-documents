# India — sources

All dates UTC. Sources read 2026-11-17.

| Source | Publisher | Where read |
|---|---|---|
| A UID Numbering Scheme (Kanakia, Nadhamuni, Sarma, May 2010) | UIDAI | Internet Archive copy of the file UIDAI hosted at `www.uidai.gov.in/documents/A_UID_Numbering_Scheme.pdf`: <https://web.archive.org/web/20100704211109/www.uidai.gov.in/documents/A_UID_Numbering_Scheme.pdf> (`pdftotext`) |
| What is Masked Aadhaar? (FAQ) | UIDAI | Internet Archive copy of 2022-08-11 <https://web.archive.org/web/20220811180652/https://www.uidai.gov.in/283-faqs/aadhaar-online-services/e-aadhaar/1887-what-is-masked-aadhaar.html> (the live URL answers 404) |
| Aadhaar Handbook 2021 | UIDAI | Internet Archive copy <https://web.archive.org/web/2022/https://uidai.gov.in/images/AadhaarHandbook2021.pdf> (12-digit number, 16-digit VID; no format rule) |
| Tutorial "Permanent Account Number (PAN)" [As amended by Finance Act, 2025] | Income Tax Department | Internet Archive copy <https://web.archive.org/web/2025/https://incometaxindia.gov.in/tutorials/1.permanent%20account%20number%20(pan).pdf> (the live host answers 403 to `curl` and WebFetch; the PDF is dated 2025-10-14) |
| Instructions for filling of Passport Application Form and Supplementary Form (V3.0) | MEA, Passport Seva | <https://www.passportindia.gov.in/AppOnlineProject/pdf/ApplicationformInstructionBooklet-V3.0.pdf> |
| Personalization of Indian Passports using Next Generation Automatic ... (tender corrigendum of 2017-05-26), 4.4 provisional database structure of the data page | MEA | <https://www.mea.gov.in/Portal/Tender/3234_1/1_corrigendum_dated_26.05.2017-1.pdf> |
| Explainer on Ayushman Bharat Health Accounts (ABHA), 2024-04-04 | PIB (Ministry of Health and Family Welfare) | <https://www.pib.gov.in/PressReleasePage.aspx?PRID=2017129&reg=48&lang=2> (read with `curl`; WebFetch answers 403) |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6 | ICAO | see `ICAO.md` |

Reachability: `incometaxindia.gov.in`, `www.pib.gov.in` (WebFetch), `abdm.gov.in`, `www.eci.gov.in` pages answer 403/406
to WebFetch and plain `curl`; `ceotripura.nic.in` (ECI Manual on Electoral Rolls) does not connect;
`healthid.ndhm.gov.in` does not connect. Two further attempts at the ECI declaration PDF with browser-like request
headers were refused by the sandbox and not repeated. Secondary only (never a rule, never cited in code): python-stdnum,
Wikipedia "Permanent account number", "Voter ID (India)", Protean (`proteantech.in`, a company, not the ITD), validator
and insurer blogs.

## Names

**MEA Passport Seva instruction booklet, Column 2.1:** "You must furnish your full name as you want it to appear on your
passport. For instance, if you have filled in your surname as JAIN and your given name as PIYUSH KUMAR the same will
appear on your passport as: Surname: JAIN Given Name: PIYUSH KUMAR". Note: "In case you do not use a surname - leave
the "Surname" column blank and write your full name in "Applicant's Given Name" column." "No initials should be written
and all initials (if any) in the applicant's name should be expanded ... writing the Given Name as "P.K.JAIN" or
"PIYUSH K JAIN" is not correct." "No honorifics, titles such as Major, Doctor etc. should be written. Surname could have
two words like Roy Choudhary or Das Gupta." Also: "Use CAPITAL LETTERS only"; "Leave one box blank after each complete
word".

- **Parts:** given name (first), surname optional (a mononym is written whole in the given name).
- **Latin letters:** `\p{sc=Latin}` with the package default separators (hyphen, apostrophe, space). The booklet names
  no letter set or punctuation; initials written as letters (`P K`, `Piyush K`) are *not* refused (tested) (the rule
  binds a new application, and a still-valid name is never refused; a full stop is not a name character here, as in the
  package's Latin default).
- **Latin (ICAO) form:** derived `SURNAME<<GIVEN`; a person with no surname has the given name as the only identifier
  (`RAJESH<KUMAR`), the Doc 9303 rule for a single name (as `Name\MX`). Passport Seva prints such a passport with a
  blank surname (`P<IND<<RAJESH<KUMAR`); the package keeps the ICAO primary identifier (owner's ruling on code review
  CR-009) and cannot store the blank-surname form, as an `Icao\PersonName` needs a primary identifier. The booklet
  quotes no MEA rule that lets a passport keep another spelling, so the override policy is `TransliterationVariant`
  (another ICAO table spelling only).
- **Devanagari:** the booklet is a Latin-script form; no official text read restricts a Devanagari name (Aadhaar prints
  a name in the regional language, with no rule found for the script). `Name\IN\Devanagari\PersonName` takes the Latin
  form as mandatory (the passport spelling is a personal choice, the plan decision): parts of `\p{sc=Devanagari}` words;
  the virama (U+094D) and nukta (U+093C) are Script=Devanagari but may not start a word (each word starts with a letter,
  `\p{L}`); U+200C and U+200D (Script=Inherited, so listed explicitly) are allowed only right after the virama U+094D
  and before a letter (`क्‌ष`), where they select the half or explicit-virama form; anywhere else (after a consonant or
  a vowel sign, before the virama) they would store a second value for one visible name and are refused (code review
  CR-008). The Devanagari digits (U+0966-096F, Nd), the abbreviation sign U+0970 (Po) are not name characters; the danda
  U+0964/0965 is Script=Common and so outside the class. NFC decomposes the precomposed nukta letters (`क़` U+0958 →
  U+0915 U+093C) so both spellings store one value.

## Aadhaar number (`Document\IN\AadhaarNumber`)

UIDAI, "A UID Numbering Scheme" (IA copy of the uidai.gov.in file): "12 decimal digits will be sufficient"; "The
Checksum: There are several schemes possible. We recommend the Verhoeff scheme." (reference:
<http://www.cs.utsa.edu/~wagner/laws/verhoeff.html>). The same paper says "0- numbers (a1 = 0) could be used as an
"escape" for future extensions", "1- numbers (a1 = 1) could be reserved for entities rather than individuals" and "We
could use 2-9 numbers right away": these are proposals ("could"), not a rule on issued numbers, so **no first-digit
refusal** (a 12-digit number starting with 0 or 1 and a valid check digit is accepted). The Verhoeff check is enforced
because it is UIDAI's own publication, but the text only *recommends* it and no later UIDAI text restating it was found
(the Aadhaar Handbook 2021 says only "12-digit"); flagged to the owner.

- **Verhoeff:** the dihedral-group tables d, p of Verhoeff's 1969 algorithm (public domain; test vectors `2363`,
  `123451` from the algorithm's published examples).
- **Masked Aadhaar, UIDAI FAQ:** "Masked Aadhaar number implies replacing of first 8 digits of Aadhaar number with some
  characters like "xxxx-xxxx" while only last 4 digits of the Aadhaar Number are visible." `masked()` is
  `XXXX XXXX 1234` (the task brief's form; UIDAI says "characters like" and does not fix the separator).
- **Display form:** none quoted; no `formatted()`.
- Not enforced: that a number was issued; the 16-digit Virtual ID is a different identifier and is refused by length.

## PAN (`Document\IN\PermanentAccountNumber`)

ITD tutorial: "PAN is a ten-digit unique alphanumeric number issued by the Income Tax Department". "the first three
characters represent the alphabetic series running from AAA to ZZZ". "The fourth character of PAN represents the status
of the PAN holder. "P" stands for Individual, "C" ... Company, "H" ... Hindu Undivided Family (HUF), "A" ... Association
of Persons (AOP), "B" ... Body of Individuals (BOI), "G" ... Government Agency, "J" ... Artificial Juridical Person, "L"
... Local Authority, "F" ... Firm/ Limited Liability Partnership, "T" ... Trust". "Fifth character of PAN represents the
first character of the PAN holder's last name/surname in case of an individual. In case of non-individual PAN holders
fifth character represents the first character of the PAN holder's name." "Next four characters are sequential numbers
running from 0001 to 9999." "Last character, i.e., the tenth character is an alphabetic check digit."

- Secondary lists show other holder letters (E, K, ...): they are refused, since the ITD's ten-letter text is the
  source.
- 5 letters, 4 digits, 1 letter; the fourth in the ten letters above (`holderType()` gives a `PanHolderType`); the four
  digits are not `0000`. No formula for the check letter is published, so it is any letter. The fifth character is not
  checked against a name (none is given). The ITD page lists the ten letters as of the Finance Act 2025 amendment.
- Mask: no rule; last 4 characters. Display form: none (PAN is written without separators).

## Passport number (`Document\IN\PassportNumber`)

MEA tender corrigendum 4.4 (data page): "Passport Number - CHAR(8)"; the specimen value of the last-page bar code is
`J1234002` and an old number `B5007771`. The structure inside the 8 characters (one letter and seven digits in both
specimens, as every source repeats) is not stated as a rule, so only the length of 8 and the ASCII letters and digits
are enforced. The field is the same for every booklet, so the class covers any series; the numbers of diplomatic and
official passports are not shown to differ. No check character is published. Mask: last 3 (no rule; the package
keeps 3 of a number of 8 characters or fewer).
Passport Seva's file number (`DL1070012356714` in the specimen) is not the passport number and is not covered.

## ABHA number (`Document\IN\AbhaNumber`)

PIB explainer (Ministry of Health and Family Welfare, 2024-04-04): "ABHA number: Unique Health Identifier as a random
14-digit number"; "Ayushman Bharat Health Account (ABHA) is a unique 14 digit number". 14 digits, format only: no check
digit or prefix is published (ABDM sandbox docs read through search results show hyphenated groups, but no ABDM text
read fixes the display form, so there is no `formatted()`). Mask: last 4. The ABHA address (`name@abdm`) is a different
identifier and is not covered.

## Not covered

- **EPIC (voter ID):** the ECI declaration form (found by search, excerpt: "every EPIC has a unique number of
  alphanumeric strings with 3 alphabetical codes called Functional Unique Serial Number (FUSN), followed by 7 numerical
  digits, the last 7th digit being the checksum") and the Manual on Electoral Rolls (ceotripura.nic.in) could not be
  fetched here (406, no connection), so the text was not read. The same search results report that cards issued before
  the series were allotted use other formats and that the ECI itself acknowledged duplicate numbers; a still-valid
  number must not be refused, and no formula for the checksum is quoted. The Goa Department of Information and Publicity
  press note of 2025-05-14 (<https://www.goa.gov.in/wp-content/uploads/2025/05/20-YEAR-OLD-LEGACY-ISSUE.pdf>, read
  2026-11-17) confirms legacy series: "when various States/ UTs were using Assembly Constituency-wise different
  alphanumeric series in a decentralized manner. These series had to be changed again in 2008", and some ACs kept the
  old series. Not covered until the ECI text can be read.
- **Driving licence number:** searched parivahan.gov.in (FAQ pages, CMVR Form 2, 7, 10, the DL Club guide), the Central
  Motor Vehicles (Sixth Amendment) Rules 2021 in the Gazette and state transport sites for a structure of the number
  (state code, RTO code, year, serial); none states it. The widely repeated 2+2+4+7 layout appears only on aggregators.
  Not covered; `regionCode()`-style accessors are therefore not offered. Lead tried a second time (2026-11-17): the
  Parivahan DL-status page <https://parivahan.gov.in/rcdlstatus/?pur_cd=101> (reported input hint "SS-RRYYYYNNNNNNN OR
  SSRR YYYYNNNNNNN", old licences to be converted) answers 404 to `curl` (also without the query) and "socket hang up"
  to WebFetch; the sarathi.parivahan.gov.in state pages that load carry no such hint. Without a readable official text
  no `DriverLicenceNumber` is added.
- Passport Seva file number, ABHA address, Aadhaar Virtual ID, voter-roll serial numbers, ration card, state-issued
  documents.
