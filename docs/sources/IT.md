# Italy — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| DM 23 dicembre 1976 "Sistemi di codificazione dei soggetti da iscrivere all'anagrafe tributaria" (Ministro per le finanze), reprinted as allegato 2 of the DM 19 aprile 1999 (modelli 730/99), GU Serie Generale n. 97 del 27-04-1999, S.O. n. 83 | Gazzetta Ufficiale (IPZS) | <https://www.gazzettaufficiale.it/eli/id/1999/04/27/099A3188/sg> (allegato 2) |
| The same decree reprinted in GU n. 76 del 02-04-1997 S.O. n. 69 (097A2438), GU 01-04-1998 (098A2638) and GU 27-03-1995 (095A1743) | Gazzetta Ufficiale | `caricaArticolo` pages of those acts on www.gazzettaufficiale.it (compared with the 1999 text, below) |
| Ministero degli affari esteri, DM 23 marzo 2010 n. 303/13 (libretti di passaporto ordinario) and n. 303/14 (libretto di passaporto temporaneo), GU n. 115 del 19-05-2010 (10A05685, 10A05686) | Gazzetta Ufficiale | <https://www.gazzettaufficiale.it/eli/gu/2010/05/19/115/sg/html> |
| Ministero degli affari esteri, DM 23 giugno 2009 n. 303/015 (libretti di passaporto ordinario elettronico), GU n. 147 del 27-06-2009 (09A07410) | Gazzetta Ufficiale | <https://www.gazzettaufficiale.it/gazzetta/serie_generale/caricaDettaglio?dataPubblicazioneGazzetta=2009-06-27&numeroGazzetta=147> |
| Ministero dell'interno, DM 23 dicembre 2015 "Modalità tecniche di emissione della Carta d'identità elettronica", GU n. 302 del 30-12-2015, allegato A | Gazzetta Ufficiale | <https://www.gazzettaufficiale.it/eli/gu/2015/12/30/302/sg/pdf> |
| AgID, "CIE 3.0 – Specifiche Chip" v1.0 (19/11/2015), § 6.12.3 | Agenzia per l'Italia digitale | <https://www.agid.gov.it/sites/default/files/repository_files/documentazione/cie_3.0_-_specifiche_chip.pdf> |
| DPR 3 novembre 2000, n. 396 (ordinamento dello stato civile), art. 35 | Normattiva | <https://www.normattiva.it/uri-res/N2Ls?urn:nir:presidente.repubblica:decreto:2000-11-03;396~art35> |
| "La patente di guida" (field numbering of the card) | Ministero delle infrastrutture e dei trasporti, Portale dell'Automobilista | <https://www.ilportaledellautomobilista.it/web/portale-automobilista/la-patente-di-guida> |

Unreachable or unusable from this environment: `def.finanze.it` (TLS chain incomplete, curl error 60 and WebFetch
"unable to verify the first certificate"), `www.mit.gov.it` (same), `www.cartaidentita.interno.gov.it` (answered the
home page once, then reset every connection: `SSL_ERROR_SYSCALL`, "socket hang up"), its test host
`collaudo.cartaidentita.interno.gov.it` (502 / not resolvable), `augusto.agid.gov.it` (historical GU archive, 502),
`senato.it` PDFs (403), `docs.italia.it` (502). The 1976 Gazzetta issue (n. 345 del 29-12-1976) lists only the laws of
that day in its online index, so the decree is read from its official reprints.

## Names (`Name\IT\*`)

**DPR 396/2000, art. 35**, verbatim:

> 1. Il nome imposto al bambino deve corrispondere al sesso e può essere costituito da un solo nome o da più nomi,
> anche separati, non superiori a tre.
> 2. Nel caso siano imposti due o più nomi separati da virgola, negli estratti e nei certificati rilasciati
> dall'ufficiale dello stato civile e dall'ufficiale di anagrafe deve essere riportato solo il primo dei nomi

No reachable Italian text lists the letters allowed in a civil-status name (the Interior ministry's *massimario* of
civil status could not be read), so the package keeps its Latin default:

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` | `\p{Latin}` (`à è é ì ò ù` and foreign diacritics) | hyphen, apostrophe (`D'Angelo`), space (`De Luca`) |
| `GivenName` | `\p{Latin}` | hyphen, apostrophe, space (`Maria Grazia`) |

