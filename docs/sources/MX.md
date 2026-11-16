# Mexico — sources

All dates UTC. Sources read 2026-11-16.

| Source | Publisher | Where read |
|---|---|---|
| Instructivo Normativo para la asignación de la Clave Única de Registro de Población (DOF 18-06-2018), numerales Tercero, Décimo Segundo | Secretaría de Gobernación, DGRNPI (RENAPO) | <https://dof.gob.mx/nota_detalle_popup.php?codigo=5526717> |
| Modificación al Instructivo Normativo para la Asignación de la CURP (DOF 18-10-2021) | Secretaría de Gobernación, DGRNPI | <https://dof.gob.mx/nota_detalle_popup.php?codigo=5632965> |
| Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021-10-22), §5 "Casos especiales para la integración de la CURP", §18 Anexos | DGRNPI (RENAPO) | <https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf> |
| "Informe sobre la viabilidad de incorporar en la Credencial para Votar el dato que reconozca a las personas no binarias…" (CRFE, session 2023-01-18), pp. 5-6 | INE, Dirección Ejecutiva del Registro Federal de Electores (DERFE) | <https://repositoriodocumental.ine.mx/xmlui/bitstream/handle/123456789/147568/crfe-1se-2023-01-18-p6-informe.pdf>, Internet Archive copy of 2025-07-04 17:17:46 UTC (`web.archive.org/web/20250704171746id_/…`) |
| Acuerdo INE/CG432/2023 (Consejo General, 2023-07-20), antecedente 3 | INE | <https://repositoriodocumental.ine.mx/xmlui/bitstream/handle/123456789/152537/CGex202307-20-ap-9.pdf>, Internet Archive copy of 2025-06-26 03:38:28 UTC |
| Lineamientos para la conformación de la Lista Nominal de Electores Residentes en el Extranjero para los Procesos Electorales Locales 2021-2022, numeral 1, fracción III, inciso b) | INE (Consejo General, 2021-08-27) | <https://repositoriodocumental.ine.mx/xmlui/bitstream/handle/123456789/124711/CGor202108-27-ap-14-L.pdf>, Internet Archive copy of 2025-06-08 19:00:57 UTC |
| Glosario, "Clave de elector" | INE (portal CAPP, pautas.ine.mx) | <https://pautas.ine.mx/transparencia/caap/glosario.html>, Internet Archive copy of 2024-12-03 23:44:32 UTC |
| Catálogo de tipos de datos del CFDI (`tdCFDI.xsd`), simpleTypes `t_RFC`, `t_RFC_PM`, `t_RFC_PF` | SAT | <http://www.sat.gob.mx/sitio_internet/cfd/tipoDatos/tdCFDI/tdCFDI.xsd> |
| Norma 3000-001-007 (prestaciones económicas: pensiones, rentas vitalicias, subsidios y ayudas), 5.57 | IMSS | <http://reposipot.imss.gob.mx/normatividad/DNMR/Norma/3000-001-007.pdf>, Internet Archive copy of 2018-08-27 12:01:29 UTC |
| Acuerdo por el que se establece el contenido de los pasaportes electrónicos mexicanos (DOF 23-09-2021), III.4, V | SRE | <https://dof.gob.mx/nota_detalle_popup.php?codigo=5630645> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | see `ICAO.md` |

Reachability: `dof.gob.mx`, `www.gob.mx` and `www.sat.gob.mx` answer curl. `repositoriodocumental.ine.mx` answers
with a Cloudflare challenge page, `reposipot.imss.gob.mx` with an Incapsula page, and `ayudarepresentantes.ine.mx` and
`pautas.ine.mx` fail (proxy 502, DNS failure for WebFetch). Their unmodified Internet Archive copies (`id_` form, found
through the CDX API with `filter=statuscode:200`) are read instead. Secondary only (never a rule, never cited in code):
Wikipedia, news sites, CURP/RFC generator and validator sites, law-firm summaries.

## Names (`Name\MX\*`)

1. **Which surnames exist** (Reglas, §5.1, verbatim):
   - 1.10: "Si la persona registrada solo tiene un apellido, se asignará una “X” en la tercera posición." Examples
     "JULIO TOMAS GARDUÑO GAXJ", "MARGARITA ZARO ZAXM".
   - 1.11: "Si la persona registrada no cuenta con apellidos, el sistema asignará “X” en la primera, segunda y tercera
     posición. En la captura de los datos en el sistema se debe anotar “DESCONOCIDO” y este no presentará el dato en la
     Constancia ni en la consulta pública que se realice a esta clave." Examples "JUAN XXXJ", "NANCY XXXN".
