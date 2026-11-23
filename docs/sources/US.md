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
   field. b. If an applicant has only one name […] the name must be recorded in the last name field on the application
   and in TDIS/ACS. For domestic U.S. passport applications, insert a caret (^) in the first and middle names field in
   TDIS. […] NOTE: If an applicant submits documentation indicating she/he only has a first and middle name, and no
   last name, the first and middle names must still be placed in the last name field." c. treats "No First Name" (NFN)
   and "First Name Unknown" (FNU) the same way.
6. **8 FAM 403.1-3(B) Name Spacing**: "U.S. passports […] are printed in all uppercase letters."

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` (last name) | `\p{sc=Latin}` | hyphen, apostrophe, space | no federal text lists the letters of a name; the national form keeps diacritics as the evidence writes them (1); non-Latin scripts are refused (2); digits refused (4) |
| `GivenName` (first name, optional) | the same | the same | the same; null for a person with one name (5) |
| `MiddleName` | the same | the same | the same; the second secondary identifier |

- **ʻokina** (U+02BB, and U+2018 as 8 FAM itself types it in (3)) is stored as an apostrophe (U+0027), as the
  passport writes it (3); U+2019 already is.
  `Kalanianaʻole` → `Kalaniana'ole` → ICAO `KALANIANAOLE` (Doc 9303 Part 3 §4.6: the apostrophe is omitted).
- **Derived Latin:** `LAST<<FIRST<MIDDLE<SUFFIX` through ICAO table 6.A: `O'Brien, José Luis, 3RD` →
  `OBRIEN<<JOSE<LUIS<III`, `Peña-Ruiz, Zoë` → `PENA-RUIZ<<ZOE`.
- **Override policy `Name\TransliterationVariant`**: the State Department corrects only non-Latin names from other
  evidence (2); a Latin-script name loses its diacritics (1), and the bare base letter is a table 6.A spelling of every
  diacritic letter (`Ü` → `UE`/`UXX`/`U`, `Ñ` → `N`/`NXX`), so the passport spelling is always a table spelling: an
  override must be one (`MULLER<<ANNA` for `Müller Anna`; the derived form is `MUELLER<<ANNA`).
- **One-word names (5)**: `PersonName($family, null)`; the one name is the last name, so the ICAO form is the
  primary identifier alone (`Teller` → `TELLER`), as GB, CH and NL. A middle name without a first name is refused
  ("(middle name without a given name)"): the one-word rule puts even "a first and middle name, and no last name" into
  the last-name field, so no source defines a middle name beside a single name. A suffix without a first name is
  refused ("(suffix without a given name)"): 8 FAM 403.1-3(D) records only the one name, with a caret in the first and
  middle fields, and Doc 9303 §4.6 puts a suffix among the secondary identifiers, which a one-word name does not have;
  no source shows that combination, so it is not invented.

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
(8 FAM c.; §4.6 forbids digits anyway): `NameSuffix::SECOND->icao()` = `II`. An override must carry the same component
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

## DL/ID numbers (`Document\US\DlId\*`)

All sources were read on 2026-11-16 (UTC). "IA" is an unmodified Internet Archive capture (`id_` form), cited with its
UTC timestamp, read because the live page answered 403 or 404 or failed TLS.

**The standard leaves the format to the state.** AAMVA 2020 DL/ID Card Design Standard, Table 1, ref. f, field `4d`
"Customer identifier": "The alphanumeric string assigned or calculated by the issuing authority." (V25ANS). Table D.3,
ref. q, element `DAQ` "Customer ID Number": "The number assigned or calculated by the issuing authority." (V25ANS).
The standard gives no per-jurisdiction format. AAMVA's CDLIS State Procedures Manual (rel. 5.3.2.1, Aug 2013) does not
either.
The federal E-Verify web service accepts any 1–32 alphanumerics, saying the format "is subject to change at any time to
support a change to any state's document number format". So each jurisdiction's format comes from that jurisdiction's
own texts. For the 10 states in E-Verify's RIDE programme it also comes from USCIS's state fact sheets. This is the AU
pattern: a federal agency's help text that states the field rule.

### Sources