- The comma of art. 35 c. 2 (names "separati da virgola") is refused: it marks which name the certificates print and
  is not part of a name. The "non superiori a tre" limit applies to naming a child; older and foreign-born persons
  can have more, so the count is not checked.
- Case is kept (`di Pietro`, `De Luca`); the Latin form is upper case.
- **Derived Latin:** `FAMILY<<GIVEN` through ICAO Doc 9303 Part 3 table 6.A (`ICAO.md`): `Ò` → `O`, `À` → `A`, the
  apostrophe is omitted (`D'Angelo` → `DANGELO`), a space becomes `<` (`DE<LUCA`).
- **Override policy `Name\TransliterationVariant`.** The passport decrees say only that page 2 is the page "destinata
  alla lettura automatica secondo la normativa ICAO" (DM 303/13, art. 1 A); no Italian text that could be read lets a
  passport carry another Latin spelling, so an override must be one of the table's spellings (`MULLER<<ANNA` for
  `Müller Anna`).
- **Display order** (`full()`): given names, then family name (`Maria Grazia De Luca`).
- **Not covered:** the married woman's added surname (a separate civil-status matter); the codice fiscale uses only the
  maiden surname (DM 1976 art. 3).

Payload (`PersonNameCodec`): `{family, given, latin}` with `latin` the override only; the JSON view is
`{family, given, effectiveLatin}` and is refused by the codec, as for AU, DE and FR.

## Codice fiscale (`Document\IT\FiscalCode`)

The official English name is "fiscal code" (CIE layout, DM 23/12/2015 allegato A: "CODICE FISCALE / FISCAL CODE",
"16 caratteri alfanumerici").

**DM 23 dicembre 1976, artt. 2, 5, 6 and 7**, verbatim from the 1999 reprint:

> 2. Numero di codice fiscale delle persone fisiche. - Il numero di codice delle persone fisiche e' costituito da una
> espressione alfanumerica di sedici caratteri.
> I primi quindici caratteri sono indicativi dei dati anagrafici di ciascun soggetto secondo l'ordine seguente:
> tre caratteri alfabetici per il cognome; tre caratteri alfabetici per il nome; due caratteri numerici per l'anno di
> nascita; un carattere alfabetico per il mese di nascita; due caratteri numerici per il giorno di nascita ed il
> sesso; quattro caratteri (uno alfabetico e tre numerici) per il comune italiano o per lo Stato estero di nascita.
> Il sedicesimo carattere, alfabetico, ha funzione di controllo.

> 5. […] I due caratteri numerici indicativi dell'anno di nascita sono, nell'ordine, la cifra delle decine e la cifra
> delle unita' dell'anno stesso.
> Il carattere alfabetico corrispondente al mese di nascita e' quello stabilito per ciascun mese nella seguente
> tabella: Gennaio = A Maggio = E Settembre = P / Febbraio = B Giugno = H Ottobre = R / Marzo = C Luglio = L
> Novembre = S / Aprile = D Agosto = M Dicembre = T
> […] per i soggetti maschili il giorno di nascita figura invariato, con i numeri da uno a trentuno, facendo precedere
> dalla cifra zero i giorni del mese dall'uno al nove. Per i soggetti femminili il giorno di nascita viene aumentato di
> quaranta unita', per cui esso figura con i numeri da quarantuno a settantuno.
> I quattro caratteri alfanumerici indicativi del comune italiano o dello Stato estero di nascita costituiti da un
> carattere alfabetico seguito da tre caratteri numerici, si rivelano rispettivamente dal volume "Codice dei comuni
> d'Italia" o del volume "Codice degli Stati esteri", redatti a cura della Direzione generale del catasto e dei servizi
> tecnici erariali.

> 6. Persone fisiche con identica espressione alfanumerica. - Quando l'espressione alfanumerica relativa ai primi
> quindici carattere del codice risulta comune a due o piu' soggetti, si provvede a differenziarla per ciascuno dei
> soggetti successivi al primo soggetto codificato. A tal fine, si effettuano, nell'ambito dei sette caratteri numerici
> contenuti nel codice, sistematiche sostituzioni di uno o piu' cifre a partire da quella di destra, con corrispondenti
> caratteri alfabetici secondo la seguente tabella:
> 0 = L 5 = R / 1 = M 6 = S / 2 = N 7 = T / 3 = P 8 = U / 4 = Q 9 = V

