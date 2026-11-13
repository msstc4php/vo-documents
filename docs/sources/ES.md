# Spain — sources

All dates UTC. Sources read 2026-11-13.

| Source | Publisher | Where read |
|---|---|---|
| "Cálculo del dígito de control del NIF/NIE" | Ministerio del Interior | <https://www.interior.gob.es/opencms/es/servicios-al-ciudadano/tramites-y-gestiones/dni/calculo-del-digito-de-control-del-nif-nie/>, read through its Internet Archive capture of 2026-02-28 17:25:49 UTC (`web.archive.org/web/20260228172549/…`), see below |
| Real Decreto 255/2025, de 1 de abril, por el que se regula el Documento Nacional de Identidad (BOE núm. 80, 2-4-2025, BOE-A-2025-6601), arts. 2 and 12 | BOE | <https://www.boe.es/boe/dias/2025/04/02/pdfs/BOE-A-2025-6601.pdf> |
| Orden INT/2058/2008, de 14 de julio (Número de Identidad de Extranjero), BOE núm. 170, 15-7-2008 | BOE | <https://www.boe.es/boe/dias/2008/07/15/pdfs/A30880-30881.pdf> |
| Real Decreto 1065/2007, de 27 de julio (Reglamento general de gestión e inspección tributaria), arts. 19-21 (BOE-A-2007-15984) | BOE | <https://www.boe.es/buscar/pdf/2007/BOE-A-2007-15984-consolidado.pdf> |
| TGSS, Sistema RED, "Mensaje de Afiliación. Trabajador por cuenta ajena. Régimen General. Versión 4.0.0" (and 3.4.0), segment TRA, data 710-730 | Tesorería General de la Seguridad Social | <https://www.seg-social.es/descarga/es/43234>, <https://www.seg-social.es/descarga/es/34137> |
| Resolución de 1 de diciembre de 1997 (identificación única en el Sistema de Información de la Seguridad Social), BOE-A-1997-27151 | BOE | <https://www.boe.es/buscar/pdf/1997/BOE-A-1997-27151-consolidado.pdf> |
| "Qué es el número de la Seguridad Social y dónde obtenerlo" (9-8-2022) | Revista de la Seguridad Social (Secretaría de Estado de la Seguridad Social) | Internet Archive capture of 2024-03-24 of `revista.seg-social.es` |
| Real Decreto 896/2003, de 11 de julio (pasaporte ordinario), arts. 9-10 (BOE-A-2003-13978) | BOE | <https://www.boe.es/buscar/pdf/2003/BOE-A-2003-13978-consolidado.pdf> |
| Real Decreto 818/2009, de 8 de mayo (Reglamento General de Conductores), anexo I (BOE-A-2009-9481) | BOE | <https://www.boe.es/buscar/pdf/2009/BOE-A-2009-9481-consolidado.pdf> |
| Resolución de 9 de julio de 2024 (Convenio Secretaría de Estado de Seguridad – Jefatura Central de Tráfico), BOE núm. 170, 15-7-2024, BOE-A-2024-14501, expositivo cuarto | BOE | <https://www.boe.es/boe/dias/2024/07/15/pdfs/BOE-A-2024-14501.pdf> |
| Ley 20/2011, de 21 de julio, del Registro Civil, arts. 49-53, 56 (BOE-A-2011-12628) | BOE | <https://www.boe.es/buscar/pdf/2011/BOE-A-2011-12628-consolidado.pdf> |

