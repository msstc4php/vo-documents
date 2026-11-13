# Netherlands — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| Logisch Ontwerp BSN, versie 2025.Q4 (11 november 2025), § 3.2.1, § I.2.3 (footnote 22), § II.2.3, § IV.1 | RvIG | <https://rvig.nl/sites/default/files/2025-11/Logisch%20Ontwerp%20BSN%202025.Q4.pdf> |
| Regeling invoering BSN en voorzieningen digitale overheid BES, 3 december 2025, Stcrt. 2025, 41570, art. I onderdeel C (Regeling burgerservicenummer, art. 3 → LO BSN 2025.Q4) | Staatscourant | <https://zoek.officielebekendmakingen.nl/stcrt-2025-41570.html> |
| Logisch Ontwerp BSN, versie 2024.Q1 (same elfproef text; superseded) | RvIG | <https://www.rvig.nl/sites/default/files/2023-12/Logisch%20Ontwerp%20BSN%202024.Q1.pdf> |
| "Aansluiting testen en toetsen" BV BSN, versie 1.6 (13-12-2019), Bijlage 1 (test set) | RvIG | <https://rvig.nl/sites/default/files/2025-05/Aansluiting%20testen%20en%20toetsen%20BSN.pdf> |
| Logisch Ontwerp BRP, versie 2025.Q2 (1 april 2025), § 5.1.2 (Tekenset, tables 5.1-5.3), elements 01.20, 02.10-02.40, table 36 | RvIG | <https://rvig.nl/sites/default/files/2025-05/Logisch%20Ontwerp%20BRP%202025.Q2.pdf> |
| Landelijke tabel 36 — Voorvoegseltabel (GBA tabel, 01-12-2004) | RvIG | <https://publicaties.rvig.nl/media/13287/download> |
| Kenmerken paspoorten en identiteitskaarten model 2014 (NL), kenmerk 5 "Documentnummer" | RvIG | <https://www.rvig.nl/sites/default/files/2023-02/Kenmerkenbrochure%20paspoorten%20en%20identiteitskaarten%20model%202014%20NL.pdf> |
| Kenmerken paspoort model 2021, kenmerk "Documentnummer" | RvIG | <https://www.rvig.nl/sites/default/files/2023-02/Kenmerkenbrochure%20paspoort%20model%202021.pdf> |
| Kenmerken Nederlandse paspoorten 2024 (September 2024), p. 5 and kenmerk 5 | RvIG | <https://rvig.nl/sites/default/files/2024-09/Kenmerkenbrochure%20paspoort%20model%202024.pdf> |
| Kenmerken Nederlandse identiteitskaart 2024, kenmerk 5 | RvIG | <https://rvig.nl/sites/default/files/2024-09/Kenmerkenbrochure%20identiteitskaart%20model%202024.pdf> |
| "Bevat mijn paspoortnummer het cijfer 0 of de letter O?" | Rijksoverheid | <https://www.rijksoverheid.nl/themas/migratie-en-reizen/paspoort-en-identiteitskaart/vraag-en-antwoord/paspoort-en-identiteitskaart/bevat-mijn-paspoortnummer-het-cijfer-0-of-de-letter-o> |
| Paspoortuitvoeringsregeling Nederland 2001 (BWBR0012811), art. 23, version in force from 01-10-2026 | wetten.overheid.nl | <https://wetten.overheid.nl/BWBR0012811/> (through WebFetch) |
| Paspoortwet (BWBR0005212), art. 2 and 3, version in force from 01-10-2026 | wetten.overheid.nl | <https://wetten.overheid.nl/BWBR0005212/> (through WebFetch) |
| "Echtheidskenmerken rijbewijs", "Rijbewijscontrole", "Rijbewijsmodel 2025" | RDW | <https://www.rdw.nl/het-rijbewijs/over-het-rijbewijs/echtheidskenmerken-rijbewijs> and siblings |

