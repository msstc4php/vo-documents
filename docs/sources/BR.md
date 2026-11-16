# Brazil — sources

All dates UTC. Sources read 2026-11-16.

| Source | Publisher | Where read |
|---|---|---|
| Anexo II do Ato Declaratório Executivo COFIS nº 41 (May 2017), "Manual RERCT", REGRA_VALIDA_CPF | Receita Federal (SPED) | <http://sped.rfb.gov.br/estatico/27/69D86AF38819D6F10B6EE8C145B0005D5F41D2/ADE%2041_2017%20-%20Anexo%20II%20-%20Manual%20RERCT.pdf> |
| Manual de Preenchimento e-Financeira, Anexo II, versão 2.1.1 (ADE COFIS nº 09, 2026-04-22), REGRA_VALIDA_CPF (same text) | Receita Federal (SPED) | <http://sped.rfb.gov.br/estatico/24/010F96BC9C88417C5BB3C0CE8CBD138741C3BD/Manual%20e-Financeira%20v%202.1.1-%20Anexo%20II%20-%20Leiautes%20Gerais.pdf> |
| "Por que a Receita Federal é responsável pelo meu CPF?" (educação fiscal leaflet, 2019) | Receita Federal | <https://www.gov.br/receitafederal/pt-br/assuntos/educacao-fiscal/educacao-fiscal/publicacoes/folhetos/cpf.pdf> |
| SIPREV Gestão - Cadastros, Regras de Validação, versão 1.14, RV_01, RV_03 | Ministério da Previdência (Dataprev) | <https://www.gov.br/previdencia/pt-br/outros/imagens/2015/07/rgrva_RegrasValidacao.pdf> |
| Especificação Técnica para Integração PIX-PDQ com o Cartão Nacional de Saúde v5 (2015), serviço AuditoriaCadsusPS; RN001, RN032 | Ministério da Saúde, DATASUS | <https://datasus.saude.gov.br/wp-content/uploads/2019/12/Especificac%CC%A7a%CC%83o-Te%CC%81cnica-para-Integrac%CC%A7a%CC%83o-PIX-PDQ-com-o-Carta%CC%83o-Nacional-de-Sa%C3%BAde.pdf> |
| Resolução nº 23.659, de 26 de outubro de 2021 (cadastro eleitoral), art. 36 | Tribunal Superior Eleitoral | <https://www.tse.jus.br/legislacao/compilada/res/2021/resolucao-no-23-659-de-26-de-outubro-de-2021>, Internet Archive copy of 2026-11-10 22:48:11 UTC (`web.archive.org/web/20261110224811id_/…`) |
| Manual de Orientação do eSocial, versão 2.4 (March 2018), "Regras da Qualificação Cadastral - NIS" | eSocial (Receita Federal, Ministério do Trabalho, INSS, Caixa) | <https://www.gov.br/esocial/pt-br/documentacao-tecnica/manuais/mos-manual-de-orientacao-do-esocial-2-4-publicada.pdf> |
| Resolução CONTRAN nº 1.020, de 1º de dezembro de 2025 (DOU 2025-12-09), art. 10 | CONTRAN (Ministério dos Transportes) | <https://www.gov.br/transportes/pt-br/assuntos/transito/conteudo-contran/resolucoes/Resolucao10202025.pdf/@@display-file/file> |
| Resolução CONTRAN nº 886, de 13 de dezembro de 2021 (DOU 2021-12-23), art. 4 | CONTRAN | <https://www.gov.br/transportes/pt-br/pt-br/assuntos/transito/conteudo-contran/resolucoes/Resolucao88620212.pdf/@@download/file/Resolucao88620212.pdf> |
| "Número do passaporte: onde fica e outras informações a respeito" (published 2021-06-05, updated 2024-01-29) | Polícia Federal | <https://www.gov.br/pf/pt-br/assuntos/passaporte/ajuda/duvidas_/caderneta/caderneta-numero-onde-fica-e> |
| Passaporte, "Dúvidas": "Nome abreviado no passaporte", "Nome completo não cabe no campo, e agora?" | Polícia Federal | <https://www.gov.br/pf/pt-br/assuntos/passaporte/duvidas> |
| "Dado pessoal errado, erro no passaporte, o que fazer?" (updated 2024-01-29) | Polícia Federal | <https://www.gov.br/pf/pt-br/assuntos/passaporte/ajuda/duvidas_/caderneta/caderneta-dado-errado-o-que> |
| Código Nacional de Normas – Foro Extrajudicial (Provimento nº 149/2023, as amended by Provimento nº 153/2023), art. 515-B | Conselho Nacional de Justiça | <https://atos.cnj.jus.br/atos/detalhar/5243> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | see `ICAO.md` |

