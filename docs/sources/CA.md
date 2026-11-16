# Canada — sources

All dates UTC. Sources read 2026-11-16.

| Source | Publisher | Where read |
|---|---|---|
| "SIN for temporary residents in Canada" (page details 2026-06-02) | ESDC (Service Canada) | <https://www.canada.ca/en/employment-social-development/services/sin/temporary-residents.html> |
| "Employer information - Social Insurance Number (SIN)" (2023-06-22) | ESDC | <https://www.canada.ca/en/employment-social-development/programs/ei/ei-list/ei-employers-sin.html> |
| "The Social Insurance Number (SIN) Code of Practice", main page and Section 2 (2026-03-24) | ESDC | <https://www.canada.ca/en/employment-social-development/services/sin/reports/code-of-practice.html>, <https://www.canada.ca/en/employment-social-development/services/sin/reports/code-of-practice/section-2.html> |
| "Employers - What you need to know about Social Insurance Numbers" (2026-10-09) | ESDC | <https://www.canada.ca/en/employment-social-development/services/sin/reports/employers.html> |
| "Get the social insurance number (SIN) from the individual" (2024-04-10) | CRA | <https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/set-up-new-employee/social-insurance-number.html> |
| "Rejected T4FHSA individual records or annual information returns" (2026-09-16) | CRA | <https://www.canada.ca/en/revenue-agency/services/tax/registered-plans-administrators/first-home-savings-account/rejected-t4fhsa-individual-records-annual-information-returns.html> |
| Learn about your taxes, "Starting to work" | CRA | <https://www.canada.ca/en/revenue-agency/services/tax/individuals/educational-programs/starting-work.html> |
| Help Centre, "Where is my status in Canada document number?" (date modified 2026-11-03) | IRCC | <https://www.ircc.canada.ca/english/helpcentre/answer.asp?qnum=1446>; earlier version: Internet Archive copy of 2023-02-17 07:38:52 UTC, <http://web.archive.org/web/20230217073852id_/https://www.cic.gc.ca/english/helpcentre/answer.asp?qnum=1446> |
| "Features of Canada's new passport" (2026-03-19) | IRCC (Passport Program) | <https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/new-passport-features.html> |
| "Changing the name on your passport or other travel document" (2026-06-01) | IRCC (Passport Program) | <https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/change-name.html>, text read from the Internet Archive copy of 2026-10-29 22:00:33 UTC, <http://web.archive.org/web/20261029220033id_/https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/change-name.html> |
| PPTC 153 "Adult General Passport Application" (08-2026), instructions section I | IRCC (Passport Program) | <https://www.canada.ca/content/dam/ircc/migration/ircc/english/passport/forms/pdf/pptc153.pdf>, Internet Archive copy of 2026-10-15 20:12:02 UTC |
| Help Centre, "What do you mean by surname?" (date modified 2026-11-03) | IRCC | <https://ircc.canada.ca/english/helpcentre/answer.asp?qnum=014> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | see `ICAO.md` |

Reachability: `www.canada.ca` resets HTTP/2 streams and stalls HTTP/1.1 requests from curl (no answer within 90 s);
WebFetch reads it. Its pages were read through WebFetch (quotes as returned) or, for the verbatim passages marked
below, through unmodified Internet Archive captures (`id_` form, gzip-encoded). `ircc.canada.ca` and
`www.ircc.canada.ca` answer WebFetch. Secondary only (never a rule, never cited in code): SIN generator and validator
sites, Wikipedia, `servicecanadapassport.ca` (not a Government of Canada domain).

## Names (`Name\CA\*`)

1. **Characters** ("Changing the name on your passport", heading "You may not be able to use some characters or accent
   marks", verbatim from the archive copy): "We can only print the Roman alphabet and some accented letters in passports
   and travel documents. Most languages have alternative spellings of names to account for this." It continues: "All
   systems that handle passenger data, including personal identity information, follow the ICAO guidelines."
2. **Given names may differ from the evidence** (same page, verbatim): "If you have multiple given names on your proof
   of Canadian citizenship, you may ask for your given name(s) to be slightly different on your passport. You must be
   applying for a new adult passport to do so. You can [list:] drop given names [;] invert given names. If you're
   dropping or inverting your given name(s), the name you want on your passport must be the same as the name that's
   on your supporting ID."
3. **Requested name** (PPTC 153, section I, verbatim): "Write your name as you request that it appear in the passport.
   If you are requesting a passport in a name that is different from the name that appears on the proof of citizenship
   or differs from a legal name change, please note the following: […]" (relationship certificate for a surname
   change, legal name change certificate, court or adoption order).
4. **No family name** (IRCC Help Centre qnum=014, as returned by WebFetch): "If you don't have a family name on your
   passport, travel or identity document, enter all your given name(s) in the surname field and leave the given name
   field blank." and "Do not enter "Not applicable," "*" or "NA."" This is IRCC's rule for its application forms; no
   Passport Program page found states how a passport prints a one-word name (the `XXX` forename described on
   `servicecanadapassport.ca` is not from an official source and is not applied).

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` (surname) | `\p{sc=Latin}` | hyphen, apostrophe, space | (1) names no closed list ("some accented letters"), so the Latin script is the rule; English and French names (`Gagné`, `Bérubé-Lévesque`, `D'Amours`, `de la Fontaine`) fit; other scripts (Cyrillic, Canadian syllabics) are refused |
| `GivenName` (optional) | the same | the same | the same; null for a person without a family name (4) |

- **One-word names (4)**: `PersonName($family, null)`, the one name as the surname, ICAO form `KIAWENTI` (the primary
  identifier alone), as GB, CH, NL and US.
