# United States — sources

All dates UTC. Sources read 2026-11-16.

| Source | Publisher | Where read |
|---|---|---|
| Social Security Number Randomization | SSA | <https://www.ssa.gov/employer/randomization.html>, Internet Archive copy of 2026-10-31 10:55:48 UTC: <http://web.archive.org/web/20261031105548id_/https://www.ssa.gov/employer/randomization.html> |
| Social Security Number Randomization, Frequently Asked Questions | SSA | <https://www.ssa.gov/employer/randomizationfaqs.html>, Internet Archive copy of 2026-10-21 19:22:35 UTC: <http://web.archive.org/web/20261021192235id_/https://www.ssa.gov/employer/randomizationfaqs.html> |
| Internal Revenue Manual 3.13.5 "Individual Master File (IMF) Account Numbers" (effective 01-01-2027): 3.13.5.21 (01-01-2022) "Social Security Number (SSN) Format", 3.13.5.22 (01-01-2016) "Individual Taxpayer Identification Number (ITIN) Format" | IRS | <https://www.irs.gov/irm/part3/irm_03-013-005> |
| 26 CFR 301.6109-4 "IRS truncated taxpayer identification numbers" | eCFR (Office of the Federal Register) | <https://www.ecfr.gov/current/title-26/section-301.6109-4> |
| "Information about the Next Generation U.S. Passport" | U.S. Department of State | <https://travel.state.gov/content/travel/en/passports/passport-help/next-generation-passport.html>, Internet Archive copy of 2026-05-22 12:50:26 UTC |
| "Design and Security of our Documents" | U.S. Department of State | <https://travel.state.gov/en/passports/apply/help/security-design.html>, Internet Archive copy of 2026-06-27 00:05:03 UTC |
| E-Verify ICA documentation, "U.S. Passport and U.S. Passport Card" (field definitions) | USCIS (DHS) | <https://everify.uscis.gov/ica-docs/cases/appendix/field-definitions/uspassport-and-uspassport-card> (page text read from its published script chunk `assets/js/a5a23ade.b7bbc160.js`) |
| "Understanding the Medicare Beneficiary Identifier (MBI) Format" | CMS | <https://www.cms.gov/medicare/new-medicare-card/understanding-the-mbi.pdf> |
| Foreign Affairs Manual 8 FAM 403.1 "Name Usage and Name Changes" (CT:CITZ-141, 03-10-2026) | U.S. Department of State, CA/PPT/S/A | <https://fam.state.gov/fam/08fam/08fam040301.html>, Internet Archive copy of 2026-10-09 11:54:24 UTC: <http://web.archive.org/web/20261009115424id_/https://fam.state.gov/fam/08fam/08fam040301.html> |
| 2020 AAMVA DL/ID Card Design Standard, Ver 1.0 (Table 2 ref. g; Table D.4 ref. i, `DCU`) | AAMVA | <https://www.aamva.org/getmedia/99ac7057-0f4d-4461-b0a2-3a5532e1b35c/AAMVA-2020-DLID-Card-Design-Standard.pdf> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §3.4, §4.6, §6.A | ICAO | <https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf> (fetched from `https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf`) |

Reachability: `www.ssa.gov` answers 403 ("Access Denied", Akamai) to curl; `travel.state.gov` serves a Cloudflare block
page to curl and WebFetch (also in the most recent archive captures, so earlier captures with status 200 are cited);
`fam.state.gov` fails TLS verification (curl error 60, WebFetch "unable to verify the first certificate"). Their
unmodified Internet Archive captures (`id_` form) were read; earlier ruling: such a copy counts as the official source.
`www.irs.gov`, `www.ecfr.gov`, `www.cms.gov`, `www.aamva.org`, `www.icao.int` and `everify.uscis.gov` answer to curl
(the E-Verify page is a script-rendered site; its text is in the page's JavaScript chunk). Secondary only (never a
rule): barcode SDK documentation listing the AAMVA `DCU` values, Wikipedia.

## Names (`Name\US\*`)

1. **8 FAM 403.1-3(C)(3) Diacritical Marks** (verbatim): "a. Diacritical marks (such as accent marks, umlauts, carets,
   etc.) are not supported in TDIS/ACS. b. Use a single line to cross out all diacritical marks, such as accent marks,
   even if they appear on the applicant's evidence of citizenship/nationality or ID."