Reachability: `www.gov.br`, `sped.rfb.gov.br`, `datasus.saude.gov.br` and `atos.cnj.jus.br` answer curl.
`www.tse.jus.br` answers 403 (curl and WebFetch): its unmodified Internet Archive copy (`id_` form, found through the
CDX API with `filter=statuscode:200`) is read. `normas.receita.fazenda.gov.br` is a single-page application that
returns an empty shell to curl and WebFetch, so the Receita Federal's normative notes there (NE Conj. Cief/CSAR nº
17/1991, AD Cosar/Cotec nº 45/1996, found by search as CPF check-digit texts) were not read; the SPED manuals above
carry the Receita's rule. `rni-docs.anvisa.gov.br` answers 403. Secondary only (never a rule, never cited in code): CPF
and CNS generator or validator sites, municipal and school material (a Santos school worksheet), the state SEFAZ
check-digit pages (they are about state registrations), Wikipedia.

## Names (`Name\BR\*`)

1. **Which parts exist** (CNJ Código Nacional de Normas, art. 515-B, verbatim): "Toda pessoa tem direito ao nome, nele
   compreendidos o prenome, de livre escolha dos pais, e o sobrenome, que indicará a ascendência do registrado." §1º:
   "[…] sendo obrigatório que o nome contenha o sobrenome de, ao menos, um ascendente de qualquer grau […]". §5º: "Caso
   o declarante indique apenas o prenome do registrado, o oficial completará o nome incluindo ao menos um sobrenome de
   cada um dos pais, se houver". §6º: "Para a composição do nome, é permitido o acréscimo ou supressão de partícula
   entre os elementos do nome, a critério do declarante." §7º: "[…] é obrigatório o acréscimo de agnome ao final do
   nome a fim de distingui-los."