Reachability: `rvig.nl`, `www.rvig.nl`, `publicaties.rvig.nl`, `www.rijksoverheid.nl` and the HTML pages of
`zoek.officielebekendmakingen.nl` answer to curl. `wetten.overheid.nl` fails from curl (HTTP/2 PROTOCOL_ERROR, and a
TLS "bad record mac" over HTTP/1.1) and was read through the WebFetch tool. Some `zoek.officielebekendmakingen.nl` PDFs
answered HTTP 504 (Stcrt. 2012, 12298 and 2025, 18098, the driving licence model regulations); their HTML versions
carry the licence models as images only. `www.rdw.nl` was read through WebFetch. The Logisch Ontwerp BRP 2025.Q4 URL
(`2025-11/…BRP 2025.Q4.pdf`) answered 404; version 2025.Q2 is quoted. Its character set is the one the LO BSN
2025.Q4 § IV.1 still refers to ("In het Logisch Ontwerp BRP is een weergave van de Teletex tekenset terug te vinden").

## Names (`Name\NL\*`)

1. **Paspoortwet, art. 3 lid 1**: "Elk reisdocument vermeldt de volgende persoonsgegevens van de houder: geslachtsnaam,
   voornamen, …"
2. **Paspoortuitvoeringsregeling Nederland 2001, art. 23**:

   > 3. De geslachtsnaam omvat tevens de voorvoegsels en adellijke titels, de voornaam omvat tevens de adellijke
   > predicaten. […]
   >
   > 4. Indien alleen een naam, voornaam of een roepnaam bekend is, wordt deze als geslachtsnaam beschouwd.

3. **LO BRP 2025.Q2, element 02.30 "Voorvoegsel geslachtsnaam"**: "Dat deel van de geslachtsnaam dat voorkomt in
   Tabel 36, Voorvoegseltabel en, gescheiden door een spatie, voorafgaat aan de rest van de geslachtsnaam." Element
   02.10 "Voornamen": "De verzameling namen die, gescheiden door spaties, aan de geslachtsnaam voorafgaat."
4. **Table 36 (Voorvoegseltabel)** lists each prefix in both cases (`Van der` / `van der`) and prefixes with an
   apostrophe: `'s`, `'t`, `Aan 't`, `Aus 'm`, `D'`, `d'`, `Dal'`, `De l'`, `L'`, `S'`, `van 't`, `Van de l'`, ….
5. **LO BRP 2025.Q2, § 5.1.2.1 Tekenset** (verbatim):

   > In paragraaf 5.1.2.3 wordt een overzicht gegeven van die tekens uit de Teletex-tekenset die binnen het
   > BRP-systeem gebruikt mogen worden. Niet opgenomen tekens mogen niet gebruikt worden. Dat betreft onder andere de
   > Hollandse IJ (codes E6 en F6). […] In paragraaf 5.1.2.4 staat een overzicht van alle combinaties van letters en
   > diakrieten die binnen het BRP-systeem gebruikt mogen worden. Niet opgenomen combinaties mogen niet gebruikt
   > worden.

   Table 5.2 letters besides `A-Z a-z`: `Æ Đ Ħ Ŀ Ł Ø Œ Þ Ŧ Ŋ ŉ ĸ æ đ ð ħ ı ŀ ł ø œ ß þ ŧ ŋ` (also `ª º`, signs and
   digits). Table 5.3 (letter + diacritic): `À Á Â Ã Ā Ă Ä Å Ą`, `Ć Ĉ Ċ Ç Č`, `Ď`, `È É Ê Ē Ė Ë Ę Ě`, `Ĝ Ğ Ġ Ģ`
   (small `ģ`), `Ĥ`, `Ì Í Î Ĩ Ī İ Ï Į` (no small dotted i), `Ĵ`, `Ķ`, `Ĺ Ļ Ľ`, `Ń Ñ Ņ Ň`, `Ò Ó Ô Õ Ō Ö Ő`,
   `Ŕ Ŗ Ř`, `Ś Ŝ Ş Š`, `Ţ Ť`, `Ù Ú Û Ũ Ū Ŭ Ü Ů Ű Ų`, `Ŵ`, `Ý Ŷ Ÿ`, `Ź Ż Ž` and their small letters as listed.
