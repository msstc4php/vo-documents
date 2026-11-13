# France — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| INSEE definition "Numéro d'inscription au répertoire" (updated 21/05/2019) | INSEE | <https://www.insee.fr/fr/metadonnees/definition/c1409> |
| "Le Répertoire national d'identification des personnes physiques (RNIPP) au cœur de la vie administrative française", Courrier des statistiques, encadré 1 (updated 29/11/2022) | INSEE | <https://www.insee.fr/fr/information/6665188> |
| "Que signifie le numéro de Sécurité sociale ?" (F33078, checked 14/09/2026) | DILA, service-public.gouv.fr | <https://www.service-public.gouv.fr/particuliers/vosdroits/F33078> |
| "Les règles d'identification et d'immatriculation des assurés par les organismes sociaux" (2015 leaflet, linked from F33078) | Ministère des affaires sociales et de la santé / Ministère de l'économie et des finances | <https://caf.fr/sites/default/files/medias/cnaf/DCOM/mentions_legales/informatique_libertes/Regles-authentification-immatriculation.pdf> (scanned; read as images) |
| DSN cahier technique CT2026.1, rubrique S21.G00.30.001 (pages 169-170) | GIP-MDS (net-entreprises.fr), the normative DSN specification | <https://www.net-entreprises.fr/media/documentation/dsn-cahier-technique-2026.1.pdf> |
| "Dès l'embauche, je m'identifie" (Cnav leaflet, 09/2017) | Cnav / net-entreprises.fr | <https://www.net-entreprises.fr/media/2018/11/n4ds-identification-embauche.pdf> |
| Instruction générale relative à la carte nationale d'identité, NOR INTD0000001C (2000), rubriques 46 and 55 | Ministère de l'intérieur | quoted from the mirror <https://www.gisti.org/IMG/pdf/norintd0000001c.pdf> (not an official host) |
| Answers of "France Titres-ANTS" on Services Publics+ (passport and driving licence numbers) | ANTS (France Titres), on the government platform plus.transformation.gouv.fr | links in the sections below |
| France — Information on Tax Identification Numbers | French tax administration, published by the OECD (AEOI portal) | <https://www.oecd.org/content/dam/oecd/en/topics/policy-issue-focus/aeoi/france-tin.pdf> |
| Circulaire du 23 juillet 2014 relative à l'état civil, NOR JUSC1412888C, BOMJ n° 2014-07 du 31 juillet 2014, § 1 | Ministère de la justice | <https://www.legifrance.gouv.fr/circulaire/id/38565> (PDF `cir_38565`) |

Reachability notes (sites that block scripted requests) are in `.claude/docs/known-issues.md`; Légifrance texts
are cited by URL and NOR, copies on other hosts are marked as mirrors.

## Names (`Name\FR\*`)

**Circulaire du 23 juillet 2014 (JUSC1412888C), § 1**, verbatim:

> Se fondant sur ces principes, l'instruction générale relative à l'état civil (IGREC) (§ 106) rappelle que seul
> l'alphabet romain peut être utilisé et que les seuls signes diacritiques admis sont les points, tréma, accents et
> cédilles tels qu'ils sont souscrits ou suscrits aux voyelles et consonne autorisés par la langue française.
> […]
> Dès lors les voyelles et consonne accompagnées d'un signe diacritique connues de la langue française sont : à-
> â - ä- é - è - ê - ë - ï - î - ô -ö - ù - û - ü- ÿ-ç.
> Ces signes diacritiques peuvent être portés tant sur les lettres majuscules que sur les minuscules.
> Les ligatures « æ » (ou « Æ ») et « œ » (ou « Œ »), équivalents de « ae » (ou « AE ») et « oe » (ou OE) sont
> admises par la langue française.
> Tout autre signe diacritique attaché à une lettre ou ligature ne peut être retenu pour l'établissement d'un acte de
> l'état civil.
> […]
> Ces mêmes règles s'appliquent pour le nom de famille ainsi que toutes autres indications contenues dans les
> actes de l'état civil. Ainsi, par exemple, l'adresse d'un domicile à l'étranger ou le nom d'une personne même de
> nationalité étrangère doivent être indiqués avec les voyelles et consonnes connues de la langue française sans
> reproduire les éventuels signes diacritiques de la langue étrangère […].