2. **8 FAM 403.1-3(C)(4) Non-Latin Alphabets**: "a. ICAO standards require that names be written in a Latin alphabet.
   b. If the applicant's name includes characters from non-Latin alphabets, e.g., Cyrillic letters, you must correct
   the name using Latin characters based on the applicant's citizenship evidence, ID, Permanent Resident Card (PRC), or
   U.S. visa in accordance with 8 FAM 403.1-2".
3. **8 FAM 403.1-3(C)(1) Punctuation**, a.(3): "An applicant whose name contains an ‘okina to indicate a pause
   (typically seen in Hawaiian and American Samoa names, e.g., “Ku’ulei”), may receive an apostrophe in lieu of the
   ‘okina, as the ‘okina character is not supported." b.(1): "Hyphens may be included as part of a name, between parts
   of a multiple part name, and between names. The hyphen must not include additional spaces, for example, it must be
   printed Bertram-Wilberforce and not Bertram - Wilberforce".
4. **8 FAM 403.1-3(E) Numerals**: "a. If the applicant's name is a numeral, you must cross it out and write out the
   numeral on the application and in TDIS/ACS: b. If the applicant's name is an ordinal numeral (a numeral with a
   suffix), you must cross it out and write out the ordinal numeral".
5. **8 FAM 403.1-3(D) One-Word Names**: "a. ICAO standards require that one-word names be printed in the last name
   field. b. If an applicant has only one name […] the name must be recorded in the last name field".
6. **8 FAM 403.1-3(B) Name Spacing**: "U.S. passports […] are printed in all uppercase letters."

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` (last name) | `\p{sc=Latin}` | hyphen, apostrophe, space | no federal text lists the letters of a name; the national form keeps diacritics as the evidence writes them (1); non-Latin scripts are refused (2); digits refused (4) |
| `GivenName` (first name) | the same | the same | the same |
| `MiddleName` | the same | the same | the same; the second secondary identifier |

- **ʻokina** (U+02BB) is stored as an apostrophe (U+0027), as the passport writes it (3); U+2019 already is.
  `Kalanianaʻole` → `Kalaniana'ole` → ICAO `KALANIANAOLE` (Doc 9303 Part 3 §4.6: the apostrophe is omitted).
- **Derived Latin:** `LAST<<FIRST<MIDDLE<SUFFIX` through ICAO table 6.A: `O'Brien, José Luis, 3RD` →
  `OBRIEN<<JOSE<LUIS<III`, `Peña-Ruiz, Zoë` → `PENA-RUIZ<<ZOE`.
- **Override policy `Name\TransliterationVariant`**: the State Department corrects only non-Latin names from other
  evidence (2); a Latin-script name loses its diacritics (1), and the bare base letter is a table 6.A spelling of every
  diacritic letter (`Ü` → `UE`/`UXX`/`U`, `Ñ` → `N`/`NXX`), so the passport spelling is always a table spelling: an
  override must be one (`MULLER<<ANNA` for `Müller Anna`; the derived form is `MUELLER<<ANNA`).
- **One-word names (5) are not representable**: the plan fixes `PersonName(FamilyName, GivenName, …)` with a required
  given name (as AU). Not covered; see "Not covered".

## Name suffix (`Name\US\NameSuffix`)

AAMVA DL/ID Card Design Standard 2020, Table 2 "Optional data elements", ref. g "Name suffix / Suffixe" (V5ANS), and
Table D.4 "2D Optional data elements", ref. i, element `DCU` "Name Suffix" (verbatim):

> Name Suffix (If jurisdiction participates in systems requiring name suffix (PDPS, CDLIS, etc.), the suffix must be
> collected and displayed on the DL/ID and in the MRT). Collect full name for record, print as many characters as
> possible on portrait side of DL/ID.
>
> • JR (Junior) • SR (Senior) • 1ST or I (First) • 2ND or II (Second) • 3RD or III (Third) • 4TH or IV (Fourth)
> • 5TH or V (Fifth) • 6TH or VI (Sixth) • 7TH or VII (Seventh) • 8TH or VIII (Eighth) • 9TH or IX (Ninth)

Table 1 footnote 4: "If a suffix is used, it will follow the given name(s) and be separated by a comma and a space."

**8 FAM 403.1-5(B) Name Suffixes** (verbatim): "a. Name suffixes (Jr., Sr., II, III, etc.) may be added or dropped
based on the applicant's preference on form DS-11 […] (1) If the name suffix appears on the evidence of
citizenship/nationality or ID, you may assume that the applicant wants it included as part of her/his name unless the
applicant requests otherwise. […] (2) If the applicant writes the suffix on the application […] it should be included
as a name. b. […] (2) An applicant may use Sr. and Jr. interchangeably with I and II; […] c. Arabic ordinal numbers
must be changed to Roman numerals, for example, 2nd to II, 3rd to III".