- **Derived Latin:** `SURNAME<<GIVEN` through ICAO table 6.A: `Bérubé-Lévesque, Jean-François` →
  `BERUBE-LEVESQUE<<JEAN-FRANCOIS`, `D'Amours, Hélène` → `DAMOURS<<HELENE` (Doc 9303 §4.6 omits the apostrophe).
- **Override policy `Name\AnyIcaoSpelling`** (the plan's exception to `TransliterationVariant`): (1) sends a name
  with letters the passport cannot print to an "alternative spelling", and (2) lets an adult's passport drop or invert
  given names against the proof of citizenship, so the passport's Latin name need not be a table spelling of the
  national name (`TREMBLAY<<EVE<MARIE` for `Tremblay, Marie Ève Louise`). Any valid ICAO name is kept as typed; one
  equal to the derived form (a `<` for a hyphen included) is not kept; a letter without an ICAO spelling is accepted
  only with an override (as GB and RU).

## Social Insurance Number (`Document\CA\SocialInsuranceNumber`)

ESDC, all as returned by WebFetch:

- Code of Practice: "The SIN is a unique 9-digit number." Section 2: temporary residents "are neither Canadian
  citizens nor permanent residents and receive SINs that begin with the number "9."" and "If in doubt about whether an
  employee's SIN is valid, contact Service Canada's Social Insurance Number program."
- "SIN for temporary residents in Canada": "SINs issued to temporary residents always start with "9"".
- "Employer information - SIN": "SINs beginning with a "9" are issued to temporary workers who are neither Canadian
  citizens nor permanent residents." "These temporary SINs are valid until the expiry date indicated on the immigration
  document authorizing them to work in Canada."

CRA:

- T4FHSA rejection codes 3 and 44: a SIN "is not valid as it does not pass the Modulus 10 (Mod 10) check digit
  formula". Code 48: "An ITN is a nine (9) digit number beginning with "08" or "09"." (the CRA individual tax number,
  a different number).
- "Get the SIN from the individual": temporary SINs "begin with a "9" and have an expiry date."
- "Starting to work": "the first digit of their SIN is typically associated with their province or territory of
  residence" (no table), and sample SINs `123 456 789` (pay stub, T4 box 12) and `111 111 111` (a Nova Scotia
  example).

Verdict:

- **Check digit: not verified (format only).** ESDC publishes none. The CRA names a "Modulus 10 (Mod 10) check digit
  formula" but publishes no algorithm (no weights, no worked example) on any page found; "Mod 10" alone names a family
  of algorithms, and the only algorithm quoted by third parties (Luhn) is not an official text. The CRA's own sample
  SINs `123 456 789` and `111 111 111` fail the Luhn sum, so enforcing it would refuse official examples. A mistyped
  SIN is therefore accepted.
- **First digit:** no leading digit is refused: no ESDC text says 0 or 8 is unassigned (the CRA ITN's `08`/`09` is a
  different number; this class cannot tell an ITN from a SIN). `isTemporaryResident()` is the ESDC statement "SINs
  beginning with a "9" are issued to temporary workers": first digit 9. The province meaning of other first digits is
  "typically" only, with no published table, so it is not exposed.
