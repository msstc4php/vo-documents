# Switzerland — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| Wegleitung über Versicherungsausweis und individuelles Konto (WL VA/IK), 318.106.02 d, gültig ab 1. Januar 2024, Stand 1. Oktober 2025: Rz 1101-1203, Anhang 7 "Kontrollzifferprüfung" | BSV (Bundesamt für Sozialversicherungen) | <https://sozialversicherungen.admin.ch/de/d/6938/download> |
| UPI – Benutzerhandbuch (handbook) v3.2, § 5.1.2.2.2 (check digit, AHVV Art. 134quinquies Abs. 3) | ZAS (Zentrale Ausgleichsstelle) | <https://www.zas.admin.ch/dam/de/sd-web/Ivf0gI31cqvU/upi%20-%20handbook%20v3.2de.pdf> |
| Verordnung des EJPD über die Ausweise für Schweizer Staatsangehörige (SR 143.111), Stand 1. November 2026: Art. 3, 4, 5, 16, 22 | Fedlex | <https://www.fedlex.admin.ch/eli/cc/2010/96/de> (read as the filestore HTML <https://fedlex.data.admin.ch/filestore/fedlex.data.admin.ch/eli/cc/2010/96/20261101/de/html/fedlex-data-admin-ch-eli-cc-2010-96-20261101-de-html.html>) |
| Ausweisverordnung, VAwG (SR 143.11), Stand 1. November 2026 (checked for name and number rules: none beyond "Ausweisnummer" in Anhang 1) | Fedlex | <https://www.fedlex.admin.ch/eli/cc/2002/468/de> (filestore HTML) |
| Zivilstandsverordnung (ZStV), Änderung vom 26. Juni 2024, AS 2024 335: Art. 80 "Zeichensatz", Art. 99f | Fedlex (AS) | <https://www.fedlex.admin.ch/eli/oc/2024/335/de> (PDF-A from the data filestore) |
| "Sonderzeichen im Namen" | BJ (Bundesamt für Justiz) | <https://www.bj.admin.ch/de/sonderzeichen-im-namen> (through WebFetch) |
| "AHV-Nummer" | BSV | <https://www.bsv.admin.ch/de/ahv-nummer> (through WebFetch) |
| fedpol, "Pass" page, box "Ausweisnummer" (Letzte Änderung 21.08.2025) | fedpol | <https://www.fedpol.admin.ch/fedpol/de/home/pass---identitaetskarte/pass.html>, Internet Archive copy of 2026-02-14 10:59:01 UTC: <http://web.archive.org/web/20260214105901/https://www.fedpol.admin.ch/fedpol/de/home/pass---identitaetskarte/pass.html> |
| fedpol, "Die neue Schweizer Identitätskarte" (flyer, 02.02.2023), specimen | fedpol | <https://www.fedpol.admin.ch/dam/fedpol/de/data/pass-id/flyer-neue-idk.pdf.download.pdf/flyer-neue-idk.pdf>, Internet Archive copy of 2025-07-30 11:42:04 UTC: <http://web.archive.org/web/20250730114204/https://www.fedpol.admin.ch/dam/fedpol/de/data/pass-id/flyer-neue-idk.pdf.download.pdf/flyer-neue-idk.pdf> |
| fedpol, "Neuer Schweizerpass – FAQ" (31.10.2022) | fedpol | <https://www.fedpol.admin.ch/dam/fedpol/de/data/pass-id/faq-neuer-pass.pdf.download.pdf/faq-neuer-pass-d.pdf>, Internet Archive copy of 2025-07-30 11:42:04 UTC: <http://web.archive.org/web/20250730114204/https://www.fedpol.admin.ch/dam/fedpol/de/data/pass-id/faq-neuer-pass.pdf.download.pdf/faq-neuer-pass-d.pdf> |
| "Neuer Schweizer Pass: Häufig gestellte Fragen (FAQ)" (2024-06) | Passamt, Bevölkerungsamt, Justiz- und Sicherheitsdepartement des Kantons Basel-Stadt | <https://media.bs.ch/original_file/f9c82519e56d4de65ec334e1f12a254364907d80/pw-faq-neuer-schweizer-pass-2024-06.pdf> |
| Weisungen betreffend die Ausstellung von Lernfahr- und Führerausweisen, 15. April 2023 (ASTRA-A-05BI 3401/35), Ziffer 5, Anhang 1 Nr. 13, Anhang 2, specimens | ASTRA (Bundesamt für Strassen) | <https://www.astra.admin.ch/dam/astra/de/dokumente/dokumente-strassenverkehr/weisungen/fuehrerausweis-kreditkartenformat.pdf.download.pdf/Weisungen%20betreffend%20die%20Ausstellung%20von%20Lernfahr-%20und%20F%C3%BChrerausweisen.pdf>, Internet Archive copy of 2025-06-23 18:23:54 UTC: <http://web.archive.org/web/20250623182354/https://www.astra.admin.ch/dam/astra/de/dokumente/dokumente-strassenverkehr/weisungen/fuehrerausweis-kreditkartenformat.pdf.download.pdf/Weisungen%20betreffend%20die%20Ausstellung%20von%20Lernfahr-%20und%20F%C3%BChrerausweisen.pdf> |
| PRADO CHE-AO-04001 (Swiss passport, first issued 31/10/2022) | Council of the EU | <https://www.consilium.europa.eu/prado/en/CHE-AO-04001/index.html>, Internet Archive copy of 2024-08-18 17:32:57 UTC |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | <https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf> |