The identity card and the passport are issued from the civil-status record, so the package takes the circular's list
as the letters of a French name part:

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` | `A-Z a-z`, `À Â Ä Ç È É Ê Ë Î Ï Ô Ö Ù Û Ü Ÿ` and lower case, `Æ æ Œ œ` | hyphen, apostrophe, space |
| `GivenName` | the same | hyphen, apostrophe, space |

- Refused: every other diacritic (`Fañch`, `Łukasz`, `Müßig` — `ß` is not in the list), other scripts, digits.
- **Separators.** The circular does not list separators. Hyphenated given names (`Jean-Pierre`), particles
  (`de La Fontaine`) and elided particles (`D'Artagnan`) are written with a hyphen, a space and an apostrophe; ANTS
  states that the double hyphen of the 2002-2011 double family names is written as a space (answer below, citing the
  circulaire du 28 octobre 2011), so `--` is refused (two separators in a row), as everywhere in the package.
  ANTS, <https://www.plus.transformation.gouv.fr/experiences/5124278_les-identifiants-papier-non-reconnus>: "Un double
  tiret ne peut pas être remplacé par un tiret simple selon les règles de l'état civil (Circulaire du 28 octobre 2011
  …). Étant donné que le Fichier Nationale des Permis de Conduire n'accepte pas les 2 tirets, la règle de l'état civil
  prévoit de laisser un espace."
- **Derived Latin:** `FAMILY<<GIVEN` through ICAO Doc 9303 Part 3 table 6.A (`ICAO.md`): `É` → `E`, `Ç` → `C`,
  `Ÿ` → `Y`, `Æ` → `AE`, `Œ` → `OE`, `Ä`/`Ö`/`Ü` → `AE`/`OE`/`UE` by default (`A`/`O`/`U`, `UXX` are the table's other
  spellings). `Lætitia Cœur` → `COEUR<<LAETITIA`; the apostrophe is omitted (`D'Artagnan` → `DARTAGNAN`).
- **Override policy `Name\TransliterationVariant`.** No French text that could be read lets a passport carry a Latin
  spelling other than the ICAO transliteration of the civil-status name (the identity card and the passport print the
  civil-status name; the MRZ follows Doc 9303, Regulation (EC) No 2252/2004 and Regulation (EU) 2019/1157). An override
  must be one of the table's spellings (`MULLER<<JOEL` for `Müller Joël`).
- **Display order** (`full()`): given names, then family name (`Jean-Pierre de La Fontaine`).
- **Not covered:** the nom d'usage (married or usage name), which is a separate field on the documents.

Payload (`PersonNameCodec`): `{family, given, latin}` with `latin` the override only; the JSON view is
`{family, given, effectiveLatin}` and is refused by the codec, as for AU and DE.

## NIR — numéro d'inscription au répertoire (`Document\FR\Nir`)

1. **INSEE definition c1409**: "Ce numéro d'identification unique de l'individu est formé de 13 chiffres : le sexe
   (1 chiffre), l'année de naissance (2 chiffres), le mois de naissance (2 chiffres) et le lieu de naissance
   (5 chiffres). Les 3 chiffres suivants correspondent à un numéro d'ordre qui permet de distinguer les personnes nées au
   même lieu à la même période ; une clé de contrôle à 2 chiffres complète le NIR."