2. **Compound surnames and particles** (§5.1, 1.5 and 1.7): "Si los apellidos son compuestos, es decir, formados por más
   de una palabra, tanto en el primero como en el segundo de los apellidos, se utilizará la primera palabra del apellido
   para conformar la clave" (examples "ROCIO RIVA PALACIO CRUZ", "ROBERTO LOPEZ-CASTILLO HOGAZA"); the leading words
   "DA, DAS, DE, DEL, DER, DI, DIE, DD, Y, EL, LA, LOS, LAS, LE, LES, MAC, MC, VAN, VON" are skipped for the key
   ("JUAN ANGEL DE LAS LOMAS GARCES").
3. **Characters** (§5.1, 1.4): "Si en los apellidos o en el nombre aparecieran caracteres especiales como diagonal (/),
   guión (-), punto (.), apóstrofo (‘) y diéresis (¨) en alguna de las vocales, se captura tal cual viene en el
   documento probatorio" (examples "JUAN JOSE D/AMICO ALVAREZ", "MA. GUADALUPE ESTRADA LOPEZ"). Several given names are
   captured as one name field (1.2: "Cuando el nombre es compuesto por más de una palabra").

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` (`first`, `second`) | `\p{sc=Latin}` (`ñ`, `á é í ó ú`, `ü` and foreign diacritics) | hyphen, apostrophe (`O'Hara`), space (`de la Cruz`, `Riva Palacio`) |
| `GivenName` | `\p{sc=Latin}` | hyphen, apostrophe, space (`María Guadalupe`) |

- No RENAPO text lists the letters of a name ("tal cual viene en el documento probatorio"), so the package's Latin
  default applies. `/` and `.` are refused: the ICAO transliteration omits only apostrophes (Part 3 §4.6) and has no
  spelling for them, so a part holding them could never give a Latin form. Abbreviations (`Ma.`) must be written
  without the dot or in full.
- **`PersonName(?$first, ?$second, $given)`**: two surnames, one (`$second` null, 1.10), or none (both null, 1.11). A
  second surname without a first is refused (no RENAPO text describes it). This widens the plan's `FamilyName $first`
  to nullable because 1.11 records persons without surnames.
- Particles stay inside the surname as typed and keep their case (`de la Cruz`); the codec stores the parts as given,
  so a lower-case particle and a single surname round-trip unchanged.
- **Derived Latin:** the surnames joined by a space are the primary identifier, the given names the secondary one,
  through ICAO table 6.A: `Hernández López, José Ángel` → `HERNANDEZ<LOPEZ<<JOSE<ANGEL`; `Ñ` → `N` (default; `NXX` is a
  variant), `Ü` → `UE` (default; `U` a variant), the apostrophe omitted. A person without surnames has the given names
  as the only identifier (`JUAN`, `MARIA<JOSE`): ICAO §4.6 puts a name of one part in the primary identifier (package
  decision for several given names).
- **Override policy `Name\TransliterationVariant`.** No SRE text that could be read lets a passport keep another Latin
  spelling (the 2021 e-passport agreement describes the booklet, not name rules), so an override must be another table
  spelling: `ARGUELLO<<ANA` for `Argüello`, `NUNXXEZ<<ANA` for `Núñez`.
- **Display order** (`full()`): given names, first surname, second surname.

Payload (`PersonNameCodec`): `{first, second, given, latin}`; `first` and `second` null for no surname, `second` null
for one, `latin` the override only. The JSON view `{first, second, given, effectiveLatin}` is refused by the codec.

## CURP (`Document\MX\Curp`)

**Instructivo Normativo (DOF 18-06-2018), numeral Tercero**, table "Posición / Origen / Naturaleza / Observaciones"
(verbatim; the "Naturaleza" cell "Alfabética" of row 1 spans rows 1-4 and the one of row 11 spans rows 11-16):

| Pos. | Origen | Naturaleza |
|---|---|---|
| 1 | Letra inicial del primer apellido | Alfabética |
| 2 | Primera vocal interna del primer apellido | |
| 3 | Letra inicial del segundo apellido | |
| 4 | Primera letra del nombre | |
| 5-6 | Penúltimo / Último dígito del año de nacimiento | Numérica |
| 7-8 | Primer / Segundo dígito del mes de nacimiento ("Cuando el mes o el día sean menores a diez, se antepondrá un cero") | |
| 9-10 | Primer / Segundo dígito del día de nacimiento | |
| 11 | Sexo: H para hombre y M para mujer | Alfabética |
| 12-13 | Lugar de nacimiento codificado en dos posiciones conforme al catálogo para la conformación de la CURP | |
| 14-16 | Primera consonante interna del primer apellido / del segundo apellido / del nombre | |
| 17 | Carácter diferenciador de homonimia y siglo asignado por la aplicación: 0-9 para fechas de nacimiento hasta el 31 de diciembre de 1999, y A-J para fechas de nacimiento a partir del día 01 de enero del año 2000 (numérica o alfabética) | Numérica o alfabética |
| 18 | Corresponde al dígito verificador, el cual es un carácter asignado por la Secretaría de Gobernación, a través de la aplicación de un algoritmo que permite calcular y verificar la correcta conformación de la clave | Numérica |

