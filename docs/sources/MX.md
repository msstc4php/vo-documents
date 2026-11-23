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
   documento probatorio" (example "JUAN JOSE D/AMICO ALVAREZ"). Abbreviated given names are recorded too: 1.3 and 5.2
   1.16 list "MARIA, MA., MA, M., M, JOSE, J., J" as first given names (example "MA. GUADALUPE ESTRADA LOPEZ"). Several
   given names are captured as one name field (1.2: "Cuando el nombre es compuesto por más de una palabra").
4. **ICAO** (Doc 9303 Part 3, 8th ed., §4.6, verbatim): "Punctuation characters are not allowed in the MRZ. Where these
   appear as part of a name, they should be treated as follows: […] Other punctuation characters: All other punctuation
   characters shall be omitted from the MRZ (i.e. no filler character shall be inserted in their place in the MRZ)."

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` (`first`, `second`) | `\p{sc=Latin}` (`ñ`, `á é í ó ú`, `ü` and foreign diacritics) | hyphen, apostrophe (`O'Hara`), space (`de la Cruz`, `Riva Palacio`), slash (`D/Amico`); a full stop after or between letters |
| `GivenName` | `\p{sc=Latin}` | the same (`María Guadalupe`, `Ma. Guadalupe`, `J. Ricardo`, `M.A.`) |

- No RENAPO text lists the letters of a name ("tal cual viene en el documento probatorio"), so the package's Latin
  default applies. `/` and `.` are accepted as RENAPO records them (`Name\MX\AbstractMexicanNamePart`): `/` between
  words, `.` after or between letters (never two in a row, never leading). Other punctuation stays refused (none is
  quoted).
- **ICAO form of `/` and `.`:** omitted without a filler (§4.6 "Other punctuation characters", quoted above), through
  the `$omittedPunctuation` constructor argument of `Name\Latin\AbstractLatinPersonName` (default none, MX `.` `/`):
  `D/Amico` → `DAMICO`, `Ma. Guadalupe` → `MA<GUADALUPE`. The parts are stored as typed (`Ma.`), so `Ma. Guadalupe` and
  `Ma Guadalupe` are different names with the same ICAO form.
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

**Reglas 2021, §5.3** (verbatim): "Carácter 17: 1. Números del 0 al 9 para las personas nacidas hasta el 31 de diciembre
del año 1999. 2. Letras de la “A” a la” J” para las personas nacidas a partir del 1º de enero del año 2000. Carácter 18:
Invariablemente consistirá en un número que va del 0 al 9, el cual es asignado por la aplicación de un algoritmo que
permite calcular y verificar la correcta conformación de la clave."

**Substitutions** (Reglas §5.1 for positions 1-4, §5.2 for positions 14-16, verbatim excerpts): 1.1 "Si la letra inicial
del nombre o de alguno de los apellidos consiste en la letra Ñ, se asignará una “X” en su lugar"; 1.8 "Si de la
conformación de las primeras cuatro letras de la CURP se lee una palabra considerada como inconveniente o altisonante
señalada en el Anexo 1. Catálogo de palabras altisonantes, la segunda letra será sustituida por una “X”" (examples
"OFELIA PEDRERO DOMINGUEZ PXDO"); 1.9 no internal vowel → `X` in position 2; 1.10-1.11 one or no surname → `X`; under
5.2, 1.12-1.14 and 1.17-1.18 the same for positions 14-16.

**Third sex value** (INE DERFE report, p. 6, verbatim): "se tiene conocimiento que el RENAPO ya ha emitido la CURP con un
elemento que reconoce a las personas no binarias, derivado de sendas resoluciones jurisdiccionales. Dicho elemento se
representa con una “X” en la conformación de la clave, en lugar de la “H” o “M” que representaban el dato del sexo
binario hombre/mujer, respectivamente." and "las claves que ha emitido RENAPO en las que se reconoce la identidad de
género no binaria, se ha utilizado la letra “X”."

Rules applied:

- 18 characters, `A-Z` at 1-4 and 12-16, digits at 5-10 and 18, `H`/`M`/`X` at 11, `0-9` or `A-J` at 17. A `K`-`Z` at 17
  is refused (the Instructivo and the Reglas both give `A-J`).
- **Century (Review Focus 3):** a digit at 17 → 19YY, a letter → 20YY, as RENAPO states it. The date must exist in that
  year: 30 February, month 13 and 1900-02-29 (digit at 17) are refused; 2000-02-29 (letter) is accepted. The
  Instructivo gives "0-9 … hasta el 31 de diciembre de 1999" with no lower bound, so a person born before 1900 reads
  as 19YY (the key cannot tell 1899 from 1999).
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