**ICAO Doc 9303 Part 3 §4.6** (MRZ, verbatim): "Prefixes and suffixes, including titles, professional and academic
qualifications, honours, awards, and hereditary status (such as Dr., Sir, Jr., Sr., II and III) shall not be included
in the MRZ except where the issuing State considers these to be legally part of the name. In such cases, prefixes or
suffixes shall be represented as components of the secondary identifier(s)." "Numeric characters shall not be used in
the name fields of the MRZ." §3.4 (VIZ): "Any prefixes, suffixes or Roman numerals shall be entered in the secondary
identifier field."

Implemented: `NameSuffix` is a string-backed enum of the 20 AAMVA codes, values as AAMVA writes them (`JR`, `1ST`,
`I`, …); `fromCode()` accepts any letter case and surrounding spaces, refuses `Jr.` and anything off the list ("Invalid
American name suffix."). `1ST` and `I` are distinct values (the cardholder's choice; 8 FAM b.(2) lets them change).
**ICAO form:** the State Department includes the suffix "as part of her/his name" (8 FAM 403.1-5(B) a.), which is the
case §4.6 names, so the suffix is the last component of the secondary identifier; ordinals become Roman numerals
(8 FAM c.; §4.6 forbids digits anyway): `NameSuffix::Second->icao()` = `II`. An override must carry the same component
(`PENA<<ANNA<LEE<II`); a passport issued without the suffix (dropped by preference, a.) is modelled as a name without
it.

## Social Security number (`Document\US\SocialSecurityNumber`)

SSA, Randomization page (verbatim): "the SSN has always been comprised of the three-digit area number, followed by the
two-digit group number, and ending with the four-digit serial number." "Previously unassigned area numbers were
introduced for assignment excluding area numbers 000, 666 and 900-999."

SSA, Randomization FAQ: "Will SSN randomization assign group number (the fourth and fifth digits of the SSN) 00 or
serial number (the last four digits of the SSN) 0000? SSN randomization will not assign group number 00 or serial
number 0000. SSNs containing group number 00 or serial number 0000 will continue to be invalid." "The SSN is still
comprised of nine numeric digits".

IRM 3.13.5.21 (the IRS restating SSA): "SSA issues SSNs in the following format: nnn–(area), nn–(group),
nnnn–(serial). The Social Security Administration (SSA) does not assign SSNs that begin with the following three
digits (signifies area): 000 […] 666 […] 900-999 […] The SSA does not assign SSNs with zeroes in the fourth and fifth
positions […] The SSA does not assign SSNs with zeroes in the last four positions". It adds: "The SSNs in the following
examples are invalid and were never issued through the Social Security Administration: 000-XX-XXXX 111-11-1111
222-22-2222 777-77-7777 123-45-6789 666-66-6666, etc."

26 CFR 301.6109-4(a): "An IRS truncated taxpayer identification number (TTIN) is an individual's social security
number (SSN), IRS individual taxpayer identification number (ITIN), […] in which the first five digits of the nine-digit
number are replaced with Xs or asterisks. The TTIN takes the same format of the identifying number it replaces, for
example XXX-XX-1234 when replacing an SSN".

Implemented: 9 digits; refused exactly as the SSA states: area `000`, `666`, `9xx` ("(area number)"), group `00`
("(group number)"), serial `0000` ("(serial number)"). **No check digit** exists. The IRM's list of never-issued
examples is not applied: it ends in "etc." (an open list, not a rule), and `111-11-1111` or `123-45-6789` pass the
SSA's rules. `formatted()` is `123-45-6789`; `masked()` is the TTIN layout of 26 CFR 301.6109-4 with asterisks:
`***-**-6789` (an official "last four" rule). Encoded data: none (the area lost its geographic meaning on 25 June 2011).

## ITIN (`Document\US\Itin`)

IRM 3.13.5.22 (verbatim): "The ITIN display in a format similar to a SSN but will always begin with the number "9."
The fourth and fifth numbers within an ITIN are assigned in the following unique numbering ranges: 9nn-50-nnnn through
9nn-65-nnnn 9nn-70-nnnn through 9nn-88-nnnn 9nn-90-nnnn through 9nn-92-nnnn 9nn-94-nnnn through 9nn-99-nnnn Note: "89"
and "93" are not used as the fourth and fifth numbers for ITIN's and are reserved for other programs." (The same IRM,
3.13.5.83: the ATIN's "fourth and fifth digits are always "93"".)

Implemented: 9 digits, first digit `9` ("(first digit)"), digits 4-5 in 50-65, 70-88, 90-92, 94-99 ("(fourth and
fifth digits)"). Serial digits: no rule is quoted, so none is applied. No check digit. An ITIN is never an SSN (area
9xx) and an SSN never an ITIN (first digit). `formatted()` `912-70-1234`; `masked()` is the TTIN form (26 CFR
301.6109-4 covers ITINs): `***-**-1234`. No official example number is published (tests use synthetic numbers).

