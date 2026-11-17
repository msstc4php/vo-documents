# Saudi Arabia — sources

All dates UTC. Sources read 2026-11-17.

| Source | Publisher | Where read |
|---|---|---|
| "Introduce new format validation for Other Buyer ID (BT-46) and Other Seller ID (BT-29)" (announcement, SDK v3.4.4, posted 2025-08-26T13:12:09Z) | ZATCA e-invoicing community (Announcements category) | <https://zatca1.discourse.group/t/introduce-new-format-validation-for-other-buyer-id-bt-46-and-other-seller-id-bt-29/8189> (JSON: `.../8189.json`) |
| Electronic Invoice XML Implementation Standard, v1.2 (2023-05-19), BR-KSA-14 and section 13.3.5 | ZATCA | <https://zatca.gov.sa/ar/E-Invoicing/SystemsDevelopers/Documents/20230519_ZATCA_Electronic_Invoice_XML_Implementation_Standard_%20vF.pdf> (`pdftotext`) |
| Civil Status Law executive regulations, Art. 146 (amended) | MOI | only secondary reports found (Okaz, Ajel, qanoniah.com): "quoted from", never a rule |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6 | ICAO | see `ICAO.md` |

Reachability: `moi.gov.sa` (Civil Affairs pages) answers "Socket is closed" for WebFetch; `x.com` answers 402 (the 2013
tweet of the Ministry of Labour and Social Development "رقم الإقامة يبدأ بالرقم 2" is therefore unread and unused);
`zatca.gov.sa` is refused for `curl` but readable through WebFetch, which saves the PDF. Searches over `absher.sa`,
`my.gov.sa`, `moi.gov.sa`, `hrsd.gov.sa`, `gosi.gov.sa` and `rega.gov.sa` found no page giving the number formats
(`hrsd.gov.sa/ur/contact-us/faq` read: no format). Secondary only (never a rule, never cited in code): noblecoreksa.com,
almaal.org, ajsrp.com, ksaexpats.com, qoyod.com, SAP notes, GitHub validator issues, Microsoft Purview ("Checksum: No").

## Names

No agency text on the characters or the number of parts of a recorded name was readable. The Civil Status Law
executive regulations were reported (secondary only: Okaz, Ajel) to require on the ID card "الاسم رباعي" (first, father's,
grandfather's and family name, Art. 146 as amended) and to allow a family name with `بن` or `آل`; this applies to new
registrations and could not refuse a recorded name in any case, so no part count is enforced.

- **Parts:** `GivenName`, `FatherName`, `GrandfatherName`, `FamilyName`; only the given name is required. A name without
  father or grandfather parts is representable; the order of `full()` is given, father, grandfather, family.
- **Letters:** `\p{sc=Arabic}` letters (general category Lo) per the plan decision. Harakat U+064B-0652 and the
  superscript alef U+0670 are Script=Inherited and listed explicitly; they are allowed after a letter only, so a word
  cannot start with one. Other combining marks (Quranic annotation signs U+06D6-06ED, maddah/hamza marks that do not
  compose) are refused.
- **Tatweel U+0640** (Script=Common, general category Lm) is removed during normalisation: it is typographic, and two
  stored values would denote one name. A name made of tatweel only is empty and refused.
- **NFC:** `ا` + U+0653/0654/0655 compose to `آ`/`أ`/`إ`, `و` + U+0654 to `ؤ`, `ي` + U+0654 to `ئ`: one stored value.
  NFC also orders the marks of a letter by combining class (fatha U+064E before shadda U+0651), so both typings of
  `مَّ` are one value.
- **Refused:** Arabic-Indic and Extended Arabic-Indic digits, Arabic punctuation (`،` `؛` `؟`, U+06D4), format
  characters (zero-width joiners, RLM, number signs U+0600-0605), and presentation forms (U+FB50-FDFF, U+FE70-FEFF; NFKC
  differs, so the base class refuses them as compatibility characters).
- **Separators:** a space and a hyphen (the package default for non-Latin scripts; no source restricts them). A particle
  such as `آل` or `بن` is an ordinary word: `آل سعود` and `بن لادن` are accepted; `عبدالله` and `عبد الله` are two
  different values (both are written).
- **Latin form (decision):** mandatory `Icao\PersonName $latin`, never derived and not compared with the Arabic name
  (the passport spelling is a personal choice: Mohammed/Muhammad/Mohammad; the Passports Directorate takes it from the
  civil register and a correction goes through Civil Affairs, reported by Ajel, secondary). Shape
  `{given, father, grandfather, family, latin}`; the three optional keys may be null or missing.

## National ID and iqama (`Document\SA\NationalIdentityNumber`, `Document\SA\IqamaNumber`)

ZATCA announcement (SDK v3.4.4, posted by the community moderator "Ibrahem Daoud", in the Announcements category of ZATCA's
community; the host is `zatca1.discourse.group`, not `zatca.gov.sa`, so it is treated as ZATCA's own publication on its
developer forum, not as a gazette; the owner may reclassify it): "BR-KSA-F-10 (Seller/Buyer ID – Scheme ID = “NAT”)
Trigger: Warning if (BT-29)/(BT-46) value violates: Not exactly 10 digits in length. Contains non-numeric characters.
Does not begin with the digit ‘1’." and "BR-KSA-F-11 (Seller/Buyer ID – Scheme ID = “IQA”) … Does not begin with the digit
‘2’." The standard v1.2 (BR-KSA-14) lists the schemes "National ID with "NAT" as schemeID" and "Iqama Number with
"IQA" as schemeID" and requires only alphanumerics; the digit rules exist only in the announcement. ZATCA reports them as
warnings.

- **Two classes**, because the rule quoted names both: `NationalIdentityNumber` (10 digits, first digit 1) and
  `IqamaNumber` (10 digits, first digit 2). A number of the other kind is refused with "invalid first digit".
- **Enforced:** ten digits after removing space, hyphen, NBSP and the ideographic space; fullwidth, Arabic-Indic
  (U+0660-0669) and Extended Arabic-Indic (U+06F0-06F9) digits fold to ASCII (`١٠١٢٣٤٥٦٧٨` is `1012345678`).
- **Check digit: not enforced.** No government text quotes one; the Luhn claim comes from GitHub issues and validator sites
  only, and Microsoft Purview lists "Checksum: No".
- **Display form:** none quoted, so no `formatted()`. `masked()` keeps the last four digits: `******5678`.
- **Driving licence:** carries the identity number; there is no class for it (use `NationalIdentityNumber` or
  `IqamaNumber`).

## Not covered

- **Border number (رقم الحدود):** only blogs (noblecoreksa.com, ksaexpats.com: ten digits, "usually" starting with 3 or 4)
  and a news item on the Jawazat service; no government text gives its format. Format-only would be a ten-digit
  string with no rule, so no class.
- **Passport number:** the Passports Directorate (Jawazat) publishes no format that was found; searches returned only
  third-party pages. Not covered.
- **Check digit of the national ID / iqama:** see above.