> 7. Carattere alfabetico di controllo. - Il sedicesimo carattere ha funzione di controllo della esatta trascrizione
> dei primi quindici caratteri. Esso viene determinato nel modo seguente: ciascuno degli anzidetti quindici caratteri,
> a seconda che occupi posizione di ordine pari o posizione di ordine dispari, viene convertito in un valore numerico in
> base alle corrispondenze indicate rispettivamente ai successivi punti 1) e 2).
> 1) Per la conversione dei sette caratteri con posizione di ordine pari:
> A o zero = zero, B o 1 = 1, C o 2 = 2, D o 3 = 3, E o 4 = 4, F o 5 = 5, G o 6 = 6, H o 7 = 7, I o 8 = 8, J o 9 = 9,
> K = 10, L = 11, M = 12, N = 13, O = 14, P = 15, Q = 16, R = 17, S = 18, T = 19, U = 20, V = 21, W = 22, X = 23,
> Y = 24, Z = 25
> 2) Per la conversione degli otto caratteri con posizione di ordine dispari:
> A o zero = 1, B o 1 = 0, C o 2 = 5, D o 3 = 7, E o 4 = 9, F o 5 = 13, G o 6 = 15, H o 7 = 17, I o 8 = 19,
> J o 9 = 21, K = 2, L = 4, M = 18, N = 20, O = 11, P = 3, Q = 6, R = 8, S = 12, T = 14, U = 16, V = 10, W = 22,
> X = 25, Y = 24, Z = 23
> I valori numerici cosi' determinati vengono addizionati e la somma si divide per il numero 26.
> Il carattere di controllo si ottiene convertendo il resto di tale divisione nel carattere alfabetico ad esso
> corrispondente nella tabella sottoindicata: zero = A, 1 = B, … 25 = Z

(The two-column tables of the Gazzetta are read row by row above; the remainder table is `0` = `A` through `25` = `Z`
in alphabetical order.) The 1999, 1998 and 1995 reprints have identical tables. The 1997 reprint (097A2438) prints
"4 = O" in the omocodia table and "r = 19" in table 1; those are transcription errors of that reprint (the three others
read "4 = Q" and "T = 19", and `O` is not a letter of the omocodia series), so the package follows the three others.

**Check character: verified** (official algorithm, art. 7). The decree publishes no example number; the tests use
codes whose check letter was computed with the tables (`RSSMRA80A01H501U`, a commonly published example, gives `U`).

Implemented (16 characters after separators are removed and ASCII letters upper-cased):

| Positions | Rule | Exposed as |
|---|---|---|
| 1-6 | letters `A-Z` (surname and name codes) | not exposed (derived from the names, art. 3-4) |
| 7-8 | digits or omocodia letters | `birthYearDigits()`: the two digits; **no century**, so no `HasBirthDate` |
| 9 | `A B C D E H L M P R S T` ("(invalid birth month)" otherwise) | `birthMonth()`: 1-12 |
| 10-11 | digits or omocodia letters: `01`-`31` (men), `41`-`71` (women) | `birthDay()`: 1-31; `sex()` (`HasSex`): 41-71 → `Sex::Female` |
| 12 | a letter | first character of `birthPlaceCode()` |
| 13-15 | digits or omocodia letters | `birthPlaceCode()`: letter + three digits, e.g. `H501`, `Z404` |
| 16 | a letter, the art. 7 check character | — ("Invalid Italian fiscal code checksum.") |

- **Omocodia (art. 6).** Each of the seven numeric positions holds a digit or its letter `L M N P Q R S T U V`; the
  fields are read through the table (`RSSMRA80A01H50MM` is born on 1 January, place `H501`), the check character is
  computed on the characters as written (the art. 7 tables value every letter). The decree says the substitutions are
  made "a partire da quella di destra", which describes the order in which the office assigns them; it does not say
  that a code with a left digit replaced and a right one kept is invalid, so **any combination is accepted** (owner's
  rule: no structural rule beyond the text). A letter outside the series in a numeric position (`O`, `A`, `K` …) is
  "(invalid character)". The stored value is the code as written, not the base code.
