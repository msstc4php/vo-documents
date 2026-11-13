# Germany — sources

All dates UTC. Sources read 2026-11-13. Federal acts are quoted from the official consolidated text of the Federal
Ministry of Justice, <https://www.gesetze-im-internet.de> (read in full; it answers from the build environment). The
amendment status given is the one the portal printed on that day.

| Act | Citation (portal "Vollzitat") | Portal |
|---|---|---|
| PAuswG | Personalausweisgesetz vom 18. Juni 2009 (BGBl. I S. 1346), last amended by Art. 3 G v. 16.7.2026 (BGBl. 2026 I Nr. 213) | <https://www.gesetze-im-internet.de/pauswg/> |
| PAuswV | Personalausweisverordnung vom 1. November 2010 (BGBl. I S. 1460), last amended by Art. 3 V v. 30.1.2026 (BGBl. 2026 I Nr. 31) | <https://www.gesetze-im-internet.de/pauswv/> |
| PassG | Passgesetz i. d. F. d. Bek. vom 30. Oktober 2023 (BGBl. 2023 I Nr. 291), last amended by Art. 12 Abs. 13 G v. 22.10.2026 (BGBl. 2026 I Nr. 318) | <https://www.gesetze-im-internet.de/pa_g_1986/> |
| PassV | Passverordnung vom 19. Oktober 2007 (BGBl. I S. 2386), last amended by Art. 1 V v. 30.1.2026 (BGBl. 2026 I Nr. 31) | <https://www.gesetze-im-internet.de/passv_2007/> |
| AO | Abgabenordnung, § 139b | <https://www.gesetze-im-internet.de/ao_1977/__139b.html> |
| StIdV | Steueridentifikationsnummerverordnung vom 28. November 2006 (BGBl. I S. 2726), last amended by Art. 10 V v. 19.12.2025 (BGBl. 2025 I Nr. 372) | <https://www.gesetze-im-internet.de/stidv/> |
| VKVV | Versicherungsnummern-, Kontoführungs- und Versicherungsverlaufsverordnung vom 30. März 2001 (BGBl. I S. 475), last amended by Art. 22 G v. 12.6.2020 (BGBl. I S. 1248) | <https://www.gesetze-im-internet.de/vkvv/> |
| FeV | Fahrerlaubnis-Verordnung vom 13. Dezember 2010 (BGBl. I S. 1980), last amended by Art. 3 V v. 12.8.2026 (BGBl. 2026 I Nr. 236) | <https://www.gesetze-im-internet.de/fev_2010/> |

**Reachability.** Also read: `www.bzst.de` (IdNr pages), `www.deutsche-rentenversicherung.de` (glossary),
`www.aok.de` (FAQ), `dserver.bundestag.de` (Bundesrat prints 300/19 and 301/19), `lima.diplo.de` (KBA authority-key
list). Not usable: the PDF of BR-Drs. 301/19 (PassVwV) extracts as scrambled glyphs (subset font without a Unicode
map), so its text could not be searched.

## Names (`Name\DE\*`)

1. **PAuswV Anlage 3 Abschnitt 1, Vorbemerkung Nr. 3** (identity card) and **PassV Anlage 11, Vorbemerkung Nr. 3**
   (passport), the same words:

   > Als Zeichensatz ist der in der jeweils gültigen Fassung der Technischen Richtlinie des Bundesamtes für Sicherheit
   > in der Informationstechnik, BSI: Technische Richtlinie TR-03123, XML-Datenaustauschformat für hoheitliche
   > Dokumente (TR XhD), veröffentlichte Zeichensatz „String.Latin“ zu verwenden.

2. **PAuswV Anlage 3 Abschnitt 1 Nr. 5** / **PassV Anlage 11 Nr. 5**:

   > In den Datenfeldern „Name“ (Familienname und Geburtsname) sowie „Vornamen“ sind alle Namensbestandteile komplett
   > darzustellen, soweit dies technisch entsprechend der nachstehenden Tabelle umsetzbar ist.