2. **INSEE, Courrier des statistiques, encadré 1**:

   > Le NIR est composé de 13 chiffres et est unique pour chaque personne : Sexe (1 chiffre) : 1 pour les hommes, 2
   > pour les femmes ; Année de naissance (2 derniers chiffres de l'année) ; Mois de naissance (2 chiffres) ; Lieu de
   > naissance (5 chiffres) ; Numéro d'ordre dans la commune (3 chiffres). S'ajoute une clé de vérification sur 2
   > chiffres. Par exemple Adrien né le 5 décembre 2021 à Coulommiers et enregistré comme 232 e naissance du mois pour
   > cette commune dans le répertoire aura comme numéro NIR : 1 21 12 77 131 232 - 50 (… l'ordre dans le mois peut
   > comprendre des personnes nées à 100 ans d'écart, l'année figurant uniquement sur 2 caractères).

   and: "lorsqu'une personne change de sexe, le numéro NIR est mis à jour avec une modification uniquement du premier
   numéro pour que le chiffre indiqué corresponde bien au sexe actuel de la personne".
3. **service-public.gouv.fr F33078** (table "Composition du numéro de sécurité sociale"): 1er chiffre "Sexe : 1 :
   Homme 2 : Femme"; 5 chiffres "Vous êtes né en France : 2 chiffres du code du département de naissance (ce code est
   celui existant au moment de la naissance), suivis des 3 chiffres du code commune officiel de l'Insee"; "Vous êtes né
   à l'étranger : les 2 chiffres du code du département sont remplacés par 99 et le code commune par un code Insee du
   pays de naissance"; "91, 92, 93 ou 94 pour l'Algérie, 95 pour le Maroc, 96 pour la Tunisie"; "2 derniers chiffres
   Clé de contrôle".
4. **Ministries' leaflet** (linked from F33078): "caractères n°6 à n°10 : le lieu de naissance … (de 01 à 95 et 2A ou
   2B pour la Corse depuis le 1er janvier 1976) … le code du département est 97 ou 98, suivi du code commune"; "Deux
   chiffres constituant une « clé de contrôle » complètent le numéro de sécurité sociale."
5. **DSN cahier technique CT2026.1, S21.G00.30.001** (the norm every employer declaration is checked against):

   > Ce numéro est composé de 13 chiffres et d'une clé de 2 chiffres. […]
   > Le Nir doit avoir la forme SAAMMDDCCCNNN avec :
   > S = sexe de la personne physique doit être égal à 1 ou 2
   > AA = année de naissance de la personne physique doit être comprise entre 00 et 99
   > MM = mois de naissance de la personne physique doit être compris entre 01 et 12 ou entre 30 et 42 ou entre
   > 50 et 99 ou égal à 20
   > DD = département de naissance de la personne physique doit être compris entre 01 et 99 ou être égal à 2A
   > ou 2B
   > CCC = commune de naissance de la personne physique doit être comprise entre 001 et 999
   > NNN = numéro d'inscription la personne physique au registre d'état civil doit être compris entre 001 et 999
   > […] Si l'immatriculation est provisoire, ne pas utiliser les numéros provisoires reconnaissables par leur premier
   > caractère égal à 7 ou 8, mais utiliser le NIA qui a été notifié.

   with the pattern `CSL 00 : [1-2][0-9]{2}(0[1-9]|1[0-2]|20|3[0-9]|4[0-2]|[5-9][0-9])(0[1-9]|[1-9][0-9]|2A|2B)…`.
   (The pattern's `3[0-9]` also admits 30-39, consistent with "entre 30 et 42".) The rules CCH-11 (2A/2B only from
   1976, 20 only before 1976, 96 only before 1968) need the century, which the NIR does not hold; CCH-13/16 are
   declaration rules (placeholder numbers), not rules of the number.
6. **Cnav leaflet** (2017): "les mois de naissance peuvent être supérieurs à 12 (20, 30…) pour les assurés dont le
   mois de naissance n'est pas connu".

