# Australia — sources

All dates UTC. Sources read 2026-11-13. Official publications come first; a "quoted from" copy is the secondary text a
quote was taken from when the official page could not be read from the build environment.

**Reachability.** Most Australian government sites refuse or drop connections from the build environment on
2026-11-13: `idmatch.gov.au`, `www.usi.gov.au`, `softwaredevelopers.ato.gov.au`, `www.ato.gov.au`,
`implementer.digitalhealth.gov.au`, `www.dfat.gov.au` (HTTP/2 stream reset or timeout), `www.transport.vic.gov.au`,
`transport.vic.gov.au`, `www.justice.tas.gov.au`, `nt.gov.au` (HTTP 403), `meteor.aihw.gov.au` (bot challenge).
Read in full: `immi.homeaffairs.gov.au`, `qhdd.health.qld.gov.au`, `www.qld.gov.au/transport/code`,
`www.service.nsw.gov.au`, `www.transport.wa.gov.au`, and the Internet Archive copy of the Digital Health namespace page.

## Names (`Name\AU\*`)

Australia has no federal rule on the characters of a personal name: names are registered by the eight state and
territory registries of births, deaths and marriages, and the passport carries "the name … exactly how it appears on
the customer's Australian birth certificate, Australian citizenship certificate or Australian RBDM name change
certificate" (DFAT passport policy, quoted from a search snippet of
<https://www.dfat.gov.au/about-us/publications/corporate/passports/passport-policy>; page not reachable). Names are
written in the Latin script; diacritics occur (`Zoë`, `Müller`), and the passport's Latin form follows ICAO Doc 9303
Part 3 §6.A (the multinational Latin table, see `ICAO.md`).