**INE lineamientos 2021, numeral 1, fracción III, inciso b)** (verbatim): "Clave de Elector: Elemento de información y
control de la Credencial para Votar, compuesto por 18 caracteres, que se conforma con las primeras letras de los apellidos y nombre, año, mes, día y
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

The licencia de conducir of each of the 32 federal entities, in ISO 3166-2:MX order. All sources were read on
2026-11-16 (UTC); "IA" is an unmodified Internet Archive capture (`id_` form) cited with its UTC timestamp. **Result:
one entity is covered (MX-MEX), 31 are not.** Basis for the code: the Task 6 "Files" line builds the `Jurisdiction`,
interface, classes and factory "for state licences if any state is covered", and the controller's Task 6b ruling
accepts the Edomex validator's field rule as the authority's own publication; once one state is covered, the plan's
decision "Namespaces" applies and the enum lists all 32 entities regardless. Classes: `Document\MX\Jurisdiction`
(32 cases), `Document\MX\DriverLicenceNumber`, `Document\MX\DriverLicence\MexDriverLicenceNumber`, factory
`Document\MX\DriverLicenceNumberFactory` (pattern of `Document\CA\DriverLicence\*`).

### Sources

| Ref | Source | Publisher | Where read |
|---|---|---|---|
| MEX-VAL | "Validación de licencias", with the button "Validar licencia de conducir" linking to MEX-VAL2 | Secretaría de Movilidad del Estado de México | <https://smovilidad.edomex.gob.mx/validacion_de_licencias>; IA 2026-01-17 19:54:54 of the same page (then linking to MEX-VAL3) |
| MEX-VAL2 | "Valida la autenticidad de la Licencia de Conducir del Estado de México", field "Número de Licencia" | linked from MEX-VAL (host `dev-emx-validador-699226709879.northamerica-south1.run.app`) | <https://dev-emx-validador-699226709879.northamerica-south1.run.app/> |
| MEX-VAL3 | "Validación de Licencias para Conducir" (the earlier validator) | linked from MEX-VAL in January 2026 | IA 2023-10-28 21:38:15 of <http://187.237.233.54/ValidadorLicencias?AspxAutoDetectCookieSupport=1> |
| LGMSV | Ley General de Movilidad y Seguridad Vial (DOF 17-05-2022), art. 29 | Congreso de la Unión | <https://dof.gob.mx/nota_detalle.php?codigo=5652187&fecha=17%2F05%2F2022&print=true> |
| AGU-LV | Ley de Vialidad del Estado de Aguascalientes | Congreso del Estado; copy on the Calvillo municipal site | <https://www.calvillo.gob.mx/LEYES/14%20LEY%20DE%20VIALIDAD%20DEL%20ESTADO%20DE%20AGUASCALIENTES.pdf> |
| CMX-RLM | Reglamento de la Ley de Movilidad de la Ciudad de México (GOCDMX 15-09-2017, última reforma GOCDMX 10-10-2025) | Gobierno de la Ciudad de México; consolidated copy by PAOT (a CDMX public body) | <https://paot.org.mx/centro/reglamentos/df/pdf/2025/RGTO_LEY_MOVILIDAD_10_10_2025.pdf> |
| CMX-VAL | "SEMOVI · Validación de Licencia y Tarjetas de Circulación" (result page of a QR scan; no input field) | SEMOVI | IA 2026-10-05 19:15:56 of <https://app.semovi.cdmx.gob.mx/licencias/a/validadar.php> |
| GUA-RLM | Reglamento de la Ley de Movilidad del Estado de Guanajuato y sus Municipios, Decreto Gubernativo 175 (POGEG 22-11-2016), art. 306 | Gobierno del Estado de Guanajuato; copy on the León municipal site | <https://leon.gob.mx/movilidad/adjunto.php?a=773> |
| JAL-LMT | Ley de Movilidad y Transporte del Estado de Jalisco (Decreto 24451/LX/13), art. 58 | Congreso del Estado de Jalisco | <https://transparencia.info.jalisco.gob.mx/sites/default/files/Ley%20de%20Movilidad%20y%20Transporte%20del%20Estado%20de%20Jalisco_2.pdf> |
| JAL-ML | "Mi Licencia" portal (no licence-number field) | Gobierno de Jalisco | <https://milicencia.jalisco.gob.mx/> |
| NLE-LRL | Ley que Regula la Expedición de Licencias para Conducir del Estado de Nuevo León (Decreto 282, POE 30-12-2005; última reforma integrada Decreto 443, POE 148-III, 24-11-2023), art. 18 | Congreso del Estado de Nuevo León ("Compilación Legislativa"); copy on the Galeana municipal site | <https://galeana.gob.mx/wp-content/uploads/2025/11/51_Ley_Licencias_de_Conducir.pdf> |
| SLP-SIPEL | "Enlace SIPEL — Licencia de Conducir" (renewal form) | Secretaría de Finanzas de San Luis Potosí | <https://enlacesipel.slp.gob.mx/LicenciasConductor> |
| SLP-VAL | "Valida tu licencia" (CURP + licence number form) | Gobierno de San Luis Potosí | <https://validatulicencia.slp.gob.mx/> |
| TAB-LGTV | Ley General de Tránsito y Vialidad del Estado de Tabasco (última reforma Decreto 265, POE 8733 "E", 16-05-2026), art. 21 | Congreso del Estado de Tabasco | <https://congresotabasco.gob.mx/wp-content/uploads/2026/05/Ley-General-de-Transito-y-Vialidad-del-Estado-de-Tabasco.pdf> |