3. **PassG § 4 Abs. 2** lists the content of the machine-readable zone (family name, given names, …); the zone follows
   ICAO Doc 9303 (Regulation (EC) No 2252/2004, PassG § 4 Abs. 3). No German act, regulation or the PassVwV text that
   could be read lets a passport carry a Latin spelling other than the ICAO transliteration: the visual zone prints the
   name in String.Latin (`MÜLLER`), the MRZ the ICAO form (table 6.A, `ICAO.md`).

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` | `\p{Latin}` | hyphen, apostrophe, space | `Müller-Lüdenscheidt` (hyphenated double name), `von der Leyen`, `D'Angelo` |
| `GivenName` | `\p{Latin}` | hyphen, apostrophe, space | several given names (`Anna Maria`), `Hans-Peter` |

- String.Latin (TR-03123, now DIN 91379) is a list of Latin letters with diacritics; `\p{Latin}` (the shared
  `AbstractLatinNamePart`) is a superset of its letters. The package does not copy the String.Latin list (designer's
  call; the ICAO transliteration refuses a letter without a spelling anyway).
- German letters: `ä ö ü ß` and the capital `ẞ` (U+1E9E). Table 6.A: `Ä` → `AE` or `A`, `Ö` → `OE` or `O`,
  `Ü` → `UE`, `UXX` or `U`, `ẞ` → `SS`; lower-case `ß` is upper-cased to `SS` before the lookup (`ICAO.md`).
- **Derived Latin:** `FAMILY<<GIVEN` with the first (default) spelling: `Müller` → `MUELLER`, `Groß` → `GROSS`,
  `von der Leyen` → `VON<DER<LEYEN`.
- **Override policy `Name\TransliterationVariant`** (the plan's default; no German rule allows another spelling, see 3):
  an override must be one of the table's spellings of both parts (`MULLER<<JURGEN` for `Müller Jürgen`); anything else
  (`MILLER<<JURGEN`) is refused. An override equal to the derived form is not kept.
- **Display order** (`full()`): given names, then family name (`Erika Mustermann`), the order of address. The
  documents print the family name in its own field first; `latin()` keeps that order.
- **Not covered:** the birth name (Geburtsname), the doctorate (Doktorgrad, PAuswV Anlage 3 Nr. 8, not part of the
  name) and the Ordens- or Künstlername; the composite has a family name and given names only.

Payload (`PersonNameCodec`): `{family, given, latin}` with `latin` the override only; the JSON view is
`{family, given, effectiveLatin}` and is refused by the codec, as for AU.

## Personalausweis number (`Document\DE\IdentityCardNumber`)

1. **PAuswG § 2 Abs. 8**:

   > Jeder Ausweis erhält eine neue Seriennummer. Die Seriennummer eines Personalausweises setzt sich aus einer
   > vierstelligen Behördenkennzahl und einer fünfstelligen, zufällig vergebenen Nummer zusammen und kann Ziffern und
   > Buchstaben enthalten. Die Seriennummer des vorläufigen Personalausweises und des Ersatz-Personalausweises besteht
   > aus einem Buchstaben und sieben Ziffern.

2. **PAuswV Anlage 3 Abschnitt 1 Nr. 9**:

   > Die alphanummerische Seriennummer des Personalausweises wird ausschließlich aus den Buchstaben C, F, G, H, J, K,
   > L, M, N, P, R, T, V, W, X, Y, Z und den Ziffern 1, 2, 3, 4, 5, 6, 7, 8, 9 gebildet. Beim vorläufigen
   > Personalausweis und beim Ersatz-Personalausweis besteht die Seriennummer aus einem Buchstaben und sieben Ziffern.

3. **PAuswG § 5 Abs. 8** (as printed): "Die Seriennummer, die Prüfziffern, das Sperrkennwort und Sperrmerkmale dürfen
   keine Daten über die Person des Ausweisinhabers … enthalten." The check digits are separate MRZ fields (ICAO Doc
   9303), not part of the number.