- **Display:** `formatted()` = `123 456 789` (the CRA's printed form). **Mask:** no official rule; the plan default
  keeps the last four digits: `*** **6 789`.
- No sex or birth date is encoded.

## Passport number (`Document\CA\PassportNumber`)

- IRCC Help Centre qnum=1446 (current): "Since May 10, 2023, new passports have 9 characters and includes letters and
  numbers in this order: begins with a letter, the next 6 characters are numbers, and ends in 2 letters."
- The same page, Internet Archive copy of 2023-02-17 07:38:52 UTC (verbatim): "It has 8 characters and includes
  letters and numbers in this order: begins with 2 letters, and ends in 6 numbers."
- "Features of Canada's new passport": "The first character is always a letter, the next 6 characters are always
  numbers, and the last 2 characters are always letters."

Scope: only the general (regular) passport format IRCC publishes is covered; the special, diplomatic, temporary and
emergency travel document formats are not published and not covered.

Implemented: `[A-Z][0-9]{6}[A-Z]{2}` (since 2023-05-10) or `[A-Z]{2}[0-9]{6}` (before; such passports are still
valid). Format only: no check digit or letter set published (the MRZ check digit is a separate field). No official
example number. Mask: no official rule; last four characters, three for the 8-character form (`*****56BC`,
`*****456`).

## Encoded sex and birth date

Neither covered number encodes a sex or a birth date, so `Sex` is unchanged.

## Provincial documents

Driver's licence numbers (`Document\CA\DriverLicence\*`) and health card numbers (`Document\CA\HealthCard\*`) of the
13 provinces and territories, in ISO 3166-2:CA order. All sources were read on 2026-11-16 (UTC). "IA" is an unmodified
Internet Archive capture (`id_` form), cited with its UTC timestamp, read because the live host blocked automated
reads (SAAQ answers with a Radware 303, `open.alberta.ca` and MPI with 403).

### Sources

| Ref | Source | Publisher | Where read |
|---|---|---|---|
| ON-PTC | "Province and Territory Codes" (September 2022), the format table for reciprocal claims | Ontario Ministry of Health | <https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf> |
| ON-B | OHIP INFOBulletin 220902 "Northwest Territories Health Service Number" (2022-09-13) | Ontario Ministry of Health | <https://www.ontario.ca/document/ohip-infobulletins-2022/bulletin-220902-northwest-territories-health-service-number> |
| ON-IHCS | "Interface to Health Care Systems Technical Specifications" v6.2 (2023-04-01), §4.14 "MOD 10 Check Digit" | Ontario Ministry of Health | <https://files.ontario.ca/moh-tech-spec-health-care-systems-manual-en-2023-06-12.pdf> |
| ON-HCV | "Technical Specification for Health Card Validation (HCV) Service via EBS" v4.6 (October 2025) | Ontario Ministry of Health | <https://www.ontario.ca/files/2025-10/moh-ohip-technical-specification-health-card-validation-service-via-electronic-business-services-en-2025-10-08.pdf> |
| ON-HCM | "Health Card Validation Reference Manual" (2024-10-10), "Version Codes" | Ontario Ministry of Health | <https://www.ontario.ca/files/2024-10/moh-health-card-validation-reference-manual-en-2024-10-10.pdf> |
| ON-BG | MTO Carrier and Driver Records "Business Glossary" (last updated 2021-08-03) | Ontario Ministry of Transportation | <https://www.cdr.mto.gov.on.ca/assets/files/glossary/Business%20Glossary.pdf> |
| AB-PRG | "Physician's Resource Guide 2024", Appendix D "Valid Provincial/Territorial Health Cards" | Alberta Health | IA 2026-08-30 12:05:29 of <https://open.alberta.ca/dataset/d79fa4a5-f134-464f-8f49-6787c6408a75/resource/70f56882-e5df-4a80-9035-1d90108c8978/download/hlth-physicians-resource-guide-2024.pdf> |
| AB-RDN | Registrar's Decisions: Notification (RDN) 01/2024 "Driver's Licence History Report (DLHR)"; RDN 05/2025 "Operator Information Search Report (OISR)" | Service Alberta (AMVIR) | <https://www.alberta.ca/system/files/sa-registrar-decision-notification-01-2024.pdf>, <https://www.alberta.ca/system/files/sa-registrar-decision-notification-05-2025.pdf> |
| BC-TP | "Teleplan4 Web Record Specifications" v4.8, chapter 1, §1.14.1–1.14.2 | BC Ministry of Health (MSP) | <https://www2.gov.bc.ca/assets/gov/health/practitioner-pro/medical-services-plan/teleplan-ch1.pdf> |
| BC-DLN | "Driver's Licence Number Project (DLNP) — Technical information for external stakeholders" v1.1 (2022-11-04); "Driver's licence number (DLN) changes" FAQ (last updated 2023-04-26) | ICBC | <https://partners.icbc.com/assets/3r0rg6eeZ7PkeCIz3K9Usq/dln-technical-guidelines.pdf>, <https://partners.icbc.com/assets/6cjn4yP78X3AOu0g5vLn1y/dln-faqs.pdf> |
| MB-HC | "Your Manitoba Health Card (Registration Certificate)" | Manitoba Health, Seniors and Long-Term Care | <https://www.gov.mb.ca/health/mhsip/health-card-information.html> |
| MB-DVA | The Drivers and Vehicles Act, C.C.S.M. c. D104, s. 126.1(1), as enacted by S.M. 2022, c. 39 | Manitoba | <https://web2.gov.mb.ca/laws/statutes/2022/c03922e.php> |
| NL-PIM | MCP Physician Information Manual, "Residents of Other Provinces" (January 2007) | NL Department of Health & Community Services | <https://www.gov.nl.ca/hcs/files/mcp-providers-pim-residents-of-other-provinces.pdf> |
| NS-ID | Permits Directory, "ID Cards (Photo)", "Additional Information" | Service Nova Scotia (RMV) | <https://novascotia.ca/sns/paal/rmv/paal275.asp> |
| QC-SAAQ | "Aide-mémoire — Première connexion à SAAQclic Commerçants" (C-6902-2 (23-01)); "Presentation of Québec driver's licences" / "Présentation du permis de conduire" (2023-07) | SAAQ | IA 2024-05-07 01:02:36 of <https://saaq.gouv.qc.ca/blob/saaq/documents/promotion-services/saaqclic-commercants/aide-memoire-saaqclic-connexion-administrateur-commerce-agree.pdf>; IA 2026-07-18 09:34:17 of <https://saaq.gouv.qc.ca/blob/saaq/documents/publications/presentation-quebec-driver-licences.pdf>; IA 2024-05-11 18:24:19 of <https://saaq.gouv.qc.ca/blob/saaq/documents/publications/presentation-permis-conduire-quebec.pdf> |
| QC-GIU | "Cadre normatif pour la gestion de l'identification de l'usager (GIU)" v5.5 (September 2024), element 30100030 | Québec MSSS | <https://publications.msss.gouv.qc.ca/msss/fichiers/2024/24-715-08W.pdf> |
| QC-X | "Feuillet — carte d'assurance maladie avec le marqueur de genre X" (24-302-01W, 2024) | Québec MSSS | <https://cdn-contenu.quebec.ca/cdn-contenu/sante/documents/Systeme_et_services_de_sante/regimes-assurance/feuillet_marqueur_X.pdf> |
| SK-IV | "Identity Validation and Privacy" (Saskatchewan Account help) | Government of Saskatchewan | <https://www.saskatchewan.ca/government/doing-business-with-government/saskatchewan-account-help/identity-validation-and-privacy> |
| SK-EH | MySaskHealthRecord "Support for your account" | eHealth Saskatchewan | <https://www.ehealthsask.ca/residents/mysaskhealthrecord/account-support> |
| SK-BG | "Saskatchewan's One-part Driver's Licence" backgrounder (2010-11-18) | SGI, on saskatchewan.ca | <https://www.saskatchewan.ca/-/media/news-archive/2010/november/18/sgi-unveils-new-onepart-drivers-licence/onepart-drivers-licence-backgrounder.pdf> |

Searched without a format (see "not covered"): MPI pages and forms (Licence Lookup, DD/RÉF help, driving records);
Service New Brunswick and GNB licence, e-Licensing and records pages; NL Motor Registration Division forms and driver's
handbook; PEI Highway Traffic Act, handbook and forms; GNWT driver's licence application and driver's manual; Nunavut
driver's manual; Yukon ID pages. Rejected (secondary, never a rule): insurer quoting guides (`sonnet.ca`), DLP and
"eduction grammar" vendor definitions (Microsoft Purview, OpenText IDOL), pharmacy-software help (McKesson), billing
vendors, Wikipedia. Sample cards and sample numbers are not rules.

**A multi-province official source exists for health cards.** ON-PTC is the Ontario ministry's own table of every
jurisdiction's health number format, kept for reciprocal billing (ON-B shows it is maintained: NWT letter `H` was added
on 2022-09-13). It was spot-checked against each province's own text where one was found: AB (AB-PRG), BC (BC-TP), MB
(MB-HC), ON (ON-HCV), QC (QC-GIU) and SK (SK-EH) agree with it; AB-PRG and NL-PIM, two more provincial ministries,
agree for NB, NS, NU, PE and YT (and add nothing that contradicts it). No such source exists for driver's licences: no
federal or interprovincial text with per-province licence formats was found.