| Ref | Source | Publisher | Where read |
|---|---|---|---|
| AAMVA | 2020 DL/ID Card Design Standard, Ver 1.0, Table 1 ref. f, Table D.3 ref. q | AAMVA | <https://www.aamva.org/getmedia/99ac7057-0f4d-4461-b0a2-3a5532e1b35c/AAMVA-2020-DLID-Card-Design-Standard.pdf> |
| CDLIS | CDLIS State Procedures Manual, release 5.3.2.1 (Aug 2013): no DLN formats | AAMVA | IA 2024 copy of <https://malegislature.gov/Reports/8279/CDLIS%20SPM%20_Rel%205%203%202%201%20-%20Aug%202013_%20wo%20track%20changes.pdf> |
| EV-ICA | E-Verify ICA v32 API schema, `drivers_license_number`, `document_bc_number` | USCIS (DHS) | <https://everify.uscis.gov/ica-docs/> (script chunks) |
| RIDE | "<State> Driver's License and ID Card Information", RIDE fact sheets (PDF metadata of the FL sheet: 2013-09-06); identical copies under `factsheetsArchive/` | USCIS (DHS), E-Verify | `https://www.e-verify.gov/sites/default/files/everify/factsheets/<State>RIDEFactSheet.pdf` for Arizona, Florida, Idaho, Iowa, Maryland, Mississippi, Nebraska, NorthDakota, Wisconsin, Wyoming (other states: 404) |
| AZ-1 | Form 99-0121 "Redaction Program" (R03/25), "Things to Remember" item 8 | ADOT MVD | <https://apps.azdot.gov/files/mvd/mvd-forms-lib/99-0121.pdf>, IA 2025-07-15 15:38:36 |
| AL-1 | "Alabama Driver License Number Changes" (news) | ALEA | <https://www.mvtrip.alabama.gov/News/ViewArticle/444>, IA 2021-11-19 15:14:20 |
| CO-1 | Form DR 2330 (06/12/26) | Colorado DOR, DMV | <https://dmv.colorado.gov/sites/default/files/documents/DR_2330_e_wo.pdf> |
| CT-1 | "About the Department of Motor Vehicles" (glossary) | CT DMV | <https://portal.ct.gov/dmv/resources/dmv-about-us> |
| FL-1 | FACC eCitation Interface Control Document 6.1 (2025-09-02), field 28 | FL Assn. of Court Clerks, hosted by FLHSMV | <https://www.flhsmv.gov/pdf/courts/utc/eCitation_ICD_6.1_Rev%209.2.2025%20(1).pdf>, IA 2026-07-07 23:04:39 |
| FL-2 | Driver License Check | FLHSMV | <https://mydmvportal.flhsmv.gov/home/en/publicweb/dlcheck>, IA 2026-11-10 01:21:00 |
| FL-3 | News 2024-07-19 "FLHSMV Implements Legislation Requiring Driver Licenses and Identification Card numbers to Change"; F.S. 322.14(1)(a) | FLHSMV; Florida Senate | <https://www.flhsmv.gov/2024/07/19/flhsmv-implements-legislation-requiring-driver-licenses-and-identification-card-numbers-to-change/>, IA 2026-10-15 20:03:44; <https://m.flsenate.gov/statutes/322.14> |
| HI-1 | State of Hawaii Driver's License Application (Rev. 3/15/2023) | City & County of Honolulu (state form) | <https://www4.honolulu.gov/docushare/dsweb/Get/Document-325980/State%20of%20Hawaii%20Driver_s%20License%20Application.pdf> |
| ID-1 | "Driver Records and Suspensions" | Idaho Transportation Department | <https://itd.idaho.gov/guide/driver-records-and-suspensions/>, IA 2026-10-03 02:12:06 |
| IL-1 | LEADS Operating Manual, Drivers License chapter (05/11/2022) | Illinois State Police | <https://isp.illinois.gov/LawEnforcement/GetFile/de672e4e-5074-484d-a82f-cbfc20145c54> |
| IN-1 | "Electronic Insurance Forms Submission FAQs" | Indiana BMV | <https://www.in.gov/bmv/resources/insurance-agents/electronic-insurance-forms-submission-faqs> |
| KS-1 | "How to Read a Driving Record" (Rev 8/01) | Kansas DOR | <https://www.ksrevenue.gov/pdf/readingdmvreport.pdf> |
| KS-2 | Driver's License Status Check login | Kansas DOR | <https://www.kdor.ks.gov/Apps/DLStatus/login.aspx> |
| KY-1 | Graduated Driver Licensing eligibility | KY Transportation Cabinet | <https://apps.transportation.ky.gov/gdl>, IA 2025-12-30 01:56:33 |
| KY-2 | Three-year Driving History Records, more information | Commonwealth of Kentucky | <https://secure.kentucky.gov/dhronline/Records/MoreInformation> |
| MA-1 | Driver's Manual, revised 2/2018, "SA" and "S" License Numbers | Massachusetts RMV | <https://www.mass.gov/files/documents/2018/03/21/Drivers_Manual.pdf>, IA 2026-08-31 13:26:31 |
| MD-1 | IDVR web user guide | MDOT MVA | <https://egov.maryland.gov/mva/idvr/support/userguide> |
| ME-1 | Driver's Record Check Service | Maine BMV | <https://apps1.web.maine.gov/cgi-bin/bmv/drc/step1.pl> |
| MI-1 | "MiLogin and online accounts" FAQ | Michigan SOS | <https://www.michigan.gov/sos/faqs/resources/milogin-and-online-accounts>, IA 2026-06-06 13:21:39 |
| MI-2 | Friend of the Court Manual FOC 340X1, FCB 2001-001 (1-1-2001) | Michigan Family Independence Agency | <https://mdhhs-pres-prod.michigan.gov/olmweb/ex/foc/340X1.pdf>, IA 2025-04-25 03:48:22 |
| MS-1 | Mississippi Administrative Code, Title 31 Part 1 (Driver Services), Rule 4.4 and the driver-education rule | MS DPS (via MS SOS) | <https://www.sos.ms.gov/adminsearch/ACCode/00000383c.pdf> |
| NJ-1 | MyMVC AVS help | NJ MVC | <https://emvc.state.nj.us/mvc/MVC_SUAAV_WS/html/AVSHelp.html> |
| NM-1 | Online Driver History Records Service | NM MVD | <https://secure.mvd.newmexico.gov/RenewalServices/default.aspx> |
| NY-1 | "Information about Transaction Entries" | NY DMV | <https://dmv.ny.gov/transaction-message/information-about-transaction-entries>, IA 2026-05-14 22:14:21 |
| OK-1 | "Integrating External Court Records with the Oklahoma DPS Online Court Reporting System" (Rev. 2012-01-27), field 2 | Oklahoma DPS | <https://apps.ok.gov/dps/courts/external/documents/DPS.pdf> |
| OK-2 | SOP A.01 "Original Issuance – Class D and State ID" (effective 3/11/2025), quoting the agency rule on driver licence numbers | Service Oklahoma | <https://oklahoma.gov/content/dam/service-oklahoma/intranet/job-resources/sops/a--driver-license/A.01%20-%20Original%20Issuance%20-%20Class%20D%20and%20State%20ID.pdf> |
| PA-1 | "Where to Find Your Driver's License/Photo ID Number" | PennDOT | <https://www.pa.gov/agencies/dmv/faqs/driver-licensing-faqs/find-your-drivers-license-photo-id-number> |
| RI-1 | RI DMV Driver Records | State of Rhode Island | <https://www.ri.gov/DMV/mvr/citizen/> |
| RI-2 | Industry Alert 2017-2 "New 8 digit RI Drivers' Licenses" | RI Dept. of Business Regulation | <https://dbr.ri.gov/sites/g/files/xkgbur696/files/documents/divisions/insurance/consumers/IndustryAlert2017-2.pdf>, IA 2025-09-16 06:35:46 |
| TX-1 | "Where's my license or ID?" status search | Texas DPS | <https://www.dps.texas.gov/apps/driverlicense/dlsearch/dlstatus.aspx>, IA 2026-11-07 21:34:58 |
| VT-1 | "Law Enforcement Changes" (DMV modernization, 2025), "PID Format" | Vermont DMV | <https://dmv.vermont.gov/sites/dmv/files/documents/Law%20Enforcement%20Changes%202025.pdf> |
| VT-2 | Driver Record Search help | Vermont DMV | <https://secure.vermont.gov/DMV/mvr/help/>, IA 2021-05-08 22:24:56 |
| WA-1 | REAL ID information page | WA DOL | <http://www.dol.wa.gov/realid.html>, IA 2018-06-16 23:44:57 |
| WA-2 | "Get your learner permit" | WA DOL | <https://dol.wa.gov/driver-licenses-and-permits/get-your-first-license-or-permit/get-your-learner-permit> |
| WA-3 | ACCESS Operations Manual, chapter 8 "Department of Licensing" (revised November 2014) | Washington State Patrol | <https://www.wsp.wa.gov/wp-content/uploads/2023/10/08_licensing.pdf> |
| WI-1 | "Interpreting Public DMV Record Abstracts" (October 24, 2024) | WisDOT DMV | <https://portal.wi.gov/Documents/Interpreting-DMV-Abstracts10242024.pdf> |
| WI-2 | MV4000 Law Enforcement Officer's Instruction Manual (1998 ed.), field 29 | WisDOT | <https://wisconsindot.gov/Documents/dmv/shared/manual-mv4000.pdf> |