Unreachable or unusable from this environment: `www.interior.gob.es` (every language path answers HTTP 403 with a
Cloudflare JavaScript challenge, to WebFetch and curl alike; the page is therefore read from the Internet Archive's
unmodified capture `…id_/…` of the same URL, which is a copy of the ministry's page, not an aggregator),
`www.dnielectronico.es` (the pages that could be fetched carry no number format), `revista.seg-social.es` (403;
read from its archive capture), `www.consilium.europa.eu/prado` (PRADO, 403 for every Spanish document, no archive
capture), the Chrome browser bridge (not connected).

## Names (`Name\ES\*`)

**Ley 20/2011, art. 49.2** (excerpt, verbatim):

> La filiación determina los apellidos.
> Si la filiación está determinada por ambas líneas, los progenitores acordarán el orden de transmisión de su
> respectivo primer apellido, antes de la inscripción registral.
> […] En los supuestos de nacimiento con una sola filiación reconocida, ésta determina los apellidos. […] En esta
> primera inscripción, cuando así se solicite, podrán constar la preposición «de» y las conjunciones «y» o «i» entre
> los apellidos, en los términos previstos en el artículo 53 de la presente Ley.

**Art. 50.2 and 50.4**: "Las personas son identificadas por su nombre y apellidos." / "A petición del interesado o de su
representante legal, el encargado del Registro sustituirá el nombre propio de aquél por su equivalente en cualquiera de
las lenguas españolas."

**Art. 51.1.º**: "No podrán consignarse más de dos nombres simples o uno compuesto."

**Art. 53.2.º and 4.º**: "La anteposición de la preposición «de» al primer apellido que fuera usualmente nombre propio o
empezare por tal, así como las conjunciones «y» o «i» entre los apellidos." / "La regularización ortográfica de los
apellidos a cualquiera de las lenguas oficiales correspondiente al origen o domicilio del interesado […]".

**Art. 56**: a person who acquires Spanish nationality may keep surnames "en forma distinta de la legal".

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` (`first`, `second`) | `\p{Latin}` (`ñ`, `á é í ó ú`, `ü`, `ç` and foreign diacritics) | hyphen, apostrophe (`d'Ors`), space (`de la Fuente`) |
| `GivenName` | `\p{Latin}` | hyphen, apostrophe, space (`María José`) |

- No Spanish text lists the letters of a civil-status name; the co-official languages (art. 50.4, 53.4.º) and the
  foreign surnames of art. 56 rule out a Castilian-only list, so the package's Latin default applies.
- **Two surnames, the second optional** (plan decision; art. 49.2: one recognised filiation can give one surname
  line, and foreign-born nationals keep their surnames under art. 56). `PersonName($first, $second, $given)` with
  `$second` nullable.
- Particles stay in the surname they belong to and keep their case (`de la Fuente`, `del Río`). The conjunction «y» /
  «i» between the surnames (art. 49.2, 53.2.º) is not modelled: when it is registered, the whole form can be given as
  the first surname (`Ortega y Gasset`, second `null`).
- The art. 51 limit of two simple names or one compound name is a rule for naming a child, not for older names:
  the count is not checked. A compound name is one `GivenName` with a space (`María José`).
- **Catalan middle dot.** `l·l` written with U+00B7 is refused (U+00B7 is not a Latin letter and ICAO table 6.A has
  no row for it); written with `ŀ` (U+0140, table 6.A: `Ŀ` → `L`) it is accepted: `Marceŀlí` → `MARCELLI`.
- **Derived Latin:** the primary identifier is both surnames joined by a space (plan decision), the given name the
  secondary identifier, through ICAO Doc 9303 Part 3 table 6.A (`ICAO.md`): `García López, María José` →
  `GARCIA<LOPEZ<<MARIA<JOSE`; `Ñ` → `N` (table default; `NXX` is a variant), `Ü` → `UE` (table default; `U` and
  `UXX` are variants), the apostrophe is omitted.
- **Override policy `Name\TransliterationVariant`.** RD 896/2003 art. 10.2.f only requires "Dos líneas de caracteres
  OCR … en la forma a que se refieren los acuerdos y disposiciones internacionales aplicables al pasaporte"; no Spanish
  text that could be read lets a passport keep another Latin spelling. An override must therefore be another spelling
  of the table: `ARGUELLES<<ANA` for `Argüelles` (table default `ARGUEELLES`), `MUNXXOZ<<…` for `Muñoz`.
- **Display order** (`full()`): given name, first surname, second surname (`María José García López`).

Payload (`PersonNameCodec`): `{first, second, given, latin}` with `second` null for one surname and `latin` the
override only; the JSON view is `{first, second, given, effectiveLatin}` and is refused by the codec, as for the other
countries.

## DNI (`Document\ES\Dni`)

**RD 255/2025, art. 2.2**: "A cada Documento Nacional de Identidad se le asignará un número personal que tendrá la
consideración de identificador numérico personal de carácter general." **Art. 12 a)** lists on the front: "Número y
carácter de verificación personal e intransferible del Documento Nacional de Identidad."

**RD 1065/2007, art. 19.1**: "el número de identificación fiscal será el número de su documento nacional de identidad
seguido del correspondiente código o carácter de verificación, constituido por una letra mayúscula que habrá de
constar en el propio documento nacional de identidad".

**Ministerio del Interior, "Cálculo del dígito de control del NIF/NIE"**, verbatim (archive capture, see above):

> Para verificar el NIF de españoles residentes mayores de edad, el algoritmo de cálculo del dígito de control es el
> siguiente:
> Se divide el número entre 23 y el resto se sustituye por una letra que se determina por inspección mediante la
> siguiente tabla:
> RESTO 0 1 2 3 4 5 6 7 8 9 10 11 / LETRA T R W A G M Y F P D X B
> RESTO 12 13 14 15 16 17 18 19 20 21 22 / LETRA N J Z S Q V H L C K E
> Por ejemplo, si el número del DNI es 12345678, dividido entre 23 da de resto 14, luego la letra sería la Z:
> 12345678Z.

**Check letter: verified** (official algorithm). Implemented: eight digits and the letter (`12345678Z`, the page's
example), after separators are removed and the letter upper-cased. Wrong length "(N characters)", a letter among the
digits or a digit in last place "(invalid character)", a wrong letter "Invalid Spanish DNI number checksum.".

- **Eight digits.** The texts give no length; the page's only example has eight digits. A number below 10 000 000 must
  be given with its leading zeros (`01234567L`); a seven-digit form is refused rather than padded (no text says how).
- No encoded data. No display form (the example is compact). `masked()` keeps the last four characters (9 > 8,
  package rule; no official masking rule): `*****678Z`.
- **Not covered:** the NIF of art. 19.2 and 20.2 (`K`, `L`, `M` + seven characters + letter): no published check
  algorithm and not a plan candidate; the card's "Número de soporte" (art. 12 a, the document number): no published
  format.

## NIE (`Document\ES\Nie`)

**Orden INT/2058/2008**, apartado único, uno (new second paragraph of apartado sexto a) of the Orden of 7 February
1997), verbatim:

> «El Número de Identidad de Extranjero estará integrado por nueve caracteres con la siguiente composición: Una letra
> inicial, que será la X, seguida de siete dígitos o caracteres numéricos y de un código o carácter de verificación
> alfabético que será definido por el Departamento Ministerial competente. Una vez agotada la serie numérica
> correspondiente a la letra X, se continuará siguiendo el orden alfabético.»

Transitoria única: "Los Números de Identidad de Extranjeros asignados con anterioridad a la entrada en vigor de la
presente Orden seguirán teniendo validez". (The preamble describes the earlier layout: "la letra X; ocho dígitos; y,
la T, como código de verificación".)

**Ministerio del Interior** (same page as the DNI), verbatim:

> Los NIE de extranjeros residentes en España tienen una letra (X, Y, Z), 7 números y dígito de control.
> Para el cálculo del dígito de control se sustituye: X → 0 Y → 1 Z → 2 y se aplica el mismo algoritmo que para el
> NIF.

**Check letter: verified** for the current layout. Implemented: `X`, `Y` or `Z`, seven digits, the letter
(`X1234567L`). Another initial letter "(invalid character)"; a wrong check letter "Invalid Spanish NIE checksum.".
`masked()`: `*****567L`. Initial letters after `Z` are not accepted: the Interior page lists only X, Y, Z.