Implemented: 9 characters from `C F G H J K L M N P R T V W X Y Z 1-9` (no vowels, no `0`, no `B D Q S`), after the
separators of `Internal\Digits::SEPARATORS` are removed and ASCII letters upper-cased. **No check digit** (none in the
number). `authorityCode()` gives characters 1-4 (the Behördenkennzahl, PAuswG § 2 Abs. 8); it is not checked against a
list (none is published in the act). Official examples: none in text (the Muster in Anlage 1 is an image); tests use
numbers of the stated alphabet. A number with `0` or `O` is refused ("(invalid character)"). No display form is
published, so there is no `formatted()`; `masked()` keeps the last four characters (9 > 8, package rule):
`*****1T47`.

**Not covered:** the provisional identity card and the Ersatz-Personalausweis (one letter and seven digits, a different
document) and the eID card (PAuswV § 33 ff.).

## Reisepass number (`Document\DE\PassportNumber`)

1. **PassG § 4 Abs. 2 Satz 2 Nr. 5** (machine-readable zone):

   > die Seriennummer des Passes, die sich beim Reisepass, beim Dienstpass und beim Diplomatenpass aus der
   > Behördenkennzahl der Passbehörde und einer zufällig zu vergebenden Passnummer zusammensetzt, die neben Ziffern
   > auch Buchstaben enthalten kann und beim vorläufigen Reisepass, vorläufigen Dienstpass und vorläufigen
   > Diplomatenpass aus einem Serienbuchstaben und sieben Ziffern besteht,

2. **PassV Anlage 11, Tabelle 1** (Reisepass, Dienst- und Diplomatenpass): field "Pass-Nr." — "9 Zeichen pro Zeile;
   1 Zeile (insgesamt 9 Zeichen)".
3. **PassV Anlage 11 Nr. 9**:

   > Die alphanumerische Seriennummer des Reisepasses, Dienstpasses und Diplomatenpasses wird ausschließlich aus den
   > Buchstaben C, F, G, H, J, K, L, M, N, P, R, T, V, W, X, Y, Z und den Ziffern 1, 2, 3, 4, 5, 6, 7, 8, 9 gebildet.

4. **PassG § 16 Abs. 1** (as printed): "Die Seriennummer und die Prüfziffern dürfen keine Daten über
   die Person des Passinhabers … enthalten." The MRZ check digit belongs to the MRZ.

Implemented: 9 characters from the same alphabet as the identity card; the same class covers the Dienstpass and the
Diplomatenpass (same rule). The split between the Behördenkennzahl and the Passnummer is not given for the passport
(no length for the authority code in PassG/PassV), so there is no `authorityCode()`. **No check digit.** No display
form; `masked()` keeps the last four characters: `*****1T47`.

**Not covered:** provisional passports (one letter and seven digits).

## Steuerliche Identifikationsnummer (`Document\DE\TaxIdentificationNumber`)

1. **StIdV § 1**: "Die Identifikationsnummer nach § 139b der Abgabenordnung besteht aus zehn Ziffern und einer
   Prüfziffer als elfter Ziffer."
2. **AO § 139a Abs. 1** (cited by the BZSt document below): the number "besteht aus einer Ziffernfolge, die nicht aus
   anderen Daten über den Steuerpflichtigen gebildet oder abgeleitet werden darf; die letzte Stelle ist eine
   Prüfziffer". No encoded data.
3. **BZSt**, <https://www.bzst.de/DE/Privatpersonen/SteuerlicheIdentifikationsnummer/steuerlicheidentifikationsnummer_node.html>:
   "Die Identifikationsnummer (IdNr) ist eine elfstellige Nummer und enthält keine Informationen über die betreffende
   Person." No algorithm on the BZSt site (also not in its FAQ page).