**The key: format only; the check digit is not verified because no official algorithm is published.** INSEE,
service-public, the ministries' leaflet and the DSN norm all say "clé de contrôle (de vérification) de 2 chiffres" and
give no computation. The formula `97 − (13 digits mod 97)`, with `2A` → `19` and `2B` → `18` for Corsica, is found
only on secondary sites (Wikipedia, calculators); no reachable official page (INSEE, ameli, service-public, ANS, the
DSN norm, SESAM-Vitale specifications) states it. The INSEE example `1 21 12 77 131 232 - 50` happens to satisfy it,
but an example is not an algorithm. A NIR with any two-digit key is therefore accepted (owner's rule).

Implemented (15 characters `S AA MM DD CCC NNN KK` after separators are removed and ASCII letters upper-cased):

| Positions | Rule (source 5) | Exposed as |
|---|---|---|
| 1 | `1` or `2` (temporary numbers `7`/`8` refused) | `sex()`: `1` → `Sex::Male`, `2` → `Sex::Female` (`HasSex`; sources 2, 3) |
| 2-3 | `00`-`99` | `birthYearDigits()` — the last two digits; **no century** (source 2), so no `HasBirthDate` |
| 4-5 | `01`-`12`, `20`, `30`-`42`, `50`-`99` | `birthMonth()`: 1-12, or `null` for the other values (month unknown, source 6) |
| 6-7 | `01`-`99`, `2A`, `2B` | part of `birthPlaceCode()` |
| 8-10 | `001`-`999` | part of `birthPlaceCode()` (5 characters, e.g. `77131`, `2A004`, `99100`) |
| 11-13 | `001`-`999` | `orderNumber()` |
| 14-15 | two digits, not verified | `controlKey()` |

The department is not mapped to an ISO 3166-2 subdivision: `99` (abroad), `91`-`96` (former Algeria, Morocco, Tunisia)
and `97`/`98` + commune (overseas) have no one-to-one mapping. Messages: "(invalid character)" for a character outside
`0-9` (outside `0-9`, `A`, `B` at positions 6-7), "(invalid sex digit)", "(invalid birth month)", "(invalid
department)", "(invalid commune)", "(invalid order number)". `formatted()` is the INSEE form `1 21 12 77 131 232 - 50`;
`masked()` keeps the last four characters (15 > 8, package rule): `* ** ** ** *** *32 - 50`.

**Not covered:** the NIA (a waiting number "constitué comme un NIR", accepted as a NIR when it has the shape) and the
temporary numbers starting with 7 or 8.

## Carte nationale d'identité

### Card issued until August 2021 (`Document\FR\LegacyIdentityCardNumber`)

**Instruction générale NOR INTD0000001C, rubrique 46**, verbatim:

> Le numéro de la carte figure après la mention imprimée : « carte nationale d'identité n° : » en haut à gauche. Ce
> numéro qui figure également dans la zone de lecture au début de la seconde ligne, est attribué par le centre de
> gestion par ordre chronologique par rapport au lieu de délivrance et à la date de demande.
> Le numéro de la carte est constitué de douze chiffres : les quatre premiers correspondent à l'année et au mois de
> délivrance, les trois suivants représentent le code géographique du lieu de délivrance (numéro INSEE) et enfin les
> cinq derniers correspondent au numéro d'ordre.

Rubrique 55 b): "Sur la deuxième ligne, les douze premiers caractères sont ceux du numéro de la carte d'identité. Ce
numéro est suivi d'un chiffre particulier qui permettra, lors de la lecture automatique, d'interpréter sans erreur le
numéro de la carte." That check digit belongs to the MRZ, not to the number.

Implemented: 12 digits. Positions 3-4 are the month of issue, but the instruction states no rule for the field's
values, so they are **not validated** (owner's rule: no structural rule without an official text; `00` and `13` are
accepted). Exposed: `issueYearDigits()` (2 digits, no century), `issueMonth()` (the two digits as an int, not
validated), `issuePlaceCode()` (3 characters),
`serialNumber()` (5 digits). No display form is published; `masked()` keeps the last four digits: `********2345`.
The provisional card (rubrique 57: 11 characters with the letter `P`) is not covered.

**Concern:** the instruction says "douze chiffres", and a Corsican place code (`2A`/`2B`) would hold a letter; the
source does not address it, so a letter is refused.

### Card issued since August 2021 — not covered

Décret n° 2021-279 du 13 mars 2021 (JORF 14 mars 2021, read from the copy on `medias.amf.asso.fr`) lists "le numéro du
titre" among the card's data but gives no format. The nine-character alphanumeric number is described only on
secondary sites; PRADO (Council of the EU) could not be read and ICAO Doc 9303 Part 5 gives the width of the MRZ field
(9 characters, longer numbers overflow), not the French number. No official format: not covered.