Reachability on 2026-11-16 (UTC): `semovi.cdmx.gob.mx`, `appsextssl.ebajacalifornia.gob.mx`, `hcnl.gob.mx` (TLS
failure), `icvnl.gob.mx`, `michoacan.gob.mx`, `tramites.yucatan.gob.mx`, `ventanilla.puebla.gob.mx` and
`diputados.gob.mx` did not answer (connection reset, TLS failure or timeout, also through WebFetch), on a first try and
again on a retry later the same day. The Internet Archive was offline at the first attempt and answered later; its CDX
index has no capture of the `semovi.cdmx.gob.mx` licence pages, the Baja California verifier, `icvnl.gob.mx`,
`ventanilla.puebla.gob.mx` or Michoacán licence pages (only CMX-VAL and SEMOVI press releases). Rows whose issuer's
pages could not be read say so ("re-check"). Searched without an official format: web search restricted to each
state's `gob.mx` domain (named in the row) and by state name. Secondary only (never a rule, never cited in code):
vendor and insurer guides (Kavak, Ahorra Seguros), news sites, Justia copies, Wikipedia. Kavak's "en Sonora, el folio
tiene 14 dígitos" cites no official text; a search-index snippet of a Baja California verification page shows a sample
number, but the page could not be read and a sample is not a rule.

**Re-check of the unreachable hosts, 2026-11-17 UTC (plan 4, task 10).** `curl` from the sandbox, hosts allowed one by
one: `appsextssl.ebajacalifornia.gob.mx` (BCN), `www.semovi.cdmx.gob.mx` (CMX), `icvnl.gob.mx` (NLE) and
`tramites.yucatan.gob.mx` (YUC) timed out after 25 s; `ventanilla.puebla.gob.mx` (PUE) closed the TLS handshake
(`SSL_ERROR_SYSCALL`); `michoacan.gob.mx` (MIC) failed certificate verification (`unable to verify the first
certificate`; WebFetch gave the same; not bypassed). WebFetch answered "Socket is closed" for all but MIC. The Internet
Archive answered (CDX): no 200 capture of the BCN verifier or of any CMX licence page (only the home page and Incapsula
stubs; a domain-wide CDX query for CMX returned 504); PUE has only 301/302 captures of `/`; YUC has captures of `/`
and `.well-known` files only; MIC has a 2015 `tramitar-o-renovar-licencia` capture that carries no text, and a 2015
news item on licence renewal incentives; NLE has captures of `icvnl.gob.mx/Licencia` (2025-04-07, a shell without form
fields) and the POE of 2020-10-21 with the digital-licence notice, none of which describes the number. Result: no new
state is covered; every row stays "not covered" and the `fromNumber()` overlap table is unchanged.