- **Pre-2008 layout, format only.** `X`, eight digits and a letter `A-Z` (ten characters, `X12345678Z`). The
  transitoria única keeps these numbers valid, but no official text gives their check algorithm (the Interior page
  speaks of seven digits, the 1997 figure only shows `T` as an illustration), so **the letter is not verified** and any
  letter is accepted (controller ruling: refusing a valid old NIE is worse than accepting a wrong letter). Another
  initial than `X`, a non-digit among the eight digits or a digit in last place is "(invalid character)".
  `masked()`: `******678Z`.

## Número de la Seguridad Social (`Document\ES\SocialSecurityNumber`)

**Resolución de 1 de diciembre de 1997**: "Número de la Seguridad Social (número de afiliación a la Seguridad Social)";
the Revista de la Seguridad Social: "El NUSS coincide con el Número de Afiliación (NAF)."

**TGSS, Sistema RED, AFI message 4.0.0 (and 3.4.0), segment TRA**, verbatim from the table:

> 710 Número de afiliación a la Seguridad Social (N.A.F.) C … Obligatorio para todas las acciones a nivel de
> trabajador excepto para acción AI.
> 720 Provincia n..2 O 4 2 1..56
> 730 Número n..10 O 6 10

Implemented: twelve digits — the two-digit "Provincia" (positions 4-5 of the record) and the ten-digit "Número"
(positions 6-15). **The province code is not range-checked** (controller ruling): the message's value list `1..56`
describes what that message accepts, not every number ever issued, and real numbers with higher codes exist.
`provinceCode()` returns the two digits as a string: the message gives no code table, so no `Subdivision` mapping.

- **Format only; the check digits are not verified because no official algorithm is published.** The last two digits
  are described as control digits (`(province + number) mod 97`) only on secondary sites; neither the TGSS
  specifications nor the Seguridad Social pages that could be read state it (owner's rule).
- No display form is published (`28/12345678/90` appears only on secondary sites), so there is no `formatted()`.
  `masked()` keeps the last four digits: `********5678`.

## Passport number — not covered

RD 896/2003 art. 9.4: "Cada pasaporte contará con un número de serie que podrá repetirse en todas sus páginas mediante
perforación"; art. 10.2 a): "El número del pasaporte, que coincidirá con el de serie de la libreta." No composition is
given. The three letters + six digits form (`AAANNNNNN`) is quoted by search snippets of the Council's PRADO register
(ESP-AO-04001), which answered 403 and has no archive capture, and by bank and press pages. No official text could be
read: not covered.

## Driving licence number — no class of its own

RD 818/2009 anexo I lists "(5) el número de permiso" without a format. The BOE-published convenio of 9 July 2024
(expositivo cuarto), verbatim:

> Que los permisos y licencias para conducir vehículos a motor y ciclomotores, emitidos por el Organismo Autónomo
> Jefatura Central de Tráfico, recogen el número del DNI de aquel ciudadano que ha obtenido dicho permiso de conducir.
> Que en el caso de aquellos extranjeros que teniendo en España «Número de Identidad de Extranjero» (NIE), obtienen el
> permiso de conducir en el país, el Organismo Autónomo Jefatura Central de Tráfico emite el permiso de conducir
> recogiendo en dicho permiso el NIE.

The licence number is therefore a `Dni` or a `Nie`; no separate class.

## Where this note departs from the plan (the source wins)

| Plan candidate | Source | Implemented |
|---|---|---|
| DNI | RD 255/2025, RD 1065/2007 art. 19, Ministerio del Interior page | `Dni`: 8 digits + letter, mod 23 letter verified |
| NIE | Orden INT/2058/2008, Ministerio del Interior page | `Nie`: X/Y/Z + 7 digits + letter, verified; pre-2008 X + 8 digits + letter, **format only** |
| Número de afiliación a la Seguridad Social | TGSS Sistema RED AFI message | `SocialSecurityNumber`: 12 digits, **format only** (no province range) |
| Passport number | RD 896/2003 (no format), PRADO unreachable | **not covered** |
| Driving licence number | RD 818/2009, convenio DGP–JCT 2024 | the DNI or NIE; no class |