2. **Characters:** no civil registry text read (CNJ code, searched for "alfabeto", "acento", "apóstrofo", "hífen",
   "grafia") lists the letters or punctuation of a name. The DATASUS rule for its own register (RN002, "letras do
   alfabeto romano (incluindo K, W, Y), além dos acentos gráficos (agudo, circunflexo, til e trema) e do caractere
   apóstrofo (‘), para possibilitar a inserção de nomes como JOÃO D’ÁVILA") is an intake rule of CADSUS, not of the
   civil registry, and is not applied.
3. **Passport fields and abbreviation** (Polícia Federal, "Nome abreviado no passaporte", verbatim): "A quantidade
   limite de caracteres nos campos "Sobrenome" e "Nome" é definida pela Organização Internacional da Aviação Civil —
   ICAO como variável. No Brasil, o limite de cada um desses campos é definido em 30 caracteres." / "Quando o nome ou
   sobrenome ultrapassa o limite de 30 caracteres, o Sistema Nacional de Passaportes — SINPA faz a abreviação
   automática. Inicialmente são excluídas as partículas "de", "da", "dos" etc. Se ainda assim o nome ou sobrenome
   ultrapassa o limite de caracteres, o sistema passa a abreviar a penúltima palavra do campo, reduzindo-a a uma única
   letra." / "Se necessário, o sistema segue abreviando a penúltima palavra, mantendo sempre a primeira e a última
   palavra sem qualquer abreviação." / "A abreviação em virtude do limite de caracteres não configura erro no
   passaporte". "Dado pessoal errado": "Acentuação: a falta de cedilha, acento agudo, circunflexo etc, não gera
   restrições ao uso do passaporte" and "Abreviação: a abreviação não configura erro no passaporte."

| Part | Letters | Separators inside |
|---|---|---|
| `FamilyName` (all surnames, particles and agnome included) | `\p{sc=Latin}` (`ã õ`, `ç`, `á é í ó ú`, `â ê ô`, `à`, `ü`) | space (`da Silva`, `dos Santos de Oliveira`), hyphen, apostrophe (`D'Ávila`) |
| `GivenName` (all given names) | `\p{sc=Latin}` | the same (`Maria da Conceição`, `João Pedro`) |

- **Both parts are required** (`PersonName(FamilyName $family, GivenName $given, ?IcaoPersonName $latin = null)`): the
  CNJ code requires a prenome and at least one ascendant's surname, and completes a name given with the prenome only.
  No text read records a Brazilian without a surname, so no one-word name is accepted.
- **Several words per part:** Portuguese names carry several surnames with no fixed count; all of them are one
  `FamilyName`, all given names one `GivenName`, as the passport's "Sobrenome" and "Nome" fields hold them.
- **Particles** (`da`, `das`, `de`, `do`, `dos`, `e`) stay inside the value in the case given (§6º lets the declarant add
  or drop them, so they are part of the registered name). The agnome (`Júnior`, `Filho`, `Neto`) ends the surname part.
- **Punctuation:** none beyond the package default (space, hyphen, apostrophe) is quoted from a civil registry rule, so
  none is added and none is omitted from the ICAO form through `$omittedPunctuation`; the apostrophe is omitted and the
  hyphen kept as in every country (Doc 9303 Part 3 §4.6). A full stop (`J. Pedro`) or a slash is refused.
- **Derived Latin:** SURNAMES<<GIVEN through ICAO table 6.A: `da Silva, João` → `DA<SILVA<<JOAO`; `Conceição` →
  `CONCEICAO`, `Magalhães` → `MAGALHAES`, `Antônio` → `ANTONIO`.
- **Override policy `Name\AnyIcaoSpelling`** (plan decision "Latin override policy", exception clause): the PF's
  abbreviation rule (3) makes passport spellings that are no transliteration of the registered name
  (`dos Santos de Oliveira, Ana` → `SANTOS<OLIVEIRA<<ANA`; a shortened penultimate word `GONCALVES<ALBUQUERQUE<C<BRAGANCA`),
  so any valid ICAO name is kept as the override; one equal to the derived form is not kept. A Brazilian name cannot
  exceed `Icao\PersonName::MAX_LENGTH` (two parts of 100 characters), so the override is never required for length.
- **Display order** (`full()`): given names, then surnames (`João Pedro da Silva`).

Payload (`PersonNameCodec`): `{family, given, latin}`, `given` required, `latin` the override only. The JSON view
`{family, given, effectiveLatin}` is refused by the codec. `da Silva` round-trips unchanged (Review Focus 5).

## CPF (`Document\BR\Cpf`)

**ADE COFIS nº 41/2017, Anexo II, REGRA_VALIDA_CPF** (verbatim; the e-Financeira Anexo II 2.1.1 of 2026 has the same
text): "O dígito verificador do CPF é baseado no cálculo do módulo 11 e corresponde aos 2 últimos dígitos do CPF, o
restante dos dígitos são a base para cálculo dos dígitos verificadores. No caso do CPF, o DV módulo 11 corresponde ao
resto da divisão por 11 do somatório da multiplicação de cada algarismo da base respectivamente por 9, 8, 7, 6, 5, 4,
3, 2, 1 e 0, a partir da unidade. O resto 10 é considerado 0. Veja, abaixo, exemplo de cálculo de DV módulo 11 para o
CPF nº 280012389:"

```
2 8 0 0 1 2 3 8 9=3
x x x x x x x x x
1 2 3 4 5 6 7 8 9
-------------------------
2+16+ 0+ 0+ 5+12+21+64+81 = 201÷11=18, com resto 3
2 8 0 0 1 2 3 8 9 3=8
x x x x x x x x x x
0 1 2 3 4 5 6 7 8 9
----------------------------
0+ 8+ 0+ 0+ 4+10+18+56+72+27 = 195÷11=17, com resto 8
Portanto, CPF+DV = 280012389-38
```

**Fiscal region** (Receita Federal leaflet, verbatim): "Cada pessoa registrada possui um único número de identificação
formado por onze dígitos." "A Receita Federal é dividida em dez Regiões Fiscais para fins administrativos. O nono
dígito do seu CPF corresponde à Região Fiscal do endereço informado no cadastramento inicial. Exemplo: se o endereço
cadastrado é do estado de São Paulo (8ª Região Fiscal), o nono dígito será 8. XXX.XXX.XX8 - XX". The list: "1. Distrito
Federal, Goiás, Mato Grosso, Mato Grosso do Sul e Tocantins; 2. Pará, Amazonas, Acre, Amapá, Rondônia e Roraima; 3.
Ceará, Maranhão e Piauí; 4. Pernambuco, Rio Grande do Norte, Paraíba e Alagoas; 5. Bahia e Sergipe; 6. Minas Gerais;
7. Rio de Janeiro e Espírito Santo; 8. São Paulo; 9. Paraná e Santa Catarina; 0. Rio Grande do Sul."

**Repeated digits** (Review Focus 4). Other agencies, verbatim: DATASUS RN001.3 "Não deve ser aceita a inserção de
números repetidos em todas as posições do campo, ainda que esses números sejam válidos no algoritmo de verificação da
Receita Federal (Ex: 22222222222)"; SIPREV RV_01 "Recusar todos os dígitos iguais."; the SIRC civil-registry guide
lists "Dígitos repetidos" among the reasons a CPF is shown as invalid. No Receita Federal text read refuses such a CPF.

Rules applied:

- Eleven digits; dots, spaces and hyphens are separators. Both check digits are verified exactly as the Receita writes
  them: weights 1 to 9 from the left over the nine base digits, then 0 to 9 over the base and the first check digit,
  the remainder by 11, a remainder of 10 read as 0. (This equals the usual "11 minus the remainder, 10 and 11 → 0" form
  with weights 10..2 and 11..2.)
- **A CPF of one repeated digit is accepted** (`000.000.000-00` … `999.999.999-99` all pass the arithmetic): the owner's
  rule refuses a number only on the issuer's text, and the refusals above are DATASUS', the Previdência's and SIRC's
  intake rules. Decision and quotes are in `CpfTest::repeatedDigits()`.
- `fiscalRegion()`: the ninth digit as a string, "0" to "9". One region spans several states, so no state or ISO
  3166-2:BR code is derived. The digit is the region of the first registration address, not the current one.
- `formatted()`: `280.012.389-38`, the grouping of the leaflet (`XXX.XXX.XX8 - XX`, written without spaces) and of SIPREV
  RV_01 ("no formato XXX.XXX.XXX-XX").
- `masked()`: last four digits (plan default; no official masking rule read): `***.***.*89-38`.

## Título de eleitor (`Document\BR\VoterRegistrationNumber`)

**TSE Resolução nº 23.659/2021, art. 36, parágrafo único** (verbatim): "O número de inscrição será composto por até 12
algarismos, assim discriminados: a) os oito primeiros algarismos serão sequenciados, desprezando-se, na emissão, os
zeros à esquerda; b) os dois algarismos seguintes serão representativos da unidade da Federação de origem da inscrição,
conforme códigos constantes da seguinte tabela: 01 - São Paulo 02 - Minas Gerais 03 - Rio de Janeiro 04 - Rio Grande do
Sul 05 - Bahia 06 - Paraná 07 - Ceará 08 - Pernambuco 09 - Santa Catarina 10 - Goiás 11 - Maranhão 12 - Paraíba 13 -
Pará 14 - Espírito Santo 15 - Piauí 16 - Rio Grande do Norte 17 - Alagoas 18 - Mato Grosso 19 - Mato Grosso do Sul 20 -
Distrito Federal 21 - Sergipe 22 - Amazonas 23 - Rondônia 24 - Acre 25 - Amapá 26 - Roraima 27 - Tocantins 28 - Exterior
(ZZ) c) os dois últimos algarismos constituirão dígitos verificadores, determinados com base no "Módulo 11", sendo o
primeiro calculado sobre o número sequencial e o último sobre o código da unidade da Federação seguido do primeiro
dígito verificador."