## Passport number (`Document\FR\PassportNumber`)

**France Titres-ANTS**, answer on Services Publics+,
<https://www.plus.transformation.gouv.fr/experiences/4124633_passeport>:

> Le numéro de l'ancien passeport à renseigner se compose de 9 caractères - (2 chiffres, 2 lettres, 5 chiffres).

Implemented: `[0-9]{2}[A-Z]{2}[0-9]{5}` after separators are removed and letters upper-cased. **No check digit** (the
MRZ check digit belongs to the MRZ). No display form; `masked()` keeps the last four characters: `*****2345`. No
official example in text; the tests use numbers of the stated shape.

## Numéro fiscal / SPI (`Document\FR\TaxIdentificationNumber`)

**France, Information on Tax Identification Numbers** (submitted by the French tax administration, OECD AEOI
portal), Section II:

> The TIN consists in 13 numerals (format 99 99 999 999 999). The first digit of the TIN is always 0, 1, 2 or 3.
> No spaces are expected in filling in the TIN.

Section I: "The French name of the TIN is known as the numéro fiscal de référence or numéro SPI." No check digit or
encoded data is described: **format only** (secondary sites name a "mod 511" key on the last three digits; no official
text does). Implemented: 13 digits, first digit `0`-`3` ("(invalid first digit)"); `formatted()` follows the published
format `99 99 999 999 999` (`30 23 217 600 053`; no official example number, the tests use synthetic ones); `masked()` keeps the last four digits: `** ** *** **0 053`. The New Caledonia and
Saint-Martin TINs are not covered.

## Driving licence number (`Document\FR\DriverLicenceNumber`)

**France Titres-ANTS**, answers on Services Publics+:

- <https://www.plus.transformation.gouv.fr/experiences/4598427_permis-de-conduire>: "Le numéro de dossier est également
  appelé numéro de permis ou code NEPH. Il existe plusieurs cas : Cas n°1 : vous avez obtenu votre 1er permis de
  conduire après le 1er Juin 1975 Votre numéro de permis de conduire comporte 12 chiffres , il se trouve à côté de
  votre photo . Cas n°2 : vous avez obtenu votre 1er permis de conduire avant le 1er Juin 1975 Votre numéro de permis
  de conduire est composé de 1 à 15 caractères alphanumériques suivi de 2 fois 2 chiffres . […] Cas n°3 : votre permis
  de conduire est plus ancien Votre numéro de permis de conduire est composé de 1 à 15 caractères".
- <https://www.plus.transformation.gouv.fr/experiences/5093283_numero-de-permis-non-reconnu>: "Pour les permis de
  conduire obtenu après le 1er Juin 1975, le numéro de permis de conduire comporte 12 chiffres, celui-ci doit être
  renseigné sans espace."

Implemented: the number of a first licence obtained after 1 June 1975, 12 digits, **format only** (no check digit or
field layout is published). No display form; `masked()` keeps the last four digits: `********2345`.

**Not covered:** licences first obtained before 1 June 1975 (1 to 15 characters plus year and department fields —
no fixed format) and the older ones.

## Where this note departs from the plan (the source wins)

| Plan candidate | Source | Implemented |
|---|---|---|
| NIR (sex, birth year/month, department) | INSEE, service-public, DSN norm | `Nir`: structure rules of the DSN norm, `HasSex`; birth year as two digits (no century), month nullable; place code as a string (no ISO mapping); **key format only** (no official algorithm) |
| CNI number | Instruction INTD0000001C (old card); no official text for the 2021 card | `LegacyIdentityCardNumber` (12 digits, month field not validated); the 2021 card **not covered** |
| Passport number | ANTS | `PassportNumber`, `2 digits + 2 letters + 5 digits` |
| Numéro fiscal (SPI) | French TIN sheet (OECD) | `TaxIdentificationNumber`, format only, first digit 0-3 |
| Permis de conduire number | ANTS | `DriverLicenceNumber`, 12 digits (licences since 1 June 1975), format only |