Reachability: `sozialversicherungen.admin.ch`, `www.zas.admin.ch`, `fedlex.data.admin.ch` (filestore and SPARQL
endpoint) and `media.bs.ch` answer to curl. `www.fedlex.admin.ch` pages need JavaScript; the act texts were read from
the filestore HTML found through the SPARQL endpoint. `www.fedpol.admin.ch` and `www.bj.admin.ch` PDFs answered 404 at
their published paths, and the ASTRA instructions answered 404: their unmodified Internet Archive copies were read
(earlier ruling: such a copy counts as the official source). `www.consilium.europa.eu` (PRADO) answered 403; its
archived page gives no number layout (only "document number" as a laser-engraved field). The canton of Schwyz paper
"Kontrollziffer Berechnung" (`www.sz.ch`) answered 403 and is not used. Secondary only (never a rule): python-stdnum
`stdnum.ch.ssn`, Microsoft Purview "Switzerland SSN AHV number".

## Names (`Name\CH\*`)

1. **SR 143.111, Art. 4 Name** (passport and identity card): "1 Der amtliche Name wird gemäss Infostar,
   Einwohnerkontrollregister oder dem Informationssystem Ausweisschriften (ISA) eingetragen. 2 Bei der Ausstellung
   eines Passes oder einer Identitätskarte stehen für den Namen inklusive Leerzeichen maximal 50 Zeichen zur
   Verfügung."
2. **SR 143.111, Art. 5 Vorname**: "1 Der Vorname wird gemäss Reihenfolge im Infostar, Einwohnerkontrollregister oder
   ISA eingetragen. […] 4 Hat die antragstellende Person keinen Vornamen, so werden drei Sterne (\*\*\*) eingetragen."
3. **SR 143.111, Art. 4a Abs. 2** (Allianzname): "Mit einem Bindestrich kann diesem angehängt werden: […]"
4. **SR 143.111, Art. 22 Zeichensatz**: "Die Daten werden nach dem hinterlegten Standardzeichensatz erfasst (ISO-Norm
   8859-1 + Latin Extended-A)." In force since 11 November 2024 (AS 2025 265).
5. **ZStV Art. 80 Zeichensatz** (AS 2024 335, civil status register Infostar): "Die Daten werden nach dem hinterlegten
   Standardzeichensatz erfasst (ISO-Norm 8859-1 + Latin Extended-A)." **Art. 99f Abs. 1**: a person registered before
   the change "kann […] das Gesuch stellen, dass ihre Namen mit dem Zeichensatz nach Artikel 80 in der Fassung dieser
   Änderung geschrieben werden" — names registered earlier keep the former set unless the person asks. The BJ page
   "Sonderzeichen im Namen" calls the new set "den erweiterten neuen Zeichensatz (ISO-Norm 8859-1 + Latin
   Extended-A)", applied from 11 November 2024 (Infostar NG): the former set (not named there) is a subset, so names
   written in it are accepted too.
6. No Swiss act read (SR 143.11, SR 143.111) has a rule on the Latin spelling in the machine-readable zone or lets a
   passport keep another spelling.

How the package applies this: of the register set it keeps only the letters and the separators `-' ` (hyphen,
apostrophe, space); its digits, punctuation and other signs are refused in names.

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` | ISO 8859-1 letters (`A-Z a-z À-Ö Ø-ö ø-ÿ`) and Latin Extended-A (U+0100-U+017F) | hyphen, apostrophe, space | (4), (5); the Allianzname is joined by a hyphen (3), spaces count in the 50 characters (1), the apostrophe is an ISO 8859-1 character (`D'Amico`, `de Weck`) |
| `GivenName` | the same | the same | several given names in register order (2) |