6. **RvIG passport specimen (model 2024, p. 5)**: surname field `de Bruijn`, given names `Willeke Liselotte`, MRZ
   `PPNLDDE<BRUIJN<<WILLEKE<LISELOTTE<…`: the prefix is printed in lower case and is part of the primary identifier;
   `ij` is two letters.
7. No Dutch act or regulation read (Paspoortwet, Paspoortuitvoeringsregeling) lets a passport keep a Latin spelling
   other than the ICAO transliteration; the regulation has no rule on characters or transliteration at all.

How the package applies this:

| Part | Letters | Separators inside | Why |
|---|---|---|---|
| `FamilyName` | table 5.2 + 5.3 letters | hyphen, apostrophe, space; a word may start with `'` (at the start or after a space) and end with `'` before a space, never both around one space (`x' 'y`: no table 36 prefix has that shape) | prefixes are part of the geslachtsnaam (art. 23 lid 3): `van der Berg`, `'t Hart`, `van 't Hof`, `d' Ancona`, `d'Ancona`, `Jansen-de Vries` |
| `GivenName` | the same | the same | several given names (`Willeke Liselotte`), `Hendrik-Jan` (LO BRP example); the apostrophe is a BRP character (table 5.2, code 27) and the shared word rule also accepts it leading a given name (`'Anna`) |

- **Prefixes (plan decision, binding):** no separate class; the prefix is stored inside the family name as typed, so
  `van der Berg` and `Van der Berg` are different values (table 36 lists both) with the same Latin form. The codec
  stores the value unchanged, lower-case prefixes included.
- **`ĳ` / `Ĳ` are refused** ("(invalid character)"): the BRP forbids the Hollandse IJ, names are written `ij`. This
  overrides the plan's hint that the ligature is accepted. `ŉ` (table 5.2) is a compatibility character
  (`ʼn`) and is refused as such; `ª º` are not letters of a name and are not accepted. Letter case follows the tables:
  `İ`, `ı` and `ð` are accepted, a small dotted `i` is plain `i`; `Ð` (capital eth) and `Ș`/`Ț` (comma below) are
  refused.
- `ĸ` is accepted by the parts but has no ICAO spelling (table 6.A), so `PersonName` refuses a name with it.
- **Given names optional:** a person with one name has it as the family name (art. 23 lid 4): `PersonName($family,
  null)`, Latin form `SOEKARNO` (a mononym, no `<<`).