Rulings that apply to both tables:

- **Normalisation:** the separators of `Internal\Digits::SEPARATORS` are removed and ASCII letters are upper-cased, as
  in US and AU. Leading zeros are kept. Consequence for NS (below): the spaces that pad a short surname are separators
  too, so `FOY  220867001` normalises to `FOY220867001`.
- "N numerics", "N digits" and a `#`/`0`/`9` placeholder in a published mask are digits `[0-9]`; `X`/`A` in a mask
  and "alpha" are letters `[A-Z]`; "alphanumeric" is `[0-9A-Z]` (all-digit and all-letter values allowed, as US/AU).
- A check digit is verified only where the algorithm is published with a worked example: ON health (ON-IHCS) and BC
  health (BC-TP). QC's "chiffre valideur" has no published algorithm, so it is not verified.
- **Older formats:** ON-PTC lists prior formats (AB 11 numerics before 1994-06-01, MB 6 numerics before 2005-04-01,
  NS 11 numerics family-based before 1994-01-01). No source says such numbers are still on a valid card, so they are
  not accepted.
- **Masks:** no province publishes a masking rule for either number, so `masked()` takes the plan default (last four
  kept, three when the number is 8 characters or shorter). A layout is kept only where `formatted()` exists.

### Driver's licence numbers — per jurisdiction

"ID card": "shares" = the source says the same number is on the province's photo ID card, so one
`<Abbr>DriverLicenceNumber` covers both; "not stated" = licence only.