- The four national languages fit in these letters: German `ä ö ü`, French `é è ê ë à â ç ï î ô û ù ÿ œ`, Italian
  `à è é ì ò ù`, Romansh `à è é ì ò ù ä ö ü`. `× ÷` are not letters and are refused; `ª º µ` are compatibility
  characters and are refused by the shared rule of `AbstractNamePart`, as are `ŉ` and `ſ` of Latin Extended-A; `Ŀ ŀ`
  are kept (ICAO table 6.A lists them). `ĸ` passes the part but has no ICAO spelling, so `PersonName` refuses it.
  Letters outside the set (`Ș`, `Ț`, `Ơ`, Cyrillic, Greek) are refused.
- The 50-character limit of (1) is a printing limit of the document (longer names are shortened on the card), not a
  limit of the name, so the package keeps its shared 100-character limit.
- **Given names optional:** a person without a given name (Art. 5 Abs. 4, "\*\*\*" on the document):
  `PersonName($family, null)`, Latin form a mononym (`FAMILY`, no `<<`).
- **Derived Latin:** `FAMILY<<GIVEN` through ICAO table 6.A: `Müller, Anna` → `MUELLER<<ANNA`, `Schweizer Sample,
  Helvetia` (fedpol specimen) → `SCHWEIZER<SAMPLE<<HELVETIA`, `Caflisch, Ursina` → `CAFLISCH<<URSINA`.
- **Override policy `Name\TransliterationVariant`** (6): an override must be another table 6.A spelling of the parts
  (`MULLER<<ANNA` for `Müller Anna`); a different name is refused; an override equal to the derived form is not kept.
- **Not covered:** the Allianzname as a separate field (it is the family name with a hyphen when the holder asks for
  it), the "\*\*\*" placeholder itself.

Payload (`PersonNameCodec`): `{family, given, latin}` with `given` null when there is no given name and `latin` the
override only; the JSON view is `{family, given, effectiveLatin}` and is refused by the codec.

## AHV number (`Document\CH\AhvNumber`)

1. **WL VA/IK, Rz 1201** (verbatim):

   > Die AHV-Nummer setzt sich zusammen aus
   > – dem Code für das Ausgabeland Schweiz (756) [Stellen] 1–3
   > – einer 9-stelligen Zufallszahl 4–12
   > – einer Prüfziffer 13
   >
   > Sie wird wie folgt dargestellt: 756.3047.5009.62

2. **WL VA/IK, Rz 1202**: "Die Prüfziffer wird nach der Formel gemäss Anhang 7 gebildet." Rz 1203: "Die Bildung von
   eigenen (andere als durch die ZAS vergebene) AHV-Nummern ist nicht möglich und untersagt."
3. **WL VA/IK, Anhang 7 "Kontrollzifferprüfung", B. Beschreibung der Kontrollzifferlogik** (verbatim):

   > Die Kontrollziffer ist die letzte Ziffer (xn). Sie wird wie folgt errechnet:
   > – In einem ersten Schritt werden die Ziffern von rechts nach links, beginnend mit der vorletzten (xn-1),
   > abwechselnd mit 3 und 1 multipliziert. Anschliessend werden diese Produkte addiert:
   > Zwischensumme = (3xn-1) + (1xn-2) + (3xn-3) …
   > – in einem zweiten Schritt wird die Zwischensumme so ergänzt, dass die Gesamtsumme dem nächsthöheren Vielfachen
   > der Zahl 10 entspricht: Die ergänzende Zahl ist die Kontrollziffer xn.
   >
   > Hinweis: Ist die Zwischensumme bereits ein Vielfaches von 10, so ist die Kontrollziffer 0.

   C. Illustration: `756 1234 5678 9` → multipliers `1 3 1 3 1 3 1 3 1 3 1 3`, Zwischensumme 123, check digit 7
   (`756.1234.5678.97`).
4. **UPI handbook § 5.1.2.2.2**: "Die Prüfziffer der AHV-Nummer soll sicherstellen, dass diese Nummer gültig ist
   (zwecks Erkennung von Tippfehlern). Im Übrigen besteht die Verpflichtung (Art. 134quinquies Abs. 3 AHVV), vor jeder
   systematischen Verwendung sicherzustellen, dass die AHV-Nummer korrekt gebildet ist." Its tables also print
   `756.1234.5678.90` and `756.0123.4567.89` as illustration rows; both fail the Anhang 7 check and are not used as
   examples.