Rules applied:

- 5 to 12 digits (a running number of at least one digit after its leading zeros are dropped, the state code, two
  check digits), left-padded with zeros to 12 and stored so; `4567 0191` equals `000045670191`.
- The state code must be in the table, `01` to `28`; `00` and `29`-`99` are refused.
- **Check digits: format only.** The TSE names "Módulo 11" and the digits each covers, but not the weights or the
  digit for a remainder of 10 (the special case for São Paulo and Minas Gerais that validators apply is quoted from no
  TSE text).
- **State as a string**, `stateCode()` "01"-"28", plus `isAbroad()` for 28: the 27 state codes map one to one to the
  ISO 3166-2:BR units, but the table has a 28th code, "Exterior (ZZ)", with no ISO subdivision, so the codes do not
  map one to one and no `Jurisdiction` enum is created here (Task 7b may create one for the RG).
- No display form is quoted; `masked()` keeps the last four digits (plan default): `********0191`.

## NIS (PIS/PASEP/NIT) (`Document\BR\Nis`)

**Manual de Orientação do eSocial 2.4** (verbatim): "O NIS é um número de cadastro/inscrição de 11 (onze) dígitos
atribuído à pessoa física pela CAIXA (Caixa Econômica Federal), pelo Banco do Brasil ou pelo INSS (Instituto Nacional
do Seguro Social)." "[…] o problema estaria no formato do NIS informado (coluna 6), que deve conter 11 dígitos
numéricos, incluído o dv - dígito verificador, que é composto de um dígito numérico". Sample rows: `11816788893`
(accepted) and `1800474420` (refused for its format).