How the package applies this (designer's calls, no official character rule found):

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` | `\p{Latin}` | hyphen, apostrophe, space | double-barrelled (`Smith-Jones`), `O'Brien`, `van der Berg` |
| `GivenName` | `\p{Latin}` | hyphen, apostrophe, space | several given names (`Joan Catherine`), `Mary-Anne`, `D'Arcy` |

- U+2019 (’) is stored as U+0027 ('), as for every part that allows the apostrophe (`AbstractNamePart`).
- A Latin letter outside the ICAO table is spelled as its **base letter** (designer's call, `ICAO.md` "Letters outside
  table 6.A"): `Nguyễn` → `NGUYEN`, Romanian `Ș`/`Ț` (comma below) → `S`/`T`. Without it such a name could never be
  built, not even with an override. A letter with no base in the table (`ƒ`) passes the part but is refused by
  `PersonName`, because it has no ICAO spelling.
- A compatibility character (NFKC differs from NFC: fullwidth `Ｓｍｉｔｈ`, `ª`, `ﬁ`) is refused by the part
  (`AbstractNamePart`, every country): it would be a second spelling of the same stored name. Characters table 6.A
  lists itself (`Ĳ`, `Ŀ`) are kept and take their table spelling (`Ĳsselmeer` → `IJSSELMEER`).
- **Derived Latin:** `FAMILY<<GIVEN` with the default spelling of each character (`Zoë` → `ZOE`,
  `Müller` → `MUELLER`); apostrophes are omitted (`O'Brien` → `OBRIEN`, ICAO Part 3 §4.6).
- **Override** (policy `Name\TransliterationVariant`): a Latin form is accepted only when it is one of the ICAO
  spellings of the parts (`MULLER<<ZOE` for `Müller Zoë`); case, spaces next to `<`, MRZ padding and `<` for a hyphen
  are normalised (`Name\LatinSpelling`, matched part by part, at most 4096 spellings per part, computed only when the
  override is not the derived form). An override equal to the derived form is not kept. Unlike RU (any valid ICAO name,
  RU.md §4), a Latin-script name keeps this table rule: its Latin form is the name itself up to the table's spellings.
- **Display order** (`full()`): given names then family name (`Joan Catherine Citizen`), the order of the passport
  application form and of the licences of every jurisdiction except the ACT, Northern Territory and Tasmania, which
  print the family name first.
- **Not covered:** a single name (mononym). The passport form asks for it in "Family name" with `XXX` in "Given name"
  (passports.gov.au, quoted from a search snippet); the brief's constructor requires a given name.

Payload (`PersonNameCodec`): `{family, given, latin}` with `latin` the override only; the JSON view is
`{family, given, effectiveLatin}` and is refused by the codec, as for RU.

## Driving licence numbers (`Document\AU\DriverLicence\*`)

Every state and territory issues its own licence. The issuing authorities describe **where** the numbers are printed
but none of the pages read publishes the format:

| Jurisdiction | Authority page read | Format stated |
|---|---|---|
| NSW | <https://www.service.nsw.gov.au/verifying-your-identity/document-examples> | no ("Name followed by card number. Licence number. Date of birth.") |
| Qld | <https://www.qld.gov.au/transport/code> | no (card number "in the bottom right hand corner") |
| WA | <https://www.transport.wa.gov.au/licensing/identity/identity-security> | no ("All WA driver's licence cards include 2 different numbers") |
| Vic | <https://transport.vic.gov.au/road-and-active-transport/registration-and-licensing/licences/conditions-exemptions-other-licence-types-and-card-numbers/licence-card-numbers> | not reachable (403) |
| Tas | <https://www.justice.tas.gov.au/bdm/drivers-licence-card-number> | not reachable (403) |
| NT | <https://nt.gov.au/driving/licence/features-of-your-driver-licence/design-and-security-features-of-your-nt-driver-licence> | not reachable (403) |
| SA, ACT | <https://www.sa.gov.au/topics/driving-and-transport/licences/licence-data-breach>, Access Canberra: no format page found | no |

The formats are published by the Commonwealth for the Document Verification Service, which checks a licence against
the issuing jurisdiction's record. The DVS match specification (idmatch.gov.au) is available to registered business
users only and the public site was not reachable. **The source used** is the Department of Home Affairs help text for
entering a licence in ImmiAccount, which states the DVS field rules for every jurisdiction:

**Department of Home Affairs, "Australian driver licence"**, ImmiAccount help text,
<https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx> (last updated 23/09/2024):

> When entering the licence number and card number:
> - the licence number and the card number are different, and they are located in different places on the licence
> - you must enter the correct number of characters
> - you must enter the correct type of characters:
>   - numeric characters are 'numbers', that is 0 to 9
>   - alphanumeric characters are a combination of characters including numbers 0 to 9 and letters 'a' to 'z' or
>     'A' to 'Z'.

| Jurisdiction | Licence number (quoted) | Card number (quoted) | Sample licence on the page |
|---|---|---|---|
| ACT | "next to 'Licence No.' on the front of the card (must contain up to 10 numeric characters)" | "next to the photograph (must contain 10 alphanumeric characters)" | `1234567890`, card `A123456789` |
| NSW | "under 'Licence No.' on the front of the card (must contain 6-8 alphanumeric characters)" | "under 'Card Number' above the photograph (must contain 10 numeric characters)" | `01234567` |
| NT (examples A and B) | "next to 'LICENCE NO' / under 'Licence Number' on the front of the card (must contain up to 10 numeric characters)" | "under 'Card Number' on the back of the card (must contain 6-8 numeric characters)" | `1564102`, `1164381` |
| Qld (examples A and B) | "under 'LICENCE NO.' on the front of the card (must contain 8-9 numeric characters)" | "on the front and back of the card (must contain 10 alphanumeric characters)" | `123 456 789`, card `FCC68114B9` |
| SA | "under 'Licence No.' on the front of the card (must contain 6 alphanumeric characters)" | "on the back of the card (must contain 9 alphanumeric characters)" | `000000` (placeholder) |
| Tas | "under 'LICENCE NO.' on the front of the card (must contain 6-8 alphanumeric characters)" | "under 'Card Number' on the back of the card (must contain 9 alphanumeric characters)" | `XXXXXXX` (placeholder) |
| Vic | "under 'LICENCE NO.' on the front of the card (must contain up to 10 numeric characters)" | "under 'Card Number' on the back of the card (must contain 8 alphanumeric characters" | `123456789` (the image may be cut at the right edge) |
| WA | "under 'LICENCE NUMBER' on the front of the card (must contain 7 numeric characters)" | "on the back of the card (must contain 8-10 alphanumeric characters)" | `6437772` |

The sample numbers were read from the page's images (`/help-text/eplus/PublishingImages/<STATE> DL Front.jpg`); they
are the tests' "official examples". SA and Tas print placeholders only, so their tests use synthetic numbers of the
stated shape.

Implemented (`Document\AU\DriverLicence\<Abbrev>DriverLicenceNumber`, all `Document\AU\DriverLicenceNumber`):

| Class | Pattern after normalisation |
|---|---|
| `ActDriverLicenceNumber` | `[0-9]{1,10}` |
| `NswDriverLicenceNumber` | `[0-9A-Z]{6,8}` |
| `NtDriverLicenceNumber` | `[0-9]{1,10}` |
| `QldDriverLicenceNumber` | `[0-9]{8,9}` |
| `SaDriverLicenceNumber` | `[0-9A-Z]{6}` |
| `TasDriverLicenceNumber` | `[0-9A-Z]{6,8}` |
| `VicDriverLicenceNumber` | `[0-9]{1,10}` |
| `WaDriverLicenceNumber` | `[0-9]{7}` |

- Normalisation: the separators of `Internal\Digits::SEPARATORS` (space, hyphen, tab, CR, LF, NBSP, U+2007, U+202F) are
  removed and ASCII letters upper-cased; leading zeros are kept (`01234567`). No check digit is published: **shape
  only**.
- "Alphanumeric" is taken literally: an all-letter or all-digit value of the stated length is accepted.
- `masked()` (no official rule): every character but the last `min(3, ⌊length / 2⌋)` becomes `*`
  (`1234567890` → `*******890`, `01234567` → `*****567`, `12` → `*2`, `7` → `*`). See "Masking rule" below.
- Messages: "Invalid AU-NSW driver licence number (9 characters)." — the ISO 3166-2 code, the document name and the
  character count after normalisation ("(too long)" above 40) when the length is wrong, or "Invalid AU-NSW driver
  licence number (invalid character)." when the length fits but a character is outside the jurisdiction's set
  (`Internal\NumberFormat`).

### The jurisdiction factory (`Document\AU\DriverLicenceNumberFactory`)

The formats overlap so much that **no number fits exactly one jurisdiction**:

| Input after normalisation | Jurisdictions (enum order) |
|---|---|
| 1–5 digits | ACT, NT, Vic |
| 6 digits | ACT, NSW, NT, SA, Tas, Vic |
| 7 digits | ACT, NSW, NT, Tas, Vic, WA |
| 8 digits | ACT, NSW, NT, Qld, Tas, Vic |
| 9 digits | ACT, NT, Qld, Vic |
| 10 digits | ACT, NT, Vic |
| 6 characters with a letter | NSW, SA, Tas |
| 7–8 characters with a letter | NSW, Tas |
| anything else | none |

So `fromNumber()` throws `AmbiguousJurisdictionException` (message: ISO codes only) or `InvalidDocumentNumberException`
("Invalid Australian driver licence number (N characters).") for every real input today; its single-match branch
exists for the pattern later plans copy and would only be reached if a format changed. Callers that know the issuing
jurisdiction use `fromNumberIn()`. The resolution (parse once per jurisdiction, one match → it, none → invalid,
several → ambiguous in enum order) is the generic `Internal\SubdivisionResolver`, tested with fixtures; the factory
keeps only the exhaustive enum → class `match`.

### Card numbers — not covered

The card number is a second number on the same licence, changed on every replacement (WA, SA pages above) and
required by the DVS since 2022–2023. Its formats are published in the same Home Affairs table (column "Card number"
above), but a card number identifies the physical card, not the licence, and the brief's `DriverLicenceNumber` covers
the licence number only. Left for a later plan (one class per jurisdiction behind a separate interface).

## Tax file number (`Document\AU\TaxFileNumber`)

1. **ATO, "How to obtain the tax file number algorithm"**, <https://softwaredevelopers.ato.gov.au/obtainTFNalgorithm>
   and "Tax file number (TFN) algorithm", <https://softwaredevelopers.ato.gov.au/tax-file-number-tfn-algorithm>.
   Not reachable (timeouts); quoted from search snippets: the algorithm "tests whether a number quoted as a TFN is
   valid" and is distributed **only to registered digital service providers** through the ATO's Online services for
   DSPs collaboration hub. The algorithm text itself is therefore not public and was not read.
2. **ANAO Audit Report No. 37 1998–99, "Management of Tax File Numbers"**,
   <https://www.anao.gov.au/sites/default/files/anao_report_1998-99_37.pdf> (not reachable; quoted from a search
   snippet): the TFN is generated by "a check digit routine, applied to a seven or eight digit number", and the
   routine is "regarded as widely known and in the public domain".

**No public official text; algorithm as used in practice.** Searched on 2026-11-13 UTC: law.ato.gov.au, ato.gov.au,
softwaredevelopers.ato.gov.au (only the restricted-distribution pages and the payment-reference-number guides, whose
modulus-11 weights are for other numbers), legislation.gov.au. The weights are quoted from
<https://en.wikipedia.org/wiki/Tax_file_number> ("Example": multipliers 1, 4, 3, 7, 5, 8, 6, 9, 10; "253 is a multiple
of 11 … Therefore, the number is valid" for `123456782`; the section carries "citation needed"); every published
description agrees on them. The class cites the ATO page only.

Implemented: 9 digits; Σ dᵢ·wᵢ with w = 1, 4, 3, 7, 5, 8, 6, 9, 10 must be ≡ 0 (mod 11). The 8-digit TFNs once
issued to non-individuals (same Wikipedia article) are not covered: their weights are not published anywhere official.
Test numbers: `123 456 782` (the worked example) and `330 600 376` (printed on a 1982 ATO notice of amended assessment
shown in the same article; sum 198 = 11 × 18). `formatted()` `123 456 782`; `masked()` (no official rule) keeps the
last three digits: `*** *** 782`.

## Medicare card number (`Document\AU\MedicareCardNumber`)

1. **Australian Digital Health Agency, namespace `http://ns.electronichealth.net.au/id/medicare-number`**,
   <https://implementer.digitalhealth.gov.au/namespaces/id/medicare-number/index.html> (not reachable; read from the
   Internet Archive copy of 2026-04-22):

   > Identifiers that may be represented in this namespace include the 11 digit Individual Medicare Card Number and the
   > 10 digit Medicare Card Number. … The format of the value of the identifier is 10 or 11 characters, made up of:
   > 8 digits (Medicare card); the ninth character (the checksum) is a digit; the tenth character (the issue number)
   > is a digit; the eleventh character (the individual reference number) is optional and if included a digit
2. **Queensland Health data dictionary, "Person-Medicare card number" (QH 040164 v3)**, <https://qhdd.health.qld.gov.au>
   (data element 41731), based on AIHW METeOR 270101: "a card number (8 digits), a check digit (1 digit), an issue
   number (1 digit), and a person number (1 digit)".
3. **The check algorithm: Australian Digital Health Agency, "Use of Healthcare Identifiers in Health Software
   Systems — Software Conformance Requirements", Appendix E "Medicare card number check algorithm"**
   (<https://implementer.digitalhealth.gov.au/resources/download/component/7865/77200dfd-454f-4178-8731-a3dd114a0aff>);
   not reachable and not archived; no other official host serves it (searched servicesaustralia.gov.au,
   humanservices.gov.au, digitalhealth.gov.au, health.gov.au and meteor.aihw.gov.au, which answers with a bot challenge,
   on 2026-11-13 UTC). **No public official text; algorithm as used in practice.** Quoted from
   <https://curmi.com/australian-health-identifiers/>: "The first digit is in the range 2–6. The next 7 digits are in
   the range 0–9. … Multiple them in turn using the following weights: 1, 3, 7, 9, 1, 3, 7, 9. … The check digit is
   the sum of the weighted values modulo 10." Worked example: `31899770` → check digit `2`.

Implemented: the **10-digit Medicare card number** (the card's number); the 11-digit individual number (card number
plus the individual reference number before each name) is not this value object and an 11-digit input is refused.
The first digit is 2–6; digit 9 = (Σ dᵢ·wᵢ over digits 1–8, w = 1, 3, 7, 9, 1, 3, 7, 9) mod 10; digit 10 (issue
number) is any digit (ADHA: "is a digit"). Test number: `3189 97702 1` (the worked example's first nine digits, issue
number 1 added). `formatted()` `3189 97702 1` (the card prints 4-5-1); `masked()` (no official rule) keeps the last
three digits: `**** ***02 1`.

## Masking rule

No AU source publishes a masking rule. Every AU `masked()` keeps the last **three** characters, not the four that plan
1 set as the package default (and that every RU class keeps): AU numbers are short (driver licences 1-10 characters,
TFN 9, Medicare 10), so four would show almost half of a short number. Licence numbers keep at most half of their
length (`min(3, ⌊length / 2⌋)`), so a 2-character number shows one.

## Where this note departs from plan 1, task 6 (the source wins)

| Plan | Source | Implemented |
|---|---|---|
| licence formats "from the issuing authority" | no authority publishes one; the Commonwealth DVS rules do | Home Affairs table |
| "the card number where it is separate" | separate everywhere; formats published | not covered (licence number only) |
| `fromNumber()` returns the one match | the published formats always overlap | always ambiguous or invalid today |
| TFN checksum from the ATO | ATO algorithm restricted to registered developers | no public official text; algorithm as used in practice |
| Medicare check digit from Services Australia | ADHA Appendix E, not reachable | no public official text; algorithm as used in practice |