5. **BSV, "AHV-Nummer" page**: "Die AHV-Nummer beinhaltet 13 Ziffern und beginnt mit dem Ländercode 756 für die
   Schweiz." "Die 13-stellige AHV-Nummer ist völlig anonym und zufallsgeneriert." — it encodes nothing.

Implemented: 13 digits after the separators of `Internal\Digits::SEPARATORS` and the dots of the display form are
removed; the first three must be `756` (Rz 1201; otherwise "(country code)"); **the check digit is verified** (Anhang 7:
weights 1, 3, … from the left over the first twelve digits, the sum with the check digit divisible by 10;
`Checksum::weightedMod(…, [1, 3, …, 1], 10) === 0`). Examples: `756.3047.5009.62` (Rz 1201), `756.1234.5678.97`
(Anhang 7). No encoded data. `formatted()` is the display form of Rz 1201 (`756.3047.5009.62`); `masked()` keeps the
last four digits in that layout (no official masking rule; 13 > 8): `***.****.**09.62`.

## Passport and identity card number (`Document\CH\IdentityDocumentNumber`)

1. **SR 143.111, Art. 16 Ausweisnummer**: "Jedem Ausweis wird durch die Ausfertigungsstelle eine einmalige
   Ausweisnummer zugewiesen, die zufällig generiert wird."
2. **fedpol, "Pass" page, box "Ausweisnummer"** (verbatim): "Die Buchstaben O (Oscar) und I (India) werden bei der
   Nummer der Identitätskarte oder des Schweizer Passes nicht verwendet. Die Ziffer 0 (Null) enthält in der Mitte einen
   Punkt."
3. **Kanton Basel-Stadt, Passamt, FAQ neuer Schweizer Pass** (verbatim): "Die Ausweisnummer befindet sich im oberen
   rechten Feld der Personalienseite sowie in den 8 ersten Zeichen der zweiten maschinenlesbaren Zeile."
4. **fedpol identity card flyer (2023)**, specimen: number `S1A00A00` (top right, "Ausweisnummer (erhaben)"); surname
   `Schweizer Sample`, given name `Helvetia`.
5. fedpol FAQ new passport (2022) and PRADO CHE-AO-04001 name the "Passnummer" / "document number" field without a
   layout.

Implemented: 8 characters from `A-H J-N P-Z 0-9` (no `I`, no `O`) after the separators are removed and letters
upper-cased. No position rule is published (the specimen mixes letters and digits), so none is checked. **No check
digit** (the MRZ check digit is a separate ICAO field). One class for the passport and the identity card, which share
the number rule of (2). The Notpass (emergency passport) and older models have no separately published layout; their
numbers are accepted when they fit the rule. No display form; `masked()` keeps the last three characters (8 characters,
package rule): `*****A00`.

## Driving licence number (`Document\CH\DriverLicenceNumber`)

1. **ASTRA Weisungen of 15 April 2023, Ziffer 5 (Vorderseite), "Nummer des Führerausweises"** (verbatim): "Bei der
   Aufnahme eines Ausweises wird diese Nummer automatisch vom System vergeben. Die Nummer des Führerausweises besteht
   aus der 9-stelligen FABER-PIN und einer fortlaufenden 3-stelligen Ausweisnummer." (IVZ attribute `PINID + AWNUM`.)
2. **Anhang 1 Nr. 13** (FAK from 15 April 2023, QR code): "[…] die FABER-PIN. Diese Nummer besteht aus neun Zahlen
   plus drei fortlaufenden Nummern und ist eine eigenständige Identifikationsnummer zu Verwaltungszwecken."
3. **Anhang 1 specimen** (FAK 2023): field 5 `123456789004`.
4. **Anhang 2** (FAK issued 1 April 2003 to 14 April 2023, still valid): field 5 "Nummer des Führerausweises", no
   layout in the text; its specimen prints `000123456789003` (15 digits).
5. The number is assigned by the federal system IVZ (Informationssystem Verkehrszulassung), not per canton; the canton
   appears only in field 4c (`BE-CH`). So there is one class, no `SubdivisionResolver`.

Implemented: 12 digits (FAK 2023, (1)) or 15 digits (FAK 2003, specimen (4); the instructions publish no layout for
it, and a still-valid licence is never refused). **No check digit** is published (format only). For 12 digits
`faberPin()` gives digits 1-9 and `sequenceNumber()` digits 10-12, as (1) defines; for 15 digits both are null (no
published layout). No display form (the specimens print the digits unspaced); `masked()` keeps the last four digits:
`********9004`.

## Masking rule

None published for any of the three numbers; the package rule applies (8 characters → keep 3, longer → keep 4).