## Passport book number (`Document\US\PassportBookNumber`)

State Department, Next Generation Passport FAQ (verbatim): "What is an alphanumeric passport number? The passport
number in the new passport book begins with a letter, followed by eight numbers." "Can I still use my old passport
issued before 2021? Yes, if the passport is still valid, you may use it."

State Department, Design and Security: "Another important feature is a perforated alphanumeric passport number on every
page, which is a letter followed by 8 numbers."

USCIS E-Verify field definitions: "U.S. passport number: A U.S. passport number, notated above, usually contains nine
numeric characters. There are a few versions of the U.S. passport still in circulation that vary from the version
shown. In some cases, a U.S. passport number contains eight characters—one letter followed by seven numeric
characters."

Implemented: after separators are removed and letters upper-cased, one of `[0-9]{9}`, `[A-Z][0-9]{7}` or
`[A-Z][0-9]{8}`. **Format only**: no check digit and no letter set is published, so any letter is accepted (a card
number `C` + eight digits also fits). No display form is published. `masked()` keeps the last four characters, three
for an eight-character number (package default): `*****5678`, `*****567`.

## Passport card number (`Document\US\PassportCardNumber`)

USCIS E-Verify field definitions (verbatim): "U.S. passport card number: The U.S. passport card number, notated above,
begins with the letter ‘C’, followed by eight numeric characters." No State Department page reachable here states the
card format (the card page captures do not mention it).

Implemented: `C` + 8 digits; format only. `masked()` keeps the last four characters (package default): `*****5678`.

## Medicare Beneficiary Identifier (`Document\US\MedicareBeneficiaryIdentifier`)

CMS, MBI format (verbatim): "The MBI has 11 characters". "Each MBI is randomly generated. […] The MBI's characters are
"non-intelligent" so they don't have any hidden or special meaning." "MBIs are numbers and upper-case letters. We'll use
numbers 0-9 and all letters from A to Z, except for S, L, O, I, B, and Z. […] If you use lowercase letters, our system
will convert them to uppercase letters." "Here's an example: 1EG4-TE5-MK73". "The MBI's 2nd, 5th, 8th, and 9th
characters will always be a letter. Characters 1, 4, 7, 10, and 11 will always be a number. The 3rd and 6th characters
will be a letter or a number. The dashes aren't used as part of the MBI. They won't be entered into computer systems or
used in file formats." Position table: "Position 1 – numeric values 1 thru 9 […] Position 4 – numeric values 0 thru 9".

Implemented: 11 characters in that position layout (position 1: `1-9`; letters `ACDEFGHJKMNPQRTUVWXY`), lower case
upper-cased, dashes and spaces stripped. **No check digit** exists. `formatted()` is the CMS display form
`1EG4-TE5-MK73`; `masked()` keeps the last four characters in it (no official masking rule): `****-***-MK73`. The CMS
example is valid and used in the tests.

## Encoded sex and birth date

None of these documents encodes a sex or a birth date: `Sex` is unchanged and no class implements `HasSex` or
`HasBirthDate`.

## Driver's licence and state ID numbers (Task 2)

Not part of this note yet: the per-jurisdiction DL/ID numbers (AAMVA and the 50 states and DC) are researched in plan
3, Task 2.

## Not covered

- **One-word names** (8 FAM 403.1-3(D)): `PersonName` requires a given name (plan decision); a passport holder with a
  single name cannot be represented yet.
- **The IRM's "never issued" SSN examples** are not refused (open list ending in "etc.").
- **ATIN** (`9nn-93-nnnn`) and **IRSN** (IRM 3.13.5.23): not in the plan's candidates; neither passes as an ITIN
  (93 is excluded; the IRSN campus digits listed there, 01-10, 17-19, 28-29, 37-39, 49, 66 and 89, are all outside
  the ITIN ranges) nor as an SSN (area 9xx).
- **Name suffixes off the AAMVA list** (`Esq`, `X`, `Jr.` with a period) are refused.