"1.1. Composición: Alfanumérica; 1.2. Longitud: 18 caracteres". The 2021 modification leaves rows 1-11 and 14-18
unchanged ("..."), and moves the state catalogue of row 12 to the Reglas.

**Reglas 2021, §5.2** (verbatim): "Carácter 17: 1. Números del 0 al 9 para las personas nacidas hasta el 31 de diciembre
del año 1999. 2. Letras de la “A” a la” J” para las personas nacidas a partir del 1º de enero del año 2000. Carácter 18:
Invariablemente consistirá en un número que va del 0 al 9, el cual es asignado por la aplicación de un algoritmo que
permite calcular y verificar la correcta conformación de la clave."

**Substitutions** (Reglas §5.1, verbatim excerpts): 1.1 "Si la letra inicial del nombre o de alguno de los apellidos
consiste en la letra Ñ, se asignará una “X” en su lugar"; 1.8 "Si de la conformación de las primeras cuatro letras de
la CURP se lee una palabra considerada como inconveniente o altisonante señalada en el Anexo 1. Catálogo de palabras
altisonantes, la segunda letra será sustituida por una “X”" (examples "OFELIA PEDRERO DOMINGUEZ PXDO"); 1.9 no
internal vowel → `X` in position 2; 1.10-1.11 one or no surname → `X`; 1.12-1.14, 1.17-1.18 the same for positions
14-16.

**Third sex value** (INE DERFE report, p. 6, verbatim): "se tiene conocimiento que el RENAPO ya ha emitido la CURP con un
elemento que reconoce a las personas no binarias, derivado de sendas resoluciones jurisdiccionales. Dicho elemento se
representa con una “X” en la conformación de la clave, en lugar de la “H” o “M” que representaban el dato del sexo
binario hombre/mujer, respectivamente." and "las claves que ha emitido RENAPO en las que se reconoce la identidad de
género no binaria, se ha utilizado la letra “X”."

Rules applied:

- 18 characters, `A-Z` at 1-4 and 12-16, digits at 5-10 and 18, `H`/`M`/`X` at 11, `0-9` or `A-J` at 17. A `K`-`Z` at 17
  is refused (the Instructivo and the Reglas both give `A-J`).
- **Century (Review Focus 3):** a digit at 17 → 19YY, a letter → 20YY, as RENAPO states it. The date must exist in that
  year: 30 February, month 13 and 1900-02-29 (digit at 17) are refused; 2000-02-29 (letter) is accepted.
- `birthDate()` is midnight UTC; `sex()`: `H` → `Male`, `M` → `Female`, `X` → `Sex::Unspecified` (added for this,
  plan decision "Sex"). The Instructivo still reads "H para hombre y M para mujer"; the `X` is accepted because RENAPO
  issues it (INE report) and a still-valid number is never refused.
- `birthStateCode()`: the two letters as written. The RENAPO catalogue ("Anexo 03. Catálogo de Entidades Federativas
  para la conformación de la CURP") is listed in the Reglas but not published with them, so no code list is applied
  and nothing is mapped to ISO 3166-2:MX (the code for a birth abroad, `NE` in practice, is one of its entries).
- The "palabras inconvenientes" catalogue (Anexo 01) is not applied either way: the substituted forms (`PXDO`) are
  ordinary letters and are accepted; the unsubstituted words are not refused (the catalogue is not published).
- **Check digit: format only** (any digit). RENAPO names "un algoritmo" but publishes none.
- No display form; `masked()` keeps the last four characters (plan default).

## Clave de elector (`Document\MX\ElectorKey`)

**INE lineamientos 2021, numeral 1, fracción III, inciso b)** (verbatim): "Clave de Elector: Elemento de información y control de la Credencial para
Votar, compuesto por 18 caracteres, que se conforma con las primeras letras de los apellidos y nombre, año, mes, día y
clave de la entidad federativa en que la o el titular nació, su sexo y una homoclave interna de registro;"