**SIPREV RV_03** (verbatim): "Número do PIS/PASEP/NIT − Aceitar o preenchimento apenas com números; − Realizar o cálculo
do dígito verificador conforme padrão do NIT; − Recusar todos os dígitos iguais. − Possui onze caracteres numéricos, no
formato XXX.XXXXX.XX-X;". **DATASUS RN032**: "deve ser numérica com 11 caracteres e satisfizer o algoritmo de
verificação (modulo 11) do PIS/NIS/PASEP."

Rules applied:

- Eleven digits; dots, spaces and hyphens are separators. One class for PIS, PASEP and NIT (one number, three issuers).
- **Check digit: format only.** "Padrão do NIT" and "modulo 11" are named, no issuer text read (Caixa, INSS, Banco do
  Brasil) gives the weights. Repeated digits are not refused (SIPREV's intake rule, as for the CPF).
- `formatted()`: `118.16788.89-3` (SIPREV "XXX.XXXXX.XX-X"); `masked()` keeps the last four digits (plan default):
  `***.****8.89-3`.

## CNS (`Document\BR\Cns`)

**DATASUS, PIX-PDQ specification, serviço AuditoriaCadsusPS** (verbatim): "Parâmetros de entrada: Número do CNS (15
posições)" and the error message "Número CNS “1111111” inválido. CNS deve ter 15 dígitos e conter somente números."