- **Derived Latin:** `FAMILY<<GIVEN` with the table 6.A default: `de Bruijn, Willeke Liselotte` →
  `DE<BRUIJN<<WILLEKE<LISELOTTE` (the specimen's MRZ), `'t Hart` → `T<HART` (apostrophe omitted, Part 3 §4.6),
  `Hoëvell` → `HOEVELL`.
- **Override policy `Name\TransliterationVariant`** (7): an override must be another table 6.A spelling of the parts
  (`MULLER<<ANNA` for `Müller Anna`); `DE<BRUYN<<WILLEKE` is refused. An override equal to the derived form is not
  kept.
- **Not covered:** adellijke titels and predicaten (table 38; art. 23 lid 3 counts them in the name, the package keeps
  family and given names only), the BRP placeholder `.` for an unknown family name.

Payload (`PersonNameCodec`): `{family, given, latin}` with `given` null for one name and `latin` the override only; the
JSON view is `{family, given, effectiveLatin}` and is refused by the codec.

## BSN (`Document\NL\Bsn`)

1. **LO BSN 2025.Q4, § 3.2.1** (generated numbers): "ieder gegenereerd nummer moet 9-cijferig zijn en aan de elfproef
   voldoen; ieder gegenereerd nummer moet informatieloos zijn; er mag geen volgordelijkheid uit de nummers blijken; het
   nummer moet binnen het ingestelde interval liggen."
2. **LO BSN 2025.Q4, § I.2.3**, element "Burgerservicenummer": "Het nummer moet aan de elfproef voldoen", "Lengte
   8..9", "Type Numeriek"; **footnote 22** (verbatim):

   > Elfproef: Indien het nummer per cijfer wordt aangeduid (s0 s1 s2 s3 s4 s5 s6 s7 s8), dan is de volgende
   > voorwaarde te controleren: (9\*s0) + (8\*s1) + (7\*s2) + (6\*s3) + (5\*s4) + (4\*s5) + (3\*s6) + (2\*s7) + (-1\*s8)
   > is deelbaar door 11. Indien het nummer 8-cijferig is, dan moet in de elfproef een voorloopnul toegevoegd worden.

3. The LO BSN is the annex of the **Regeling burgerservicenummer, art. 3**; Stcrt. 2025, 41570, art. I C: "In artikel
   3 wordt 'Logisch ontwerp BSN, versie 2024.Q1' vervangen door 'Logisch Ontwerp BSN, versie 2025.Q4'." The annex
   "wordt bekendgemaakt op https://www.rvig.nl". The LO BRP 2025.Q2 element 01.20 states the same test ("Lengte 9").
4. **Official examples** (RvIG test set, "Aansluiting testen en toetsen", Bijlage 1): test A.1 `123456789` → "Nummer
   is geen BSN"; test A.2 `999990068` → "Nummer is een BSN"; test persons `999990019`, `999990160`, `999994396`,
   `999994931`. All `9999…` numbers pass the elfproef; `123456789` fails it (sum mod 11 = 4).

Implemented: 8 or 9 digits after the separators of `Internal\Digits::SEPARATORS` (dots are not separators); an
8-digit number gets its leading zero and is stored with 9 digits. **The elfproef is verified** (weights 9..2 and −1,
`Checksum::weightedMod(…, 11) === 0`; a negative sum is not divisible). The "ingestelde interval" is not published, so
no range is checked: `000000000` passes the elfproef and is accepted (owner's rule). No encoded data
("informatieloos"); no published display form, so no `formatted()`; `masked()` keeps the last four digits (9 > 8,
package rule): `*****0068`.

## Passport and identity card number (`Document\NL\IdentityDocumentNumber`)

1. **RvIG, model 2014 brochure, kenmerk 5 "Documentnummer"** (passports, Noodpaspoort, Laissez-Passer and the Dutch
   identity card), verbatim:

   > Opbouw nummer: positie 1 en 2: letters, positie 3 t/m 8: letters of cijfers en positie 9: een cijfer. De letter
   > "O" komt in de documentnummers niet voor. Het cijfer "0" (nul) kan wel voorkomen.

2. **Model 2021 and 2024 brochures** (passport and identity card): the same layout, "De letter 'O' en het cijfer '0'
   komen in het documentnummer niet voor."
3. **Rijksoverheid FAQ**: "De letter 'O' komt in het Nederlandse paspoortnummer niet voor. Het gaat dus altijd om het
   cijfer '0'. Dit geldt ook voor het nummer op uw identiteitskaart."
4. **Specimen** (model 2024 brochure, p. 5, image): document number `SPECI2024`, MRZ line 2
   `SPECI20245NLD8003100F3407172<…` (the `5` after the number is the MRZ check digit, ICAO Doc 9303, not part of the
   number).

Implemented: 9 characters `A-N P-Z 0-9` after the separators are removed and letters upper-cased; positions 1-2 a
letter, position 9 a digit (otherwise "(invalid character)"). The digit `0` is accepted: model 2014 documents (issued
from 9 March 2014, valid up to ten years) may hold it, and a still-valid number is never refused. Older models are no
longer valid. **No check digit** (none in the number; the MRZ check digit is separate). One class for every document
the brochures name (passports incl. Noodpaspoort and Laissez-Passer, and the identity card), because they share one
number layout. No display form; `masked()` keeps the last four characters: `*****2024`.

## Rijbewijsnummer — not covered

The RDW pages say only where the number is ("op de voorkant van het rijbewijs onder punt 5", laser-engraved, "voelbaar")
and give no length, character set or check rule. The model regulations (Regeling vaststelling modellen rijbewijzen,
Stcrt. 2012, 12298 and 2025, 18098) publish the models as images (PDFs answered 504). The hinted "10 digits" could not
be quoted from an official source, so there is no class.

## Masking rule

None published for BSN or document numbers; the package rule applies (9 characters → keep the last 4).