**Estado de México (verbatim).** MEX-VAL2, the number field: `placeholder="12 dígitos" maxlength="12"
data-val-length="El Número de Licencia es de 12 dígitos" data-val-length-max="12" data-val-length-min="12"
data-val-regex="El Número de Licencia debe contener solo números." data-val-regex-pattern="^\d{12}$"
id="NumLicencia"`; the second field is the issue date, `type="date" … min="2000-01-01"` ("La Fecha de Emisión es
obligatoria."). MEX-VAL3: "2. Ingrese en la casilla los DOCE números de su licencia (frente de su plástico) y oprima
el botón "Consultar"", field `id="Identifiyer" maxlength="12"`.

Caveats, recorded as the controller's ruling asks: MEX-VAL2 is served from a `dev-` host on Google Cloud Run, not from
`edomex.gob.mx`; it counts as the authority's publication because the Secretaría's own page links it as its licence
validator, and the earlier validator (MEX-VAL3, linked the same way) states the same twelve digits. The date field's
`min="2000-01-01"` means the validator only takes licences issued from 2000. No source describes an older number or
says one remains in circulation, so no other format is accepted; if one surfaces, add it with its quote.

**What the other official texts say.** The laws and regulations that list a licence's contents name the number and
stop there (verbatim):

- LGMSV art. 29: the federal and state databases contain "II. Licencias de conducir, incluyendo el tipo de licencia y
  seguros registrados por vehículo"; no number format, no national licence register with a layout.
- NLE-LRL art. 18: "La licencia debe contener, al menos, los datos siguientes: […] II. Institucionales: a) Fechas de
  expedición y de vigencia; b) Número de licencia; y c) Tipo de licencia".
- JAL-LMT art. 58: "En las licencias o permisos para operar o conducir vehículos se precisarán: […] V. El número de
  registro de dicha licencia;".
- TAB-LGTV art. 21: "La Licencia de conducir de automovilista, motociclista o chofer, deberá contener indistintamente
  los siguientes datos: […] IV. Número de identificación del Titular;".
- GUA-RLM art. 306: "El registro estatal de licencias y de infracciones deberá contener al menos: I. La base de datos
  de expedición de licencias y permisos para conducir emitidas por el Instituto;".
