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

Implemented: `[A-Z][0-9]{6}[A-Z]{2}` (since 2023-05-10) or `[A-Z]{2}[0-9]{6}` (before; such passports are still
valid). Format only: no check digit or letter set published (the MRZ check digit is a separate field). No official
example number. Mask: no official rule; last four characters, three for the 8-character form (`*****56BC`,
`*****456`).

## Encoded sex and birth date

Neither covered number encodes a sex or a birth date, so `Sex` is unchanged.

## Provincial documents

Driver's licence and health card numbers of the 13 provinces and territories: plan 3 Task 5.

## Not covered

- The CRA individual tax number (ITN) and temporary tax number (TTN): CRA numbers, not ESDC identity documents; out of
  the plan's CA candidates.
- The SIN check digit (no published algorithm, see above), the province meaning of a SIN's first digit and the SIN
  expiry date (in the SIN record, not in the number).
- The passport print form of a one-word name (no Passport Program text found).