**INE glossary** (verbatim): "Secuencia alfanumérica de 18 caracteres, que se contiene en la credencial para votar con
fotografía y que la legislación electoral reconoce como clave única del registro de población; integrada a partir de
datos del elector, los primeros 6 caracteres representan el nombre del ciudadano, los siguientes su fecha de
nacimiento, dos más la entidad federativa donde nació, uno para el sexo, otro es un dígito verificador y los últimos
dos son una clave homonimia que permite diferenciar a dos electores que, sus datos, produzcan la misma clave en los
primeros 16 caracteres."

**Sex in the key** (INE report 2023, p. 6): the DERFE proposes "el dato de género no binario que se propone tanto en la
Clave de Elector y la CURP"; INE/CG432/2023 antecedente 3 quotes INE/CG123/2023: the card may show "M" or "H" "únicamente
en el campo de sexo, sin que se modifiquen los datos de información y control que se localizan en la Credencial para
Votar, tales como la clave de elector, la Clave Única del Registro de Población".

Rules applied:

- 18 characters: `A-Z` at 1-6 ("primeras letras"), digits at 7-12 (year, month, day), a letter at 15 (sex), letters
  or digits at 13-14 and 16-18 (no character set is published for the state code or the homoclave).
- The date has no century: a day that is no date in any century is refused (30 February, month 13, 29 February of a
  year not divisible by four), as for the IT codice fiscale (`Internal\TwoDigitYearDate`). `birthYearDigits()`,
  `birthMonth()`, `birthDay()`; no `HasBirthDate`.
- **No `HasSex`, no state mapping:** INE publishes neither the letters of the sex position nor the state codes of the
  key, so `sexCode()` and `birthStateCode()` return the characters as written.
- Check digit / homoclave: format only (no algorithm published).
- No display form; `masked()` keeps the last four characters (plan default).

## RFC (`Document\MX\Rfc`)

**SAT `tdCFDI.xsd`** (verbatim):

```xml
<xs:simpleType name="t_RFC_PF">
  <xs:annotation><xs:documentation>Tipo definido para la expresión de un Registro Federal de Contribuyentes de persona
  física.</xs:documentation></xs:annotation>
  <xs:restriction base="xs:string">
    <xs:whiteSpace value="collapse"/>
    <xs:minLength value="13"/>
    <xs:pattern value="[A-Z&amp;Ñ]{4}[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{2}[0-9A]"/>
  </xs:restriction>
</xs:simpleType>
```

(`t_RFC_PM`, the legal person: `[A-Z&Ñ]{3}…`, 12 characters; not covered.)

- The pattern is applied exactly: no calendar check beyond the month and day ranges (`…560230…` passes, as in the XSD),
  no homoclave check (SAT publishes no algorithm). `ñ` is upper-cased to `Ñ`, a decomposed `N` + U+0303 composed (NFC).
- The generic RFCs `XAXX010101000` / `XEXX010101000` fit the pattern and are accepted.
- No display form; `masked()` keeps the last four characters (plan default); it counts `Ñ` as one character.

## NSS (`Document\MX\SocialSecurityNumber`)

**IMSS Norma 3000-001-007, 5.57** (verbatim): "número de seguridad social o NSS: Número que el Instituto asigna a cada
trabajador, cuando es registrado por primera vez ante el IMSS, en el cual se identifica entidad federativa donde se
otorga, año de incorporación, año de nacimiento y número progresivo. Está integrado por diez dígitos numéricos y dígito
verificador."

- Eleven digits, format only: the norm gives neither the positions of the fields nor the check-digit algorithm, so no
  field accessor and no checksum.
- No display form; `masked()` keeps the last four digits (plan default).

## Passport (not covered)

**Acuerdo DOF 23-09-2021**, III.4 and V (verbatim): "un número de serie perforado por láser que comienza por la letra N
seguido de 8 dígitos" and "un código de barras bidimensional con el número de libreta que comienza con la letra N
seguida de 8 dígitos"; the data page holds "el número del pasaporte" without a format.

Not covered: the agreement gives the booklet number of the 2021 e-passport, not the passport number, and the passports
issued before it (still valid until expiry) have no published format.

## State driving licences (Task 6b)

Researched in plan 3 Task 6b.

## Not covered

- Passport number (above).
- RFC of a legal person (12 characters, `t_RFC_PM`): the package covers natural persons.
- CURP check digit, elector-key homoclave, RFC homoclave check character, NSS check digit: no algorithm published.
- INE credential numbers other than the clave de elector (CIC, OCR, número de emisión): not researched in this task.