**Format only; the check digit is not verified because no official algorithm is published.** Secondary sources name
ISO 7064 MOD 11,10; no act or BZSt page says so. The BZSt paper "Erzeugung von Identifikationsnummern — grundlegende
Beschreibung der Struktur und Merkmale" (released under the IFG, hosted by FragDenStaat:
<https://media.frag-den-staat.de/files/foi/607002/erzeugung-von-identifikationsnummern-grundlegende-beschreibung-der-struktur-und-merkmale.pdf>)
states further structure rules (no leading zero; one digit occurs twice or three times in the first ten) but no
check-digit algorithm; it is not a publication of the agency and its OCR text is incomplete, so **those rules are not
applied** (owner's rule: structural rules too). A leading zero is therefore accepted.

Implemented: 11 digits. No display form is published; `masked()` keeps the last four digits: `*******8901`.

## Rentenversicherungsnummer (`Document\DE\PensionInsuranceNumber`)

**VKVV § 2** (Aufbau der Versicherungsnummer), verbatim:

> (1) Die Versicherungsnummer wird gemäß § 147 Abs. 2 des Sechsten Buches Sozialgesetzbuch nach Maßgabe der folgenden
> Absätze gebildet aus a) der Bereichsnummer, b) dem Geburtsdatum, c) dem Anfangsbuchstaben des Geburtsnamens, d) der
> Seriennummer und e) der Prüfziffer.
> (2) Die Bereichsnummer des vergebenden Rentenversicherungsträgers gemäß der Anlage bildet die ersten beiden Stellen.
> (3) Der Geburtstag und der Geburtsmonat - jeweils zweistellig - und die beiden letzten Ziffern des Geburtsjahres der
> Versicherten bilden die Stellen drei bis acht. Die Gestaltung dieser Stellen oder der Versicherungsnummer insgesamt
> bei nicht nachgewiesenem Geburtsdatum regeln unter Beachtung des § 33a des Ersten Buches Sozialgesetzbuch die
> Spitzenverbände der Kranken- und Pflegekassen, die Deutsche Rentenversicherung Bund und die Bundesagentur für Arbeit
> einvernehmlich.
> (4) Der Anfangsbuchstabe des Geburtsnamens des Versicherten im Zeitpunkt der Vergabe bildet die neunte Stelle.
> Umlaute am Namensbeginn werden aufgelöst, fremdsprachige Sonderzeichen durch vergleichbare deutsche Buchstaben
> ersetzt, Kleinbuchstaben in Großbuchstaben umgesetzt.
> (5) Die Stellen zehn und elf enthalten die Seriennummer. Sie bezeichnet in aufsteigender Reihenfolge die
> Versicherten, die an demselben Tag geboren sind und deren Geburtsname mit dem gleichen Buchstaben beginnt. Für
> männliche Versicherte werden die Ziffern 00 bis 49, für weibliche Versicherte und Versicherte ohne Angabe zum
> Geschlecht oder mit der Angabe „divers“ die Ziffern 50 bis 99 verwandt. Die Gestaltung der Stellen zehn und elf oder
> der Versicherungsnummer insgesamt beim Verbrauch sämtlicher Seriennummern eines Geburtsdatums regeln die
> Spitzenverbände der Kranken- und Pflegekassen, die Deutsche Rentenversicherung Bund und die Bundesagentur für Arbeit
> einvernehmlich.
> (6) Die zwölfte Stelle, die Prüfziffer, wird errechnet, indem der Buchstabe in der neunten Stelle durch eine
> zweistellige Zahl ersetzt wird, die die Position des Buchstabens im deutschen Alphabet kennzeichnet. Die Ziffern der
> damit zwölfstelligen Nummer werden - an der ersten Stelle beginnend - mit den Faktoren 2, 1, 2, 5, 7, 1, 2, 1, 2, 1,
> 2 und 1 multipliziert. Von den Produkten werden die Quersummen gebildet. Die Quersummen werden addiert. Die Summe wird
> durch 10 dividiert. Der verbleibende Rest ist die Prüfziffer.

The Anlage (zu § 2 Abs. 2) lists the Bereichsnummern (02-29 regional carriers, 38/39 Knappschaft-Bahn-See, 40 Zentrale
Zulagenstelle, DRV Bund = 40 + regional number, 80-82 and 89 Knappschaft-Bahn-See).

**The checksum is verified** (official algorithm, § 2 Abs. 6): the letter becomes its position `A` = `01` … `Z` = `26`
(the "deutsche Alphabet" read as the 26 letters A-Z: § 2 Abs. 4 resolves umlauts, so position 9 is never `Ä Ö Ü`).

Display form and examples:

- **DRV glossary**, <https://www.deutsche-rentenversicherung.de/SharedDocs/Glossareintraege/DE/V/versicherungsnummer.html>:
  "Beispiel für eine Versicherungsnummer: 65 170839 J 08 8" (born 17 August 1939, letter J, serial 08 "männlich").
  This is the display form `formatted()` uses (`NN DDMMYY L SS C`). **Its check digit does not satisfy § 2 Abs. 6**
  (the algorithm gives 1, the page prints 8), so the example is illustrative and is refused with "Invalid German pension
  insurance number checksum." — tested as such.
- **AOK FAQ** (a statutory health insurer), <https://www.aok.de/pk/faq/versicherungsnummern/>: "65 230701 J008",
  "fiktiv", a man born 23 July 2001. Its check digit (8) satisfies § 2 Abs. 6; it is the tests' valid example.

Encoded data, and what the package does with it:

| Positions | Meaning (§ 2) | Exposed as | Why not typed |
|---|---|---|---|
| 1-2 | Bereichsnummer | `areaNumber()` | not checked against the Anlage: a number "wird nur einmal vergeben und nicht berichtigt" (§ 3 Abs. 1), so numbers issued under earlier versions of the Anlage stay valid |
| 3-8 | DDMMYY | `birthDateField()` (the six digits) | **no century** in the source, and for an unproven birth date the positions follow unpublished rules of the Spitzenverbände (§ 2 Abs. 3 Satz 2); so no `HasBirthDate` and **no date validation** (a "30 February" field is accepted: refusing it would apply a rule the source does not state) |
| 9 | initial of the birth name | `birthNameInitial()` | — |
| 10-11 | serial | `serialNumber()` | 50-99 means female **or no entry or "divers"**, and exhausted serials follow unpublished rules (§ 2 Abs. 5 Satz 3): no one-to-one mapping to `Sex`, so no `HasSex` |
| 12 | check digit | verified | — |

Implemented: 12 characters `[0-9]{8}[A-Z][0-9]{3}` after separators are removed and letters upper-cased (input
`65 230701 J 00 8`, `65230701j008`). Messages: length "(N characters)", wrong character class at a position
"(invalid character)", checksum "Invalid German pension insurance number checksum.". `masked()` keeps the last four
characters, laid out like `formatted()`: `** ****** J 00 8` (the initial and the serial are among the last four; no
official masking rule).

## Führerschein number — not covered

**FeV Anlage 8, I. Allgemeiner Führerschein, Nr. 2.1 c) field 5**: "Nummer des Führerscheins, die sich aus dem Behördenschlüssel der
Fahrerlaubnisbehörde, einer von dieser fortlaufend zu vergebenden Fahrerlaubnisnummer sowie einer Prüfziffer und der
Nummer der Ausfertigung des Dokuments zusammensetzt." FeV § 49 Abs. 1 Nr. 9 and 10 repeat the composition (Fahrerlaubnisnummer
= Behördenschlüssel + fortlaufende Nummer + Prüfnummer; Führerscheinnummer = Fahrerlaubnisnummer + fortlaufende Nummer
des Führerscheins). The KBA list of authority keys (copy on `lima.diplo.de`) gives the key's 4 characters ("Der
vorangestellte Buchstabe ist ein Landeskennzeichen … Die Stellen 2 und 3 sind fortlaufende Ziffern …").

**No official text gives the length or alphabet of the serial and of the Ausfertigung number, nor the check-digit
algorithm.** The 11-character layout and the modulus-11 check found on dewiki.de and similar sites are secondary
sources. Without a published total format there is nothing to validate: not covered.

## Where this note departs from the plan (the source wins)

| Plan candidate | Source | Implemented |
|---|---|---|
| Personalausweis number | PAuswG § 2 Abs. 8, PAuswV Anlage 3 Nr. 9 | `IdentityCardNumber`, alphabet rule, no check digit |
| Reisepass number | PassG § 4 Abs. 2 Nr. 5, PassV Anlage 11 | `PassportNumber`, alphabet rule, no check digit |
| Steuer-ID | StIdV § 1; no published algorithm | `TaxIdentificationNumber`, format only |
| Rentenversicherungsnummer (birth date, sex) | VKVV § 2 | `PensionInsuranceNumber`, checksum verified; birth date and sex **not typed** (no century; 50-99 is not "female") |
| Führerschein number | FeV Anlage 8: composition only | not covered |