Rejected (secondary, never a rule): state-by-state DL format lists, regex and validator pages, DLP vendor definitions
(Microsoft Purview, Trellix, Skyhigh), barcode SDK documentation, library circulation guides. Sample cards and sample
barcodes were not used as rules. One example is the VA DMV AAMVA test PDF with `DAQ T16700285`; see "Not covered".

Rulings that apply to the whole table:

- Official texts of state agencies other than the DMV (state police, courts, clerks' interface documents) count.
- **Normalisation:** the separators of `Internal\Digits::SEPARATORS` are removed (hyphens are printed on FL, IL, WI and
  WY cards, and the sources say to enter the number without them). ASCII letters are upper-cased. Leading zeros are
  kept. `*` is not a separator, so it is never stripped; only the WA PIC shape accepts it (WA-3).
- "N numbers" and "N numeric" are taken as digits `[0-9]`. "Alphanumeric" means `[0-9A-Z]`, and all-digit and
  all-letter values are allowed, as in AU. A source that calls the characters "digits" when its own agency shows letters
  is read for the length only.
- A letter set or a fixed letter is enforced only where it is quoted (HI `H`, KS `K`, MA `S`/`SA`, MD `MD`, NE, VT, WA
  `WDL`). Check digits: VT and WA mention one but publish no algorithm, so none is verified. IL gives digit ranges but
  contradicts itself, so they are not enforced.
- **ID card:** "shares" means the source states that the same numbering, or the same format, covers the ID card. Then
  one `<Abbr>DlIdNumber` class covers both. "Separate" means the source states a different ID-card format; by the
  plan-3 Task 3 ruling (one class per jurisdiction) the jurisdiction's one `<Abbr>DlIdNumber` class accepts the union
  of the licence and ID-card shapes, and its doc says which shape is which (no `<Abbr>IdCardNumber` class). "Not
  stated" means the class covers the driving licence only.
- **Display form and mask:** `formatted()` exists only where a source prints the hyphenated form: FL (FL-2
  `Z123-456-78-901-0`), WI (WI-2 `H300-1987-0162-04`) and WY (RIDE "six numbers followed by a hyphen and three
  numbers"); `masked()` keeps that layout there. IL is left without one: IL-1 says two hyphens are printed but not
  where, and its spaced example is a LEADS listing. No source states a masking rule, so `masked()` keeps the last four
  characters, three for a number of eight characters or fewer (plan default).

### Per jurisdiction

| ISO | Source | Quoted format (verbatim) | Normalised shape | ID card | Encoded data | Official sample | Verdict |
|---|---|---|---|---|---|---|---|
| US-AK | DMV Form D1, online services (searched) | none: the forms show a "ALASKA LICENSE/ID #" field without a format | — | — | — | — | not covered |
| US-AL | AL-1 | "Effective December 1, 2018, Alabama will begin issuing an 8-digit driver license number. Alabama will continue to support the existing 7-digit numbers and also the new 8-digit numbers. Alabama currently issues 8-digit non-driver identification cards." | `[0-9]{7,8}` | separate: `[0-9]{8}` (accepted by `AlDlIdNumber`; also a DL shape) | none | none | covered |
| US-AR | DFA Driver Services, voter form, ASP data handbook (searched) | none for the DL (the State Police handbook gives a CDL field "Alpha", size 10, which is a field width, not a format) | — | — | — | — | not covered |
| US-AZ | RIDE (Arizona); AZ-1 | RIDE: "Arizona driver's licenses and ID cards contain document numbers (circled) that are nine characters- one letter followed by eight numbers." AZ-1: "If your Social Security Number appears on your driver license, please contact the LECU to lift your redaction then visit AZMVDNow.gov or an authorized MVD office to request a duplicate driver license with the SSN replaced by an MVD system generated number." | `[A-Z][0-9]{8}` or `[0-9]{9}` (an SSN printed as the number is still in circulation, AZ-1; the SSN's nine digits, as for KS and MS) | shares | none (an SSN-based number is not checked as an SSN) | none in text | covered |
| US-CA | dmv.ca.gov forms DL 410, INF 1125 (searched) | none: the forms ask for the "driver license or ID card number" without a format | — | — | — | — | not covered |
| US-CO | CO-1 | "Please write your 9-digit driver license or ID number on your check or money order." | `[0-9]{9}` | shares | none | none | covered |
| US-CT | CT-1 | "Operator number: Refers to the nine-digit number assigned by DMV to an individual upon successful completion of a driver's test." | `[0-9]{9}` | not stated | none | none | covered (DL) |
| US-DC | DC DMV brochure 2013, driver records page (searched) | none (a sample card printed `1234567`, which is a sample) | — | — | — | — | not covered |
| US-DE | DMV manual 2024, Title 21 ch. 26, Reg. 2217 (searched) | none: the CDL text says only "a unique number or identifier deemed appropriate by the Division" | — | — | — | — | not covered |
| US-FL | RIDE (Florida); FL-1; FL-2; FL-3 | RIDE: "Florida driver's licenses and ID cards contain document numbers (circled below) that are 13 alphanumeric characters (letters and numbers). The format is one letter followed by 12 numeric characters." FL-1: "A Florida driver's license, if non-blank, must be 13 characters long, may not contain any embedded spaces, and must begin with the first character of "Driver-Last Name"". FL-3: "While the license or identification card format will remain the same, a different formula utilizing randomization will now be used"; F.S. 322.14(1)(a): "a minimum of four randomly generated digits" | `[A-Z][0-9]{12}` | shares (RIDE) | first character = first letter of the last name (FL-1); not verified, because the holder's name is not an input | FL-2: "Example: Z123-456-78-901-0 (with or without the hyphens)" | covered |
| US-GA | dds.georgia.gov FAQs and contact pages (searched) | none found on an official page | — | — | — | — | not covered |
| US-HI | HI-1 | Field "DRIVER'S LICENSE NUMBER" printed as `H _ _ _ _ _ _ _ _` (a fixed `H` and eight boxes) | `H[0-9A-Z]{8}` (character set of the boxes not stated) | not stated | none | none | covered (DL) |
| US-IA | RIDE (Iowa) | "Iowa driver's licenses and ID cards contain document numbers (circled) that are nine alphanumeric characters (letters and numbers)." | `[0-9A-Z]{9}` | shares | none | none in text | covered |
| US-ID | RIDE (Idaho); ID-1 | RIDE: "Idaho driver's licenses and ID cards contain document numbers (circled) that are nine alphanumeric characters (letters and numbers). The current format is two letters followed by six numeric characters followed by one letter. Documents issued before January 1, 1993, contain nine numeric characters." ID-1 adds: "Previous Idaho license numbers began with 910, 920, or 940 assigned by the sheriffs' office, or a social security number." | `[A-Z]{2}[0-9]{6}[A-Z]` or `[0-9]{9}` | shares | none | none | covered |
| US-IL | IL-1 | "An Illinois DLN (Driver's License Number) is a 12-position alphanumeric figure, computed by a special formula and assigned by the Secretary of State. Although two hyphens appear within the number (DLN) on the license itself, these hyphens are never used in a LEADS inquiry. Each DLN encodes a driver's name (including last name, first name or initial and middle initial), sex and birth date." "Y620 - First letter of the driver's last name followed by 3 numeric digits derived from the remainder of the last name" §2.1: "A PID is computed from name, sex, and date of birth exactly like a DLN, except that a PID carries the single alpha character in the last position." | `[A-Z][0-9]{11}` | separate: `[0-9]{11}[A-Z]` (accepted by `IlDlIdNumber`) | name, sex and birth date (formula not quoted; not decoded). Digit rule as quoted: "The numbers 7,8, and 9 never appear in the first three numeric positions. Also, the last three digits must be 001 through 372 if the driver is male, and 601 through 972 if female. The numbers 373 through 600 are invalid in the final three positions. For DLN's that compute out to be duplicated for more than one person the last three digits are replaced with an assigned number between 400 and 599." This is self-contradictory (400–599 falls inside "invalid" 373–600), so it is **not enforced** and `Sex` is not derived | `Y620 930 57 270` (IL-1) | covered |
| US-IN | IN-1 | "you must provide your insured 10 digit Indiana DLN or their CUID number" | `[0-9]{10}` | not stated | none | none | covered (DL) |
| US-KS | KS-1; KS-2 | KS-1: "DL Number-Drivers License or ID Card Number (either Social Security number, Computer Generated number that starts with "K" and 8 numbers, or a 6 digit alpha-numeric number)." KS-2: "( example: K12345678 ) … With no dashes or spaces" | `[0-9]{9}`, `K[0-9]{8}` or `[0-9A-Z]{6}` | shares | none (an SSN-based number is not checked as an SSN) | `K12345678` (KS-2) | covered |
| US-KY | KY-1; KY-2 | KY-1: "The Driver's License/Permit Number listed will have a letter followed by 8 numbers. Enter the full license/permit number without the dashes onto the box provided." KY-2: "The license number is made up of 9 characters, 1 letter followed by 8 numbers." | `[A-Z][0-9]{8}` | not stated | none | `C21545427` (KY-1) | covered (DL) |
| US-LA | expresslane.la.gov OMV pages (searched) | none: the forms ask for the number "as it appears on the physical card" | — | — | — | — | not covered |
| US-MA | MA-1 | "Beginning March 26, 2018, the RMV license system will assign computer generated numbers beginning with an "SA" or an "S" followed by numbers equaling a total of nine characters on your license/ID card." | `S[0-9]{8}` or `SA[0-9]{7}` | shares | none | none | covered |
| US-MD | RIDE (Maryland); MD-1 | RIDE: "Maryland driver's licenses and ID cards contain document numbers (circled) that are thirteen characters- one letter followed by twelve numbers." MD-1: "The MD ID must be entered as "MD" followed by 11 digits, or the driver's license number must start with a letter followed by 12 numbers." | `[A-Z][0-9]{12}` (the MD-1 "MD ID" form, "MD" + 11 digits, is a Customer ID search key; no MVA text read ties it to a licence or ID card, so it is not covered) | shares (RIDE) | none: MVA calls the letter-first number a "soundex" (MD PTP guide, `SoundexNumber`) but publishes no algorithm | `A999999999999` (MD-1 field table) | covered |
| US-ME | ME-1 | "Driver's License Number (7 digits):" | `[0-9]{7}` | not stated | none | none | covered (DL) |
| US-MI | MI-1; MI-2 | MI-1: "When prompted to submit an ID number, enter the twelve-digit number from your driver's license or ID." MI-2: "Driver license number. The number begins with the first letter of the individual's last name." | `[A-Z][0-9]{12}` (MI-1's "twelve-digit number" is read as the twelve digits after MI-2's leading letter) | shares (MI-1) | first letter of the last name (MI-2); not verified | none | covered |
| US-MN | revisor.mn.gov §171.07, DPS DVS pages (searched) | none: the statute says only "a distinguishing number assigned to the licensee" | — | — | — | — | not covered |
| US-MO | dor.mo.gov forms; 12 CSR 10-24 (1999, superseded) (searched) | none current; Form 5443 shows a specimen `T123456789` (sample only) | — | — | — | — | not covered |
| US-MS | RIDE (Mississippi); MS-1 | RIDE: "Mississippi driver's licenses and ID cards contain document numbers (circled below) that are nine numeric characters." MS-1: "The Social Security Number is used as the driver license number." Rule 4.4: "The wife's Social Security Number would be the same as her husband's except it would be followed by a letter of the alphabet." "Assigned Driver License and ID Card numbers will be taken from the same pool of numbers." | `[0-9]{9}` or `[0-9]{9}[A-Z]` (the rule 4.4 form, still in the code). The older assigned numbers (rule 4.4(2) "001", 4.4(4)(e) "(001, 000, 900)", the 4.4(4)(b) "800....1" series) have no stated length; they are covered only as far as they are RIDE's "nine numeric characters", and any other length is not covered | shares | none (an SSN-based number is not checked as an SSN) | none | covered |
| US-MT | MVD record guide 2023, D300 brochure, forms (searched) | none | — | — | — | — | not covered |
| US-NC | NCDMV forms, DL-DPPA-1, MyDMV (searched) | none | — | — | — | — | not covered |
| US-ND | RIDE (North Dakota) | "North Dakota driver's licenses and ID cards contain document numbers (circled) that are nine characters- three letters followed by six numbers." | `[A-Z]{3}[0-9]{6}` | shares | none | none | covered |
| US-NE | RIDE (Nebraska) | "Nebraska driver's licenses and ID cards contain document numbers (circled) that are one letter (A,B,C,E,G,H, or V) followed by eight numbers." | `[ABCEGHV][0-9]{8}` (letter set enforced) | shares | none | none | covered |
| US-NH | Saf-C 1000, RSA 263:40, DSMV 450 (searched) | none: "a distinguishing number assigned to the licensee" | — | — | — | — | not covered |
| US-NJ | NJ-1 | "Your driver license number consists of a letter followed by 14 digits. Enter your driver license number, without any spaces" | `[A-Z][0-9]{14}` | not stated | none | none | covered (DL) |
| US-NM | NM-1 | "Your driver's license number : This 9-digit number can be found at the top of your license." | `[0-9]{9}` | not stated | none | none | covered (DL) |
| US-NV | DMV handbook, 2021/2014 comparison sheet (searched) | none (a handbook card image shows `12345,67890`, a sample) | — | — | — | — | not covered |
| US-NY | NY-1 | "DMV ID Number. This is the 9-digit "ID" number printed in the upper portion of your driver license, learner permit, or non-driver ID (DMV photo document). Enter the 9 digits only; do not enter spaces, hyphens, or other punctuation." | `[0-9]{9}` | shares | none | none | covered |
| US-OH | codes.ohio.gov 4507.13, BMV pages (searched) | none: "The distinguishing number assigned to the licensee" | — | — | — | — | not covered |
| US-OK | OK-1; OK-2 | OK-1: "In Oklahoma this number is made up of an alphabetic character followed by nine numeric digits." OK-2: "Driver license numbers shall be assigned by computer. Use of the applicant's SSN as the driver license number is prohibited" … "Any licensee may request to change his or her driver license number to any nine-digit number which is not in use or has not been previously used" | `[A-Z][0-9]{9}` or `[0-9]{9}` | not stated | none | none | covered (DL) |
| US-OR | ODOT DMV chapter A, form 6665 (searched) | none: "A customer number for an individual person is usually their Oregon Driver License (ODL) number" | — | — | — | — | not covered |
| US-PA | PA-1 | "Your eight (8) digit Driver's License Number (DLN) or Photo Identification Card Number (IDN) is located on your Driver's License, Photo Identification Card, Learner's Permit and on most correspondences from PennDOT." | `[0-9]{8}` | shares | none | none | covered |
| US-RI | RI-1; RI-2 | RI-1: "Your driver's license number: This 7-digit number can be found at the top of your license." RI-2: "The upgraded system is designed to issue new RI drivers' licenses with 8 digits. This is a change from the current 7 digit drivers' licenses in use today, but existing 7 digits drivers' licenses will continue to be valid." | `[0-9]{7,8}` | not stated | none | none | covered (DL) |
| US-SC | S.C. Code Title 56 ch. 1, SCDMV barcode guide (searched) | none (the barcode guide shows `DL#: 102245737`, a sample) | — | — | — | — | not covered |
| US-SD | SDCL 32-12, DPS application forms (searched) | none | — | — | — | — | not covered |
| US-TN | tn.gov MVR and inquiry pages (searched) | none | — | — | — | — | not covered |
| US-TX | TX-1 | "Drivers License or ID Card # (8 digits number)" | `[0-9]{8}` | shares | none | none | covered |
| US-UT | DLD MVR Online, UCJIS Utah DL Manual (IA 2026-01-06) (searched) | none (only "If your license number begins with zero, do not enter the zeros" and "Enter the number into field without the UT") | — | — | — | — | not covered |
| US-VA | DMV glossary "Customer Number", PIN logon (searched) | none: "A DMV system-generated unique number associated with the customer" (the AAMVA test PDF `T16700285` is a sample) | — | — | — | — | not covered |
| US-VT | VT-1; VT-2 | VT-1: "Driver's License and Non-Driver ID PID length will continue to be 8 characters long. The first 7 characters will always be a digit, and the final character can be a digit or letter. Examples: 10512345, 3470128A, 2054512V. The following letters are valid for use in the final character: A, F, J, K, L, Q, R, T, V, X, Y. New PIDs generated will not use a check digit calculation." VT-2 (older): "The Vermont PID is either an 8 digit number or a seven digit number followed by the character 'A'." | `[0-9]{7}[0-9AFJKLQRTVXY]` (letter set enforced) | shares | none (an older check digit is implied but not published) | `10512345`, `3470128A`, `2054512V` | covered |
| US-WA | WA-1; WA-2; WA-3 | WA-1: "Starting September 4, 2018, all driver license and ID cards issued will receive new driver license numbers. All will have WDL as the first 3 letters, and will be followed by 9 randomly chosen alphabetical characters and numbers. Those who already have a driver license or ID card will get the new number when they renew or replace it after September 4." WA-2: "A WDL number is a unique 12-digit number that identifies you, and appears on learner permits, ID cards, and licenses." WA-3 (legacy PIC): "This is a 12-character code computed from name and date of birth. The first seven characters must be alphabetic or "*"; the eighth and ninth characters must be numeric, the tenth character must be numeric or "*", and the eleventh and twelfth characters may be alphabetic or numeric." | `WDL[0-9A-Z]{9}` or `[A-Z*]{7}[0-9]{2}[0-9*][0-9A-Z]{2}` | shares | legacy PIC only: five letters of the last name, initials, coded birth date, "self-checking digit" (algorithm not published; not verified, not decoded) | `GROSECM378NA`, `WEAVESE301C0` (WA-3) | covered |
| US-WI | RIDE (Wisconsin); WI-1; WI-2 | RIDE: "Wisconsin's licenses and ID cards contain document numbers (circled) that are fourteen characters- one letter followed by thirteen numbers." WI-1: "Type driver's license number as it appears on the driver's license (no dashes)" | `[A-Z][0-9]{13}` | shares | none | `H300-1987-0162-04` (WI-2) | covered |
| US-WV | WV DMV self-service, DS-23P form (searched) | none | — | — | — | — | not covered |
| US-WY | RIDE (Wyoming) | "Wyoming driver's licenses and ID cards contain document numbers (circled) that are nine numbers- six numbers followed by a hyphen and three numbers." | `[0-9]{9}` (hyphen removed) | shares | none | none | covered |

**Totals:** 30 of 51 covered (19 with the ID card shared, 9 for the driving licence only, and AL and IL whose one class
also accepts the separately stated ID-card shape). 21 are not covered.

### Overlap — the factory's test oracle

`DlIdNumberFactory` matches the 30 covered `<Abbr>DlIdNumber` classes only. Each class accepts its jurisdiction's
licence and ID-card shapes (AL and IL included), so the IL ID card has its own row. An input is normalised first
(above). The groups below are in enum order:

| Normalised input class | Jurisdictions it fits |
|---|---|
| 6 alphanumerics (digits or letters) | US-KS |
| 7 digits | US-AL, US-ME, US-RI |
| 8 digits | US-AL, US-PA, US-RI, US-TX, US-VT |
| 9 digits | US-AZ, US-CO, US-CT, US-IA, US-ID, US-KS, US-MS, US-NM, US-NY, US-OK, US-WY |
| 10 digits | US-IN |
| 7 digits + one of `AFJKLQRTVXY` | US-VT |
| 9 digits + a letter | US-MS |
| letter + 8 digits | US-AZ, US-IA, US-KY, plus US-HI if the letter is `H`, US-KS if `K`, US-MA if `S`, US-NE if one of `ABCEGHV` |
| `SA` + 7 digits | US-IA, US-MA |
| other 9 alphanumerics with ≥ 1 letter | US-IA; plus US-HI if the first is `H`; US-ID for two letters + 6 digits + letter; US-ND for three letters + 6 digits |
| letter + 9 digits | US-OK |
| letter + 11 digits | US-IL |
| 11 digits + a letter (IL ID card) | US-IL |
| letter + 12 digits | US-FL, US-MD, US-MI |
| letter + 13 digits | US-WI |
| letter + 14 digits | US-NJ |
| `WDL` + 9 alphanumerics; or the WA PIC layout | US-WA |
| anything else (e.g. 1–5, 11–16 digits; 7 digits + `B`) | none |

**Since 1.0 the factory has no `fromNumber()`** (owner decision O2: the country is not fully covered, so a single
match does not identify the issuer); the `fromNumber()` results below are the pre-1.0 behaviour and now describe a
single-element `jurisdictionsMatching()`.

Worked examples (verified against the shapes above). Inputs are shown after normalisation. "1" means `fromNumber()`
returns that class; "≥ 2" throws `AmbiguousJurisdictionException` with exactly these codes in this order; "0" throws
`InvalidDocumentNumberException`.

| Input | Matches | Jurisdictions (enum order) |
|---|---|---|
| `12345` | 0 | none |
| `123456` | 1 | US-KS |
| `AB1234` | 1 | US-KS |
| `1234567` | 3 | US-AL, US-ME, US-RI |
| `12345678` | 5 | US-AL, US-PA, US-RI, US-TX, US-VT |
| `123456789` | 11 | US-AZ, US-CO, US-CT, US-IA, US-ID, US-KS, US-MS, US-NM, US-NY, US-OK, US-WY |
| `1234567890` | 1 | US-IN |
| `12345678901` | 0 | none |
| `1234567890123` | 0 | none |
| `1234567A` | 1 | US-VT |
| `2054512V` | 1 | US-VT |
| `1234567B` | 0 | none |
| `123456789A` | 1 | US-MS |
| `A12345678` | 4 | US-AZ, US-IA, US-KY, US-NE |
| `D12345678` | 3 | US-AZ, US-IA, US-KY |
| `H12345678` | 5 | US-AZ, US-HI, US-IA, US-KY, US-NE |
| `K12345678` | 4 | US-AZ, US-IA, US-KS, US-KY |
| `S12345678` | 4 | US-AZ, US-IA, US-KY, US-MA |
| `C21545427` | 4 | US-AZ, US-IA, US-KY, US-NE |
| `SA1234567` | 2 | US-IA, US-MA |
| `HABCDEFGH` | 2 | US-HI, US-IA |
| `A1B2C3D4E` | 1 | US-IA |
| `AB123456C` | 2 | US-IA, US-ID |
| `ABC123456` | 2 | US-IA, US-ND |
| `A123456789` | 1 | US-OK |
| `Y62093057270` (`Y620-9305-7270`) | 1 | US-IL |
| `Z123456789010` (`Z123-456-78-901-0`) | 3 | US-FL, US-MD, US-MI |
| `MD12345678900` | 0 | none |
| `H3001987016204` (`H300-1987-0162-04`) | 1 | US-WI |
| `A12345678901234` | 1 | US-NJ |
| `WDL123456789` | 1 | US-WA |
| `GROSECM378NA` | 1 | US-WA |
| `SMITH**12*AB` | 1 | US-WA |

So unlike AU, several inputs do fit exactly one jurisdiction: 10 digits (IN), letter + 9 digits (OK), letter + 11
digits or 11 digits + a letter (IL), letter + 13 or 14 digits (WI, NJ), WA's layouts, VT's lettered form, MS's lettered
form, and six characters (KS). These exercise the single-match branch with real formats. Review Focus 1 holds:
`fromNumber('123456789')` throws with the eleven codes above.

### DL/ID numbers not covered

- **21 jurisdictions with no official format:** US-AK, US-AR, US-CA, US-DC, US-DE, US-GA, US-LA, US-MN, US-MO, US-MT,
  US-NC, US-NH, US-NV, US-OH, US-OR, US-SC, US-SD, US-TN, US-UT, US-VA, US-WV. The table above gives what was read
  for each.
  - Their DMV pages, forms and statutes name the number but state no format. Where a page shows one, it is only a
    sample card, image or barcode.
  - None is in E-Verify RIDE with a published fact sheet.
  - Secondary lists that give formats for all of them were rejected.
  - They go on the README "not covered" list with this reason.
- **ID cards where the source does not state the numbering:** US-CT, US-HI, US-IN, US-KY, US-ME, US-NJ, US-NM, US-OK and
  US-RI. Their classes cover the driving licence only.
- **Maryland "MD ID"** (`MD` + 11 digits): stated only as an IDVR Customer ID search key (MD-1); no MVA text read says
  it is printed on a licence or ID card.
- **Mississippi assigned numbers of any length other than nine digits** (rule 4.4: "001", "(001, 000, 900)",
  "800....1"; no length stated).
- **Pre-1993 and earlier Idaho numbers** beyond the nine-digit form, i.e. the sheriff-assigned 910/920/940 numbers of
  unstated length. Only the nine-digit numeric form is covered.
- **Decoding of encoded data:** IL, WA PIC, FL and MI letters, MD "soundex". No algorithm is quoted, so no `HasSex` or
  `HasBirthDate` comes from a DL/ID number, and no check digit is verified.
- **US territories:** out of scope (plan header).

## Not covered

- **A middle name or a suffix beside a one-word name**: refused (no source defines it; see "Names").
- **The IRM's "never issued" SSN examples** are not refused (open list ending in "etc.").
- **ATIN** (`9nn-93-nnnn`) and **IRSN** (IRM 3.13.5.23): not in the plan's candidates; neither passes as an ITIN
  (93 is excluded; the IRSN campus digits listed there, 01-10, 17-19, 28-29, 37-39, 49, 66 and 89, are all outside
  the ITIN ranges) nor as an SSN (area 9xx).
- **Name suffixes off the AAMVA list** (`Esq`, `X`, `Jr.` with a period) are refused.