- **Birth day.** "(invalid birth day)" for `00`, `32`-`40`, `72`-`99` and a day the month cannot have. The year has no
  century, so February 29 is accepted when the two-digit year is divisible by four (`00` included: 2000 was a leap
  year, although 1900 was not; the century is unknown, so `00` is accepted) and refused otherwise (`01`, `10`, `98` are
  not leap years in any century). This rejects impossible dates only; an unlikely one is accepted.
- **Place code** is a string, not a `Subdivision`: it is a cadastral (Belfiore) code of a municipality or a foreign
  state (`Z…`), with no one-to-one ISO 3166-2 mapping; the code list itself is not checked.
- `masked()` keeps the last four characters (16 > 8, package rule; no official masking rule): `************501U`. No
  display form is published, so there is no `formatted()`.

**Not covered:** the eleven-digit provisional number of a natural person and the code of non-natural persons (art. 8-10,
"espressione numerica di undici cifre", with its own art. 9 check digit) — not a candidate of the plan. The art. 10
"codice fiscale provvisorio" with wrong personal data has the normal shape and is accepted like any other code.

## Passport number (`Document\IT\PassportNumber`)

**DM 23 marzo 2010 n. 303/13**, art. 1 A) (ordinary passport), verbatim:

> Numero di serie del passaporto: e' riportato in chiaro in pagina 1 in basso, al centro, sotto la parola «Passaporto»
> e a pagina 2 (a stampa orizzontale) in alto a destra ed e' composto da una sequenza alfanumerica di due lettere
> indicanti tipologia e serie del passaporto, seguite da sette numeri arabi.

**DM 23 marzo 2010 n. 303/14** (temporary passport) uses the same words ("… sotto la parola «Passaporto temporaneo» …
composto da una sequenza alfanumerica di due lettere indicanti tipologia e serie del passaporto, seguite da sette
numeri arabi"). The earlier DM 23 giugno 2009 n. 303/015 had the same composition ("sequenza alfanumerica di due
lettere indicanti tipologia e serie del passaporto, seguite da sette numeri arabi"). The 2021 decree on the diplomatic
and service passports (GU 13-11-2021, 21A06658) describes their numbering without a composition: not covered.

Implemented: `[A-Z]{2}[0-9]{7}` after separators are removed and letters upper-cased. **No check digit** (the MRZ check
digit belongs to the MRZ). The decrees give no meaning to each letter, so none is exposed. No display form; `masked()`
keeps the last four characters: `*****4567`. No official example; the tests use numbers of the stated shape.

## Carta d'identità elettronica — not covered

- DM 23 dicembre 2015, allegato A (the card's fields): "Numero del documento — Fronte / Zona 3 — [formato] - — Il campo
  indica il numero che identifica univocamente il documento." The format column is empty; allegato B speaks of the
  "numero univoco nazionale" without a format.
- AgID CIE 3.0 chip specification § 6.12.3: the certificate's `serialNumber` is "IDC" + "IT" + "-" + "il numero del
  documento (corrisponde a quanto riportato nella Zona 2 della carta)", "Esempio: IDCIT-12345678901" — an
  illustration, not the format.
- The two letters + five digits + two letters form (`CA00000AA`) appears on the Interior ministry's CIE portal according
  to search-engine snippets of its test host, and on municipal and press pages; the portal itself could not be read
  (connections reset). No official text could be quoted: not covered.

## Patente di guida — not covered

The Portale dell'Automobilista lists the card's fields ("5 Numero della patente") without a format; Directive
2006/126/EC (annex I, field 5) and D.Lgs. 59/2011 give the field, not the number. The ten-character `U1…` form is
described only on secondary sites. No official format: not covered.

## Where this note departs from the plan (the source wins)

| Plan candidate | Source | Implemented |
|---|---|---|
| Codice fiscale (birth date, sex, birth-place code) | DM 23 dicembre 1976 (GU reprints) | `FiscalCode`: art. 7 check character verified, omocodia accepted in any combination; `HasSex`; year as two digits (no century, no `HasBirthDate`), month and day as ints; place code as a string |
| CIE number | DM 23/12/2015 (no format), AgID chip spec (illustrative example only) | **not covered** |
| Passport number | DM 23 marzo 2010 n. 303/13 and 303/14 | `PassportNumber`, `2 letters + 7 digits` |
| Patente number | Portale dell'Automobilista, Directive 2006/126/EC (no format) | **not covered** |