Not applied: the ANS (Agência Nacional de Saúde Suplementar) publishes, for its SIB-XML intake, Java routines
`validaCns` (numbers derived from a PIS, `pis + "000" + dv` or `pis + "001" + dv`) and `validaCnsProv` (the 15 digits
weighted 15 to 1 sum to a multiple of 11), and refuses `000000000000000`; its FAQ describes numbers beginning with 7
(DATASUS, "definitivos"), 8 or 9 ("gerados remotamente") and 1 or 2. Those are an agency's intake rules, not a
Ministério da Saúde/DATASUS text, and the ANVISA page that says "Validar CNS de acordo com algoritmo do Ministério da
Saúde (em anexo)" answered 403.

Rules applied: fifteen digits, spaces and hyphens as separators (the card prints the number in groups), format only.
No display form is quoted; `masked()` keeps the last four digits (plan default): `***********0004`.

## CNH (`Document\BR\DriverLicenceNumber`)

**CONTRAN Resolução nº 1.020/2025, art. 10** (verbatim): "Os documentos de habilitação conterão dois números de
identificação nacional e um número de identificação da Unidade da Federação responsável pela expedição, da seguinte
forma: I - número do registro nacional: gerado pelo sistema informatizado da Base Índice Nacional de Condutores -
BINCO, de responsabilidade do órgão máximo executivo de trânsito da União, composto de nove caracteres e dois dígitos
verificadores, único para cada condutor durante toda a sua existência, sendo vedada sua reutilização; II - número do
espelho do documento: formado por nove caracteres e um dígito verificador […]; e III - número do formulário Renach:
[…] composto de onze caracteres, sendo as duas primeiras posições a sigla da Unidade Federativa expedidora, facultada
a utilização da última posição como dígito verificador." § 1º: "O número do registro nacional é o indexador do
prontuário do condutor […] e estará vinculado ao seu número de inscrição no Cadastro de Pessoas Físicas - CPF."

**CONTRAN Resolução nº 886/2021, art. 4** (verbatim): "I - Número do Registro Nacional: […] composto de 9 (nove)
caracteres mais 2 (dois) dígitos verificadores de segurança […]" and "§ 1º O dígito verificador é calculado pelo sistema
DSR, utilizando rotina denominada "módulo 11" e sempre que o resto da divisão for 0 (zero) ou 1 (um), o dígito
verificador será 0 (zero)." Resolução 1.020/2025 art. 140 does not list 886/2021 among the revoked resolutions.

Rules applied:

- Eleven characters: nine of `0-9A-Z` ("caracteres": no text restricts them to digits, so letters are not refused)
  and two digits ("dígitos verificadores"). Separators removed, letters upper-cased, leading zeros kept.
- **Check digits: format only**: "módulo 11" and the 0/1 rule are given, the weights are not.
- Only the registro nacional is covered; the número do espelho (per document) and the formulário Renach (per service)
  are other numbers (not covered).
- No display form; `masked()` keeps the last four characters (plan default): `*******8901`.

## Passport number (`Document\BR\PassportNumber`)

**Polícia Federal** (verbatim): "Ele é composto por duas letras - chamadas de "série", e por seis dígitos subsequentes.
Por exemplo: Passaporte CS265436." "O número é diferente cada vez que um novo passaporte é emitido […] pois é gerado
somente no momento em que a Casa da Moeda personaliza a caderneta."

Rules applied: two letters and six digits, format only (no letter set for the series, no check digit; the MRZ check
digit is separate). `series()` returns the two letters. `masked()` keeps the last three characters (plan default for
eight characters): `*****436`. The FAQ is about the passport in general; no other format (diplomatic, official,
emergency) is published, so none is accepted.

## Sex

None of the covered numbers encodes a sex; `Sex` is unchanged.

## RG per state (Task 7b)

Not researched in Task 7a.

## Not covered

- Check digits of the título de eleitor, NIS, CNS and CNH registration number (above).
- CNH número do espelho and número do formulário Renach.
- Passport formats other than the PF's two letters and six digits.
- A CPF masking rule (the plan default is used; no official rule was researched).
- A Brazilian with no surname (the CNJ code requires one; indigenous registration rules were not researched).
- RG of each state: Task 7b.