- CMX-RLM: the licence number appears only as a datum to report ("Relación de conductores, identificados por nombre,
  domicilio, número de licencia que lo autoriza a conducir este tipo de vehículos").
- SLP-SIPEL: the "número de licencia" field carries the hint "No debe contener guiones, espacios, ni caracteres
  especiales"; SLP-VAL: "Ingresa el número de licencia". Neither gives a length or character set.
- AGU-LV: no clause on the licence number.

### State driving licences — per entity

| ISO | Source | Quoted format | Normalised shape | Encoded data | Official sample | Verdict |
|---|---|---|---|---|---|---|
| MX-AGU | AGU-LV; `aguascalientes.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-BCN | `appsextssl.ebajacalifornia.gob.mx/Verificacion/Licencias` (unreachable, no IA capture); `ebajacalifornia.gob.mx` (searched) | none read | — | — | none read (an index snippet only) | not covered: issuer's pages unreachable on 2026-11-16 and 2026-11-17 UTC, no format found (plan 4 re-check) |
| MX-BCS | `bcs.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-CAM | `campeche.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-CHH | `chihuahua.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-CHP | `chiapas.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-CMX | CMX-RLM; CMX-VAL; `semovi.cdmx.gob.mx` licence pages (unreachable, no IA capture) | number named, no format | — | — | — | not covered: issuer's pages unreachable on 2026-11-16 and 2026-11-17 UTC, no format found (plan 4 re-check) |
| MX-COA | `coahuila.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-COL | `col.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-DUR | `durango.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-GRO | `guerrero.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-GUA | GUA-RLM art. 306; `guanajuato.gob.mx` (searched) | number not described | — | — | — | not covered: no published format |
| MX-HID | `hidalgo.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-JAL | JAL-LMT art. 58; JAL-ML | "El número de registro de dicha licencia" | — | — | — | not covered: no published format |
| MX-MEX | MEX-VAL, MEX-VAL2, MEX-VAL3 | "El Número de Licencia es de 12 dígitos", "debe contener solo números", `^\d{12}$`; "los DOCE números de su licencia" | `[0-9]{12}` | none (format only) | none | covered |
| MX-MIC | `michoacan.gob.mx/tramites/licencias/` (unreachable, no IA capture) | none read | — | — | — | not covered: issuer's pages unreachable on 2026-11-16 and 2026-11-17 UTC, no format found (plan 4 re-check) |
| MX-MOR | `morelos.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-NAY | `nayarit.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-NLE | NLE-LRL art. 18; `icvnl.gob.mx` (Instituto de Control Vehicular, unreachable, no IA capture) | "b) Número de licencia" | — | — | — | not covered: issuer's pages unreachable on 2026-11-16 and 2026-11-17 UTC, no format found (plan 4 re-check) |
| MX-OAX | `oaxaca.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-PUE | `ventanilla.puebla.gob.mx` licence-digital FAQ (unreachable, no IA capture) | none read | — | — | — | not covered: issuer's pages unreachable on 2026-11-16 and 2026-11-17 UTC, no format found (plan 4 re-check) |
| MX-QUE | `queretaro.gob.mx` (searched; CEMER projects) | none | — | — | — | not covered: no published format |
| MX-ROO | `qroo.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-SIN | `sinaloa.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-SLP | SLP-SIPEL, SLP-VAL | "No debe contener guiones, espacios, ni caracteres especiales" | — | — | — | not covered: no length or character set |
| MX-SON | `sonora.gob.mx` (searched); "14 dígitos" from a vendor guide only | none | — | — | — | not covered: no official format |
| MX-TAB | TAB-LGTV art. 21 | "IV. Número de identificación del Titular" | — | — | — | not covered: no published format |
| MX-TAM | `finanzas.tamaulipas.gob.mx` requirement and fee sheets (searched) | none | — | — | — | not covered: no published format |
| MX-TLA | `tlaxcala.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-VER | `veracruz.gob.mx` (searched) | none | — | — | — | not covered: no published format |
| MX-YUC | `tramites.yucatan.gob.mx` (unreachable); Reglamento de Tránsito seen only on Justia (not official) | none read | — | — | — | not covered: no official source read on 2026-11-16 and 2026-11-17 UTC (plan 4 re-check) |
| MX-ZAC | `zacatecas.gob.mx` (searched) | none | — | — | — | not covered: no published format |

Rulings: separators of `Internal\Digits::SEPARATORS` are removed, as in US and CA (the Edomex field itself takes only
digits); leading zeros are kept; no check digit and no encoding (none quoted). `masked()` keeps the last four digits
(plan default: the number has more than eight characters). No `formatted()`: no display form is quoted.

### State driving licences — overlap (the factory's test oracle)

Only one entity is covered, so no shape overlaps another and `fromNumber()` never throws
`AmbiguousJurisdictionException` today. A licence of one of the 31 uncovered entities that happens to have twelve
digits is reported as MX-MEX by `fromNumber()` (the factory can only match published formats); a caller that knows
the issuer uses `fromNumberIn()`, which throws "(format not covered)" for the 31.

| Length after normalisation | Shapes | Matching entities |
|---|---|---|
| 12 | `[0-9]{12}` | MX-MEX |
| any other | — | none |

**Since 1.0 the factory has no `fromNumber()`** (owner decision O2: the country is not fully covered, so a single
match does not identify the issuer); the `fromNumber()` results below are the pre-1.0 behaviour and now describe a
single-element `jurisdictionsMatching()`.

Worked examples (`jurisdictionsMatching()`, then `fromNumber()`):

| Input | Matching | `fromNumber()` |
|---|---|---|
| `` | none | "Invalid Mexican driver's licence number (0 characters)." |
| `123456789012` | MX-MEX | `MexDriverLicenceNumber` `123456789012` |
| `1234 5678 9012` | MX-MEX | `MexDriverLicenceNumber` `123456789012` |
| `000000000001` | MX-MEX | `MexDriverLicenceNumber` `000000000001` |
| `12345678901` | none | "Invalid Mexican driver's licence number (11 characters)." |
| `1234567890123` | none | "Invalid Mexican driver's licence number (13 characters)." |
| `12345678901A` | none | "Invalid Mexican driver's licence number (fits no jurisdiction)." |

The check script (`mx_state_licences.py`, Task 6b scratchpad) verifies the 32 rows in ISO 3166-2:MX order, the
covered set, the worked examples against the shapes of the per-entity table, and each verbatim quote above against the
downloaded source.

## Not covered

- Passport number (above).
- RFC of a legal person (12 characters, `t_RFC_PM`): the package covers natural persons.
- CURP check digit, elector-key homoclave, RFC homoclave check character, NSS check digit: no algorithm published.
- INE credential numbers other than the clave de elector (CIC, OCR, número de emisión): not researched in this task.
- State driving licence numbers of 31 entities (all but MX-MEX, above): the state laws name the number without a
  format; for BCN, CMX, MIC, NLE, PUE and YUC the issuer's pages could not be read on 2026-11-16 UTC (re-check).