| ISO | Source | Quoted format (verbatim) | Normalised shape | ID card | Encoded data | Official sample | Verdict |
|---|---|---|---|---|---|---|---|
| CA-AB | AB-RDN | RDN 01/2024, "Driver's Licence Information Data": "Driver's Licence No. ######-### provides the unique DL#". RDN 05/2025: "Operator No: ######-### provides the unique DL (operator) number" | `[0-9]{9}`; `formatted()` `######-###` | not stated | none | none | covered (DL) |
| CA-BC | BC-DLN | Technical guide §5.1: "Maximum 7-digit integer" / "7 numeric character string, padded with leading zeros" (current) and "8 numeric character string, padded with leading zeros" (future); §7: "driver's licences in circulation may show either 7- or 8-digit numbers: Existing cards will show 7-digits; Newly issued, renewed or replaced cards will show 8-digits". FAQ Q5: "Existing customers will now see a leading zero (0) when cards get replaced or renewed. Driver licences that are issued to new customers will start with a leading three (3)." FAQ: "the ICBC client number (CLN) and BC Identification card (BCID) numbers will not change." | `[0-9]{7,8}` (the leading `3` of new numbers is not enforced: existing 8-digit numbers start with `0`) | not stated (BCID is a separate number, format not stated) | none | `31098794` (a new number in the guide's §5.1 table) | covered (DL) |
| CA-MB | MB-DVA; MPI pages (searched) | MB-DVA: ""driver's licence number" means the unique alpha-numeric identifier associated with a particular driver's licence." No length or layout found | — | — | — | — | not covered |
| CA-NB | SNB/GNB pages and forms (searched) | none: the pages ask for the "New Brunswick Driver's Licence number" without a format | — | — | — | — | not covered |
| CA-NL | MRD forms, driver's handbook, Highway Traffic Act (searched) | none: "Driver's Licence Number" fields only | — | — | — | — | not covered |
| CA-NS | NS-ID | "When you see the term "Master Number" or "Client Master Number" on an application form, it refers to the number on your NS Identification Card or Driver's Licence under the heading "Master". This 14-digit number is made up of the following: (a) the first 5 letters of your last name [if your last name has fewer than 5 letters, the difference is made up with spaces]; (b) plus the day / month / year of your birth; and (c) plus 3 computer-assigned numbers. For example: FINCH--220867001 [5 letters (Finch) + 22 (for 22nd) + 08 (for August, 8th month of the year) + 67 (for 1967) + 001 (3 computer-assigned numbers) ]" | `[A-Z]{1,5}` + `DD` `MM` `YY` + `[0-9]{3}`, i.e. `[A-Z]{1,5}(0[1-9]\|[12][0-9]\|3[01])(0[1-9]\|1[0-2])[0-9]{2}[0-9]{3}` (10–14 characters after normalisation, see the ruling on padding) | shares | surname prefix (first 5 letters); birth day, month and two-digit year (no century stated) | `FINCH--220867001` (in the quoted text) | covered |
| CA-NT | GNWT application form, driver's manual (searched) | none | — | — | — | — | not covered |
| CA-NU | Nunavut driver's manual (searched) | none | — | — | — | — | not covered |
| CA-ON | ON-BG | "Driver's Licence Number: A unique 15 character identifier assigned to each driver in Ontario." Format column: "Free form 15-character alphanumeric", sample `X0915-26435-95718` (also `X09152643595718` elsewhere in the glossary) | `[0-9A-Z]{15}` | not stated (the Photo Card holder has a 9-digit "Driver Identification Number (DIN)", a different number) | not defined in text (format only) | `X0915-26435-95718` (glossary sample) | covered (DL) |
| CA-PE | Highway Traffic Act, handbook, forms (searched) | none: "a numbered driver's license in the form prescribed by the Registrar" | — | — | — | — | not covered |
| CA-QC | QC-SAAQ | Aide-mémoire: "Numéro d'identification SAAQ (sur votre permis ou votre certificat d'immatriculation) - Ce numéro contient 13 caractères et commence par une lettre (format : A0000-000000-00). Sur le permis de conduire, ce numéro se trouve en dessous du titre « Permis de conduire »." Card guide: field "Driver's licence number (4d)" | `[A-Z][0-9]{12}`; `formatted()` `A0000-000000-00` | not stated | not defined in text (format only) | `L1531-171274-08` (card guide, field 4d) | covered (DL) |
| CA-SK | SK-IV; SK-EH; SK-BG | SK-IV: "you'll need to provide information from your SGI-issued driver's licence or non-driver photo ID … Make sure your customer and validation numbers contain only numbers and no letters. Customer number is eight digits. Validation number is 10 digits." SK-BG: "On the front of the card the date of birth, date of expiry, customer number and wave are raised"; the sample card labels the printed number "LICENCE NUMBER (PIC)" | `[0-9]{8}` (the customer number) | shares (SK-IV: licence or non-driver photo ID) | none | `35890562` (SK-BG sample card) | covered |
| CA-YT | Yukon licence and ID pages (searched) | none | — | — | — | — | not covered |

**Totals:** 6 of 13 covered (NS and SK with the photo ID card, AB, BC, ON and QC for the licence only). 7 are not
covered: CA-MB, CA-NB, CA-NL, CA-NT, CA-NU, CA-PE, CA-YT.

Decisions in this table:

- **SK:** SGI calls the eight-digit number the "customer number"; the 2010 card labels the same printed number "LICENCE
  NUMBER (PIC)". It is the number the province asks for to identify a licence holder, so it is the covered number. The
  10-digit validation number changes with every card and is not a licence number (not covered). SK-IV says temporary
  cards "may not display the leading 0s" and tells the reader to add them, so only the padded eight digits are accepted.
- **BC:** a 7-digit number and its 8-digit form with a leading `0` are the same licence (ICBC adds the zero in its
  systems). Both are accepted as typed; `value()` is not padded (no source says the 7-digit card form is wrong).
- **NS:** the hyphens in `FINCH--220867001` and the padding spaces are separators; the normalised value keeps the
  letters and nine digits. The letter set is `[A-Z]` ("letters of your last name"; no accented letters appear on the
  page). The day and month are checked as calendar fields because the text defines them as the birth date; the year has
  two digits and no century, so `birthDate()` cannot be built: 5b may expose day, month and two-digit year only. The
  "Master Number" of a group (joint ownership, "the last 9 digits are computer-assigned") is not on a licence or ID card
  and is not covered.
- **ON:** the glossary is the only text; it says "alphanumeric", so a letter is not required in the first position
  (the sample's leading letter and its 5-5-5 grouping are samples). The licence number encodes nothing that MTO defines
  in text. `formatted()` may follow the glossary sample's `X0000-00000-00000` as US followed its FL/WI samples; that is
  5b's call, flagged in the report.
- **QC:** SAAQ defines no encoding of the licence number in text, so it is format only.
- **AB, QC:** `formatted()` follows the published masks (`######-###`, `A0000-000000-00`).

### Driver's licence numbers — overlap (the factory's test oracle)

`DriverLicenceNumberFactory` matches the 6 covered classes. Inputs are normalised first. Groups in enum order:

| Normalised input class | Jurisdictions it fits |
|---|---|
| 7 digits | CA-BC |
| 8 digits | CA-BC, CA-SK |
| 9 digits | CA-AB |
| 1–5 letters + valid `DDMM` + 5 digits (10–14 characters) | CA-NS |
| letter + 12 digits | CA-QC |
| 15 alphanumerics | CA-ON |
| anything else (1–6 or 10–14 digits, the NS layout with a bad day or month, 2+ letters + 12 digits) | none |

Worked examples (verified by the script below). "1" means `fromNumber()` returns that class; "≥ 2" throws
`AmbiguousJurisdictionException` with exactly these codes in this order; "0" throws `InvalidDocumentNumberException`.

| Input | Normalised | Matches | Jurisdictions (enum order) |
|---|---|---|---|
| `123456` | `123456` | 0 | none |
| `1234567` | `1234567` | 1 | CA-BC |
| `0567450` | `0567450` | 1 | CA-BC |
| `12345678` | `12345678` | 2 | CA-BC, CA-SK |
| `31098794` | `31098794` | 2 | CA-BC, CA-SK |
| `35890562` | `35890562` | 2 | CA-BC, CA-SK |
| `123456789` | `123456789` | 1 | CA-AB |
| `123456-789` | `123456789` | 1 | CA-AB |
| `1234567890` | `1234567890` | 0 | none |
| `FINCH220867001` | `FINCH220867001` | 1 | CA-NS |
| `FINCH--220867001` | `FINCH220867001` | 1 | CA-NS |
| `FOY  220867001` | `FOY220867001` | 1 | CA-NS |
| `finch 220867 001` | `FINCH220867001` | 1 | CA-NS |
| `FINCH320867001` | `FINCH320867001` | 0 | none (day 32) |
| `FINCH221367001` | `FINCH221367001` | 0 | none (month 13) |
| `FINCHE220867001` | `FINCHE220867001` | 1 | CA-ON |
| `L1531-171274-08` | `L153117127408` | 1 | CA-QC |
| `l153117127408` | `L153117127408` | 1 | CA-QC |
| `LL53117127408` | `LL53117127408` | 0 | none |
| `X0915-26435-95718` | `X09152643595718` | 1 | CA-ON |
| `ABCDEFGHIJKLMNO` | `ABCDEFGHIJKLMNO` | 1 | CA-ON |
| `X0915264359571` | `X0915264359571` | 0 | none |
| `X091526435957189` | `X091526435957189` | 0 | none |

Only the 8-digit class is ambiguous (BC and SK), so the factory exercises the single-match branch for every other
covered shape, and `fromNumber('12345678')` throws with CA-BC, CA-SK.

### Health card numbers — per jurisdiction

| ISO | Source | Quoted format (verbatim) | Normalised shape | Version code | Check digit / encoded data | Official sample | Verdict |
|---|---|---|---|---|---|---|---|
| CA-AB | ON-PTC; AB-PRG | ON-PTC: "9 numerics-individual registration (effective June 1, 1994)"; prior format "Prior to June 1, 1994, 11 numerics". AB-PRG: "Information on the card includes the individual's nine-digit personal health number (PHN), name, gender and date of birth." | `[0-9]{9}` | none | none | none | covered |
| CA-BC | ON-PTC; BC-TP | ON-PTC: "10 numerics-individual registration". BC-TP §1.14.1: "The Medical Services Plan Personal Health Number is a 10-digit number validated by using a MOD-11 Check Digit Routine." "PHN is a true 10-digit number with a '9' always in the first position followed by nine additional digits." §1.14.2: weights "0 2 4 8 5 10 9 7 3 0" on positions 1–10; "151/11 = 13 (ignore remainder) … 13X11 = 143 … 151-143 = 8 … 11-8 = 3 The result must equal the check digit." | `9[0-9]{9}` | none | MOD-11: `11 − (Σ wᵢdᵢ mod 11)` = digit 10, weights 0,2,4,8,5,10,9,7,3 on digits 1–9; a result of 10 or 11 can never equal a digit, so such a number is refused | `9012372173` (BC-TP worked example) | covered |
| CA-MB | ON-PTC; MB-HC | ON-PTC: "9 numerics – individual registration (effective April 1, 2005)"; prior "Prior to April 1, 2005, 6 numerics". MB-HC: "issues a card (or registration certificate) to all Manitoba residents, which includes a nine-digit lifetime identification number for each Manitoban." "The Manitoba Health Card includes the cardholder's name, six digit registration number, Personal Health Identification Number (PHIN), birthdate and official language." | `[0-9]{9}` (the PHIN) | none | none | none | covered (PHIN) |
| CA-NB | ON-PTC; AB-PRG; NL-PIM | ON-PTC: "9 numerics-individual registration". AB-PRG: "The card contains the nine-digit Medicare registration number" | `[0-9]{9}` | none | none | none | covered |
| CA-NL | ON-PTC | "12 numerics-individual registration" (NL-PIM: "The claim contains provision for 12 digits"; no NL text on its own card's length was found) | `[0-9]{12}` | none | none | none | covered |
| CA-NS | ON-PTC; AB-PRG; NL-PIM | ON-PTC: "10 numerics-individual registration (effective January 1, 1994)"; prior "Prior to January 1, 1994, 11 numerics (Family Based)". AB-PRG: "The card includes the insured person's ten-digit health insurance number" | `[0-9]{10}` | none | none | none | covered |
| CA-NT | ON-PTC; ON-B | ON-PTC: "8 characters-individual registration. One alpha (D, N, H, M or T and 7 numerics)". ON-B: "updated to include "H" to the valid characters in the Northwest Territories (NWT) Health Service Number (HSN) format." | `[DNHMT][0-9]{7}` (letter set enforced) | none | none | none | covered |
| CA-NU | ON-PTC; AB-PRG; NL-PIM | ON-PTC: "9 numerics-individual registration". AB-PRG: "The card shows the following information: the nine-digit health insurance number" | `[0-9]{9}` | none | none | none | covered |
| CA-ON | ON-PTC; ON-HCV; ON-HCM; ON-IHCS | ON-HCV: `name="healthNumber" type="xs:string" pattern="[1-9]\d{9}"`; "The health number is a ten-digit number that appears on the face of every health card." `name="versionCode" … pattern=" [A-Z]{0, 2}"`; "Version code is an alphabetic identifier that along with the health number uniquely identifies a health card version. This field appears on the face of all photo cards and some standard ("red & white") cards. When present on the card, a version code is one letter or two letters." "Photo health cards are assigned a two-letter version code; standard "red & white" cards might have no version code, a single or two-letter version code." Response code 10 `FAILED_MOD10`. ON-IHCS §4.14: "it is recommended that the health number is verified by the MOD 10 Check Digit." Field "Health Number": "Satisfies the Mod 10 Check Digit routine"; example "Double 1st, 3rd, 5th, 7th and 9th Digits … Add The Unit Position Numbers Across … Subtract The Unit Position From Ten … The Check Digit is (7) therefore the Health Number 9876543217 is valid" | `[1-9][0-9]{9}` + optional `[A-Z]{1,2}` version code | 0, 1 or 2 letters, exposed separately (`versionCode(): ?string`); photo cards always have two, but a missing code is valid on red-and-white cards, so none is required | MOD 10 (Luhn): double digits 1,3,5,7,9, add the digits of each product and the even-position digits, check = `(10 − sum mod 10) mod 10` | `9876543217` (ON-IHCS worked example) | covered |
| CA-PE | ON-PTC; AB-PRG | ON-PTC: "8 numerics (SIN)-individual registration". AB-PRG: "Both cards show a unique 8-digit lifetime identification number" | `[0-9]{8}` | none | none (ON-PTC's "(SIN)" is not explained; no SIN rule is applied) | none | covered |
| CA-QC | ON-PTC; QC-GIU; QC-X | ON-PTC: "12 characters-individual. 4 alpha and 8 numerics". QC-GIU: "FORMAT Alphanumérique Longueur : 12 Gabarit : XXXX99999999" and "Le NAM est constitué des trois premières lettres du nom, de la première lettre du prénom, des deux derniers chiffres de l'année de naissance, du mois de naissance (additionné de 50 pour les femmes), du jour de la naissance et, en fin, d'un chiffre valideur. Si le nom de famille de l'usager a seulement deux caractères, la RAMQ ajoute un « X » à la 3e position de son NAM. Exemple : Juli Yu, le NAM sera YUXJ75021213." Sex: "le 7e et 8e caractère des hommes correspond au mois de naissance de l'usager et à ce nombre est ajouté 50 pour les femmes. Ainsi si le 7e chiffre est « 0 » ou « 1 » il faudra inscrire « M » pour le sexe et si le 7e chiffre est « 5 » ou « 6 » il faudra inscrire « F »." QC-X lists the parts ending "du code administratif de la RAMQ" with "BOUF 9401 1419 = masculin / BOUF 9451 1419 = féminin (mois de naissance + 50)" | `[A-Z]{4}[0-9]{2}(0[1-9]\|1[0-2]\|5[1-9]\|6[0-2])(0[1-9]\|[12][0-9]\|3[01])[0-9]{2}` | none | characters 1–3 surname, 4 given-name initial (no accessor needed); 5–6 two-digit birth year (no century); 7–8 birth month, +50 for female (`Sex::Female` for 51–62, `Sex::Male` for 01–12); 9–10 birth day; 11–12 RAMQ administrative code and "chiffre valideur" (algorithm not published: not verified) | `YUXJ75021213` (QC-GIU), `BOUF 9401 1419`, `BOUF 9451 1419` (QC-X) | covered |
| CA-SK | ON-PTC; SK-EH | ON-PTC: "9 numerics-individual registration". SK-EH: "Your health card number is nine digits." | `[0-9]{9}` | none | none | none | covered |
| CA-YT | ON-PTC; NL-PIM | ON-PTC: "9 numerics-individual registration". NL-PIM (2007): "This card bears the nine-digit health insurance number" | `[0-9]{9}` | none | none | none | covered |

**Totals:** 13 of 13 covered. ON-PTC is the format source for all thirteen. Eleven are confirmed by the province's own
text (AB, BC, MB, ON, QC, SK) or by a second ministry's (NB, NS, NU, PE, YT through AB-PRG or NL-PIM). CA-NL and CA-NT
rest on Ontario's texts alone (ON-PTC, and ON-B for the NT letter `H`).

Decisions in this table:

- **MB:** the card carries two numbers. The covered one is the nine-digit PHIN (the personal health insurance number,
  the one ON-PTC and AB-PRG name). The six-character family "registration number" (since 2024-03-01 with "one
  alphanumeric character (ie: A12345)") is not covered; see below.
- **ON:** the version code is part of the card's identity but not of the health number. 5b: `value()` is the ten
  digits; the class keeps the code separately (one letter or two). Two cards of one person share `value()` and differ
  in `versionCode()`. Whether a version code is current needs the ministry's validation service and is not modelled.
  The IHCS example and the HCV rejection code make MOD 10 a
  published rule, so a number that fails it is refused. "Subtract the unit position from ten" leaves 10 when the unit is
  0; read as the standard Luhn result 0, because a 10 cannot be a check digit and refusing every such number would
  refuse valid ones.
- **QC:** the month field is checked against the published encoding (01–12 or 51–62) and the day against 01–31; no
  February-30 check, because the year has no century and 29 February is possible. `Sex` gets no third value: QC-X says
  the X marker on the card "ne modifie pas le numéro d'assurance maladie (NAM) qui indique le sexe biologique". The
  temporary NAM (QC-GIU: "On reconnaît un NAM temporaire si celui-ci contient le caractère « M » à l'avant-dernière
  position. Exemple : BERC920401M2. La RAMQ n'émet aucune carte d'assurance maladie pour un NAM temporaire.") is on no
  card and is refused by the shape.
- **BC:** the leading `9` and the MOD-11 check are both quoted, so both are enforced.
- **NT:** the letter set `D N H M T` is closed and quoted, so it is enforced.

### Health card numbers — overlap (the factory's test oracle)

`HealthCardNumberFactory` matches all 13 classes. Inputs are normalised first. Groups in enum order:

| Normalised input class | Jurisdictions it fits |
|---|---|
| 8 digits | CA-PE |
| 9 digits | CA-AB, CA-MB, CA-NB, CA-NU, CA-SK, CA-YT |
| 10 digits | CA-NS; plus CA-BC if it starts with `9` and passes MOD-11; plus CA-ON if it does not start with `0` and passes MOD 10 |
| 10 digits + 1 or 2 letters | CA-ON if the ten digits pass ON's rules; otherwise none |
| 12 digits | CA-NL |
| one of `DNHMT` + 7 digits | CA-NT |
| 4 letters + 8 digits with a valid month (01–12, 51–62) and day | CA-QC |
| anything else (1–7, 11, 13+ digits; another letter + 7 digits; a bad QC month) | none |

Worked examples (verified by the script below), same conventions as above:

| Input | Normalised | Matches | Jurisdictions (enum order) |
|---|---|---|---|
| `1234567` | `1234567` | 0 | none |
| `12345678` | `12345678` | 1 | CA-PE |
| `123456789` | `123456789` | 6 | CA-AB, CA-MB, CA-NB, CA-NU, CA-SK, CA-YT |
| `123 456 789` | `123456789` | 6 | CA-AB, CA-MB, CA-NB, CA-NU, CA-SK, CA-YT |
| `1234567890` | `1234567890` | 1 | CA-NS (fails ON MOD 10; BC needs a leading 9) |
| `1234567897` | `1234567897` | 2 | CA-NS, CA-ON |
| `9876543217` | `9876543217` | 2 | CA-NS, CA-ON (ON-IHCS example; fails BC MOD-11) |
| `9012372173` | `9012372173` | 2 | CA-BC, CA-NS (BC-TP example; fails ON MOD 10) |
| `9012372174` | `9012372174` | 2 | CA-NS, CA-ON |
| `9012372175` | `9012372175` | 1 | CA-NS |
| `0123456789` | `0123456789` | 1 | CA-NS (ON refuses a leading 0) |
| `5584486674` | `5584486674` | 1 | CA-NS (fails ON MOD 10, see concern below) |
| `5584486674YM` | `5584486674YM` | 0 | none |
| `9876543217YM` | `9876543217YM` | 1 | CA-ON |
| `9876543217 ym` | `9876543217YM` | 1 | CA-ON |
| `9876543217A` | `9876543217A` | 1 | CA-ON |
| `9876543217ABC` | `9876543217ABC` | 0 | none |
| `1234567890AB` | `1234567890AB` | 0 | none |
| `123456789012` | `123456789012` | 1 | CA-NL |
| `N1234567` | `N1234567` | 1 | CA-NT |
| `H1234567` | `H1234567` | 1 | CA-NT |
| `A1234567` | `A1234567` | 0 | none |
| `BOUF 9401 1419` | `BOUF94011419` | 1 | CA-QC |
| `BOUF94511419` | `BOUF94511419` | 1 | CA-QC |
| `YUXJ75021213` | `YUXJ75021213` | 1 | CA-QC |
| `BOUF94311419` | `BOUF94311419` | 0 | none (month 31) |
| `BERC920401M2` | `BERC920401M2` | 0 | none (temporary NAM) |

`fromNumber('123456789')` throws with six codes. AB-PRG's illustration of an Ontario number, "enter 5584486674 only.
Omit the YM", fails ON-IHCS's MOD 10; it is an Alberta illustration, not an Ontario sample, and the Ontario rule wins.

### Provincial documents not covered

- **Driver's licence numbers of CA-MB, CA-NB, CA-NL, CA-NT, CA-NU, CA-PE and CA-YT:** their licensing authorities'
  pages, forms, manuals and statutes name the number without a format (MB's statute says only "unique alpha-numeric
  identifier"). Secondary lists that give formats for all of them were rejected. They go on the README "not covered"
  list with this reason.
- **Photo ID card numbers** where the source does not tie them to the licence number: AB, BC (BCID, a separate number
  that "will not change"), ON (Photo Card), QC (no SAAQ text on an ID card was read).
- **Encoded data of licence numbers** not defined in text: ON (MTO defines no encoding), QC (SAAQ defines none). For NS
  the encoding is defined but has a two-digit year, so no `HasBirthDate`.
- **SK validation numbers**, **ICBC client numbers**, **ON Driver Identification Number (DIN)**:
  internal or per-card numbers, not the licence number.
- **Manitoba six-character registration (family) number** (`A12345` since 2024-03-01): a card number beside the PHIN,
  not the personal health number.
- **Prior health number formats** (AB 11 numerics, MB 6 numerics, NS 11 numerics): no source says they are still valid.
- **QC temporary NAM** (`M` at position 11): issued on no card.
- **QC "chiffre valideur" and the NL, NS, NB, PE, NU, NT, YT, AB, MB, SK check digits:** no algorithm published.
- **ON version-code currency** (whether a code is the current card's): needs the ministry's validation service.

## Not covered

- The CRA individual tax number (ITN) and temporary tax number (TTN): CRA numbers, not ESDC identity documents; out of
  the plan's CA candidates.
- The SIN check digit (no published algorithm, see above), the province meaning of a SIN's first digit and the SIN
  expiry date (in the SIN record, not in the number).
- The passport print form of a one-word name (no Passport Program text found).
- Special, diplomatic, temporary and emergency passport numbers: IRCC publishes the general passport format only.
