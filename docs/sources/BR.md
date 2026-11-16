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
| "Algoritmos do Aplicativo de Carga" (SIB-XML; published 2021-04-06), 4 - CNS, 7 - PISPASEP; same routines in the PDF "Algoritmos Aplicativo de carga SIB-XML" (file `sib-algoritmos-xml10-17012019.pdf`) | Agência Nacional de Saúde Suplementar (ANS) | <https://www.gov.br/ans/pt-br/centrais-de-conteudo/manuais-do-portal-operadoras/sib-manual-de-instalacao-historico-de-versao-e-outros-arquivos/manual/algoritmos-do-aplicativo-de-carga>; <https://www.gov.br/ans/pt-br/arquivos/assuntos/espaco-da-operadora-de-plano-de-saude/aplicativos-ans/sib/sib-algoritmos-xml10-17012019.pdf> |
| "Perguntas & Respostas sobre o Cartão Nacional de Saúde (CNS)" | ANS | <https://www.gov.br/ans/pt-br/centrais-de-conteudo/faq-cns-pdf> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6, §6.A | ICAO | see `ICAO.md` |

Official source (controller ruling, Task 7a fix round 1): any federal or state government body's own publication
counts, not only the issuer's; so the ANS routines are applied (CNS, NIS), while other bodies' **intake policies**
(refusing repeated digits, "numérica") are still not applied where they would refuse a number the arithmetic or the
issuer's text accepts.

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
  TSE text). Re-searched in fix round 1 (2026-11-16 UTC) for any government publication of the weights: web searches
  restricted to `jus.br` and `gov.br` for "título eleitoral dígito verificador cálculo pesos" and for "inscrição
  eleitoral dígito verificador módulo 11 leiaute regra de validação" returned TSE service pages (Título Net, FAQ,
  e-Título), Res. 23.659/2021 itself and state tax (SEFAZ) check-digit pages for other registrations; no TSE or TRE
  manual, layout or validation rule gives the weights.
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

**ANS, "Algoritmos do Aplicativo de Carga", 7 - PISPASEP** (verbatim):

```java
public boolean isDvPisPasepValido(String pisOrPasep) {
    final int DIGIT_COUNT = 11;
    if (pisOrPasep == null) {
        return false;
    }
    String n = pisOrPasep.replaceAll("[^0-9]*", "");
    if (n.length() != DIGIT_COUNT) {
        return false;
    }
    int i;
    int digit;
    int coeficient =2;
    int sum = 0;
    int foundDv;
    int dv = Integer.parseInt(String.valueOf(n.charAt(n.length() - 1)));
    for (i = n.length() - 2; i >= 0; i--) {
        digit = Integer.parseInt(String.valueOf(n.charAt(i)));
        sum += digit * coeficient;
        coeficient++;
        if (coeficient > 9) {
            coeficient = 2;
        }
    }
    foundDv = 11 - sum % 11;
    if (foundDv >= 10) {
        foundDv = 0;
    }
    return dv == foundDv;
}
```

Rules applied:

- Eleven digits; dots, spaces and hyphens are separators. One class for PIS, PASEP and NIT (one number, three issuers).
- **Check digit verified** as the ANS routine: the first ten digits weighted 2, 3, …, 9, 2, 3 from the right (3, 2, 9,
  8, 7, 6, 5, 4, 3, 2 from the left), `11 - sum % 11`, a result of 10 or 11 read as 0. The eSocial sample `11816788893`
  passes (sum 283, 283 mod 11 = 8, 11 − 8 = 3).
- **Repeated digits are not refused**: SIPREV's "Recusar todos os dígitos iguais" is an intake policy, as for the CPF;
  `00000000000` passes the arithmetic and is accepted.
- `formatted()`: `118.16788.89-3` (SIPREV "XXX.XXXXX.XX-X"); `masked()` keeps the last four digits (plan default):
  `***.****8.89-3`.

## CNS (`Document\BR\Cns`)

**DATASUS, PIX-PDQ specification, serviço AuditoriaCadsusPS** (verbatim): "Parâmetros de entrada: Número do CNS (15
posições)" and the error message "Número CNS “1111111” inválido. CNS deve ter 15 dígitos e conter somente números."

**ANS, "Algoritmos do Aplicativo de Carga", 4 - CNS** (verbatim):

```java
public boolean isDvCnsValido(String numCns) {
    String cns = numCns.replaceAll("[^0-9]*", "");
    if (cns.equals("000000000000000")) {
        return false;
    }
    return validaCns(cns) || validaCnsProv(cns);
}
public boolean validaCnsProv(String cns) {
    if (cns.trim().length() != 15) {
        return (false);
    }
    float resto, soma;
    soma = ((Integer.valueOf(cns.substring(0, 1)).intValue()) * 15)
            + ((Integer.valueOf(cns.substring(1, 2)).intValue()) * 14)
            + ((Integer.valueOf(cns.substring(2, 3)).intValue()) * 13)
            + ((Integer.valueOf(cns.substring(3, 4)).intValue()) * 12)
            + ((Integer.valueOf(cns.substring(4, 5)).intValue()) * 11)
            + ((Integer.valueOf(cns.substring(5, 6)).intValue()) * 10)
            + ((Integer.valueOf(cns.substring(6, 7)).intValue()) * 9)
            + ((Integer.valueOf(cns.substring(7, 8)).intValue()) * 8)
            + ((Integer.valueOf(cns.substring(8, 9)).intValue()) * 7)
            + ((Integer.valueOf(cns.substring(9, 10)).intValue()) * 6)
            + ((Integer.valueOf(cns.substring(10, 11)).intValue()) * 5)
            + ((Integer.valueOf(cns.substring(11, 12)).intValue()) * 4)
            + ((Integer.valueOf(cns.substring(12, 13)).intValue()) * 3)
            + ((Integer.valueOf(cns.substring(13, 14)).intValue()) * 2)
            + ((Integer.valueOf(cns.substring(14, 15)).intValue()) * 1);
    resto = soma % 11;
    if (resto != 0) {
        return (false);
    } else {
        return (true);
    }
}
public boolean validaCns(String cns) {
    if (cns.trim().length() != 15) {
        return (false);
    }
    float soma;
    float resto, dv;
    String pis = "";
    String resultado = "";
    pis = cns.substring(0, 11);
    soma = ((Integer.valueOf(pis.substring(0, 1)).intValue()) * 15)
            + ((Integer.valueOf(pis.substring(1, 2)).intValue()) * 14)
            + ((Integer.valueOf(pis.substring(2, 3)).intValue()) * 13)
            + ((Integer.valueOf(pis.substring(3, 4)).intValue()) * 12)
            + ((Integer.valueOf(pis.substring(4, 5)).intValue()) * 11)
            + ((Integer.valueOf(pis.substring(5, 6)).intValue()) * 10)
            + ((Integer.valueOf(pis.substring(6, 7)).intValue()) * 9)
            + ((Integer.valueOf(pis.substring(7, 8)).intValue()) * 8)
            + ((Integer.valueOf(pis.substring(8, 9)).intValue()) * 7)
            + ((Integer.valueOf(pis.substring(9, 10)).intValue()) * 6)
            + ((Integer.valueOf(pis.substring(10, 11)).intValue()) * 5);
    resto = soma % 11;
    dv = 11 - resto;
    if (dv == 11) {
        dv = 0;
    }
    if (dv == 10) {
        soma = ((Integer.valueOf(pis.substring(0, 1)).intValue()) * 15) +
                ((Integer.valueOf(pis.substring(1, 2)).intValue()) * 14) +
                ((Integer.valueOf(pis.substring(2, 3)).intValue()) * 13) +
                ((Integer.valueOf(pis.substring(3, 4)).intValue()) * 12) +
                ((Integer.valueOf(pis.substring(4, 5)).intValue()) * 11) +
                ((Integer.valueOf(pis.substring(5, 6)).intValue()) * 10) +
                ((Integer.valueOf(pis.substring(6, 7)).intValue()) * 9) +
                ((Integer.valueOf(pis.substring(7, 8)).intValue()) * 8) +
                ((Integer.valueOf(pis.substring(8, 9)).intValue()) * 7) +
                ((Integer.valueOf(pis.substring(9, 10)).intValue()) * 6) +
                ((Integer.valueOf(pis.substring(10, 11)).intValue()) * 5)
                + 2;
        resto = soma % 11;
        dv = 11 - resto;
        resultado = pis + "001" + String.valueOf((int) dv);
    } else {
        resultado = pis + "000" + String.valueOf((int) dv);
    }
    if (!cns.equals(resultado)) {
        return (false);
    } else {
        return (true);
    }
}
```

The ANS FAQ adds that numbers beginning with 7 are generated by DATASUS ("definitivos"), with 8 or 9 "gerados
remotamente", and with 1 or 2 derived from a PIS; that is descriptive and not applied as a first-digit rule.

Rules applied:

- Fifteen digits; spaces and hyphens are separators (the card prints the number in groups); dots are not.
- **Check verified** as `isDvCnsValido`: `000000000000000` is refused; otherwise the number must pass `validaCns` or
  `validaCnsProv`. Implemented as the weighted sum of `validaCnsProv` alone (15 to 1, a multiple of 11), because every
  number `validaCns` accepts passes it: with `S` the sum of the first 11 digits weighted 15 to 5, the `000` form adds
  `dv = 11 − S mod 11` (0 for 11) at weight 1, so the full sum is `S + dv ≡ 0`; the `001` form adds 1 at weight 2 and
  `dv = 11 − (S + 2) mod 11`, so the sum is `S + 2 + dv ≡ 0` (a `dv` of 10 or 11 makes a 16-character string that never
  equals the number). The union is therefore `validaCnsProv`. Both branches have test samples: `200000000000003` (`000`),
  `100000000060018` (`001`, first result 10), `700000000000005` and `898000000000002` (`validaCnsProv`).
- No display form is quoted; `masked()` keeps the last four digits (plan default): `***********0005`.

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
- **Check digits: format only**: "módulo 11" and the 0/1 rule are given, the weights are not. Re-searched in fix round
  1 (2026-11-16 UTC) for any government publication of the weights: web searches restricted to `gov.br`,
  `serpro.gov.br` and the SP, RS, MG and PR Detran domains for "CNH número registro RENACH dígito verificador cálculo
  pesos módulo 11" found only the CONTRAN resolutions (511/2014, 598/2016, 718/2017, 886/2021, 1.020/2025), which
  repeat "módulo 11" without weights; the CNH validation code is generated by an algorithm "de propriedade" of the
  federal traffic body (Res. 718/2017, 886/2021), not published.
- **DATASUS RN048** ("A informação é numérica") is a CADSUS intake rule for its own register, not a rule of the
  issuer or of CONTRAN; it is deliberately not applied, so letters stay accepted in the first nine characters as
  CONTRAN's "caracteres" allows.
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

The número do Registro Geral (RG) of the Carteira de Identidade issued by each of the 27 federative units before
the CIN, in ISO 3166-2:BR order. All sources were read on 2026-11-16 (UTC); "IA" is an unmodified Internet Archive
capture (`id_` form, found through the CDX API with `filter=statuscode:200`) cited with its UTC timestamp. **Result:
one unit is covered (BR-SP), 26 are not.** Basis for the code: the Task 7 "Files" line builds the `Jurisdiction`,
interface, classes and factory "for RG if any state is covered", and the controller's ruling counts a state body's
own online form as its publication; once one unit is covered, the plan's decision "Namespaces" applies and the enum
lists all 27 units. Classes: `Document\BR\Jurisdiction` (27 cases), `Document\BR\IdentityCardNumber`,
`Document\BR\IdentityCard\SpIdentityCardNumber`, factory `Document\BR\IdentityCardNumberFactory` (pattern of
`Document\MX\DriverLicence\*` and `Document\CA\HealthCard\*`).

### Sources

| Ref | Source | Publisher | Where read |
|---|---|---|---|
| DEC | Decreto nº 10.977, de 23 de fevereiro de 2022 (compiled text), arts. 3, 11, 25, 26, 28 | Presidência da República | <https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2022/decreto/d10977.htm>, IA 2026-10-21 10:45:48 (`planalto.gov.br` answers 302 to `gov.br/planalto` to curl and WebFetch) |
| SP-AAC | "Pesquisa de RG" form of the Atestado de Antecedentes Criminais (`carrega-formulario`) and its script `aac.js` (`validaFormulario`, `checaRG`, `somente_numero`, `somente_numero_Digito`) | Secretaria da Segurança Pública de São Paulo (IIRGD) | <https://www2.ssp.sp.gov.br/aacweb/carrega-formulario>; <https://www2.ssp.sp.gov.br/AACWEBSTATIC/style/aac.js> |
| BA-AAC | "Emissão de Certificado" form (`solicitar_atestado.asp`) | Polícia Civil da Bahia (SSP/BA) | IA 2025-04-20 02:24:10 of <https://antecedentes.pc.ba.gov.br/solicitar_atestado.asp> (host: proxy 502 on 2026-11-16) |
| CE-AAC | "Atestado de Antecedentes" form (`AtestadoPesquisa.do`) | SSPDS Ceará | IA 2019-01-25 21:46:51 of <http://sistemas.sspds.ce.gov.br/AtestadoAntecedentes/AtestadoPesquisa.do> (host: timeout on 2026-11-16) |
| DF-AAC | "Antecedentes Criminais" request form (`cadastro.aspx`) | Polícia Civil do Distrito Federal | IA 2019-03-09 16:46:10 of <http://certidaoweb.pcdf.df.gov.br/Views/cadastro.aspx> |
| MA-AAC | "Antecedentes Criminais - Maranhão" (single-page application; field rules in its bundle `index-Dwq8SGpy.js`) | Perícia Oficial do Maranhão (SSP/MA) | <https://antecedentes.ssp.ma.gov.br/> |
| MG-AAC | "Solicitação de Atestado de Antecedentes" | Polícia Civil de Minas Gerais | IA 2024-06-10 07:44:40 of <https://wwws.pc.mg.gov.br/atestado/solicitarsel.do?evento=x&fwPlc=sou> (host: proxy 502 on 2026-11-16) |
| PA-AAC | "Antecedente Criminal - Polícia Civil do Pará", consulta | Polícia Civil do Pará | IA 2026-08-09 13:11:09 of <https://antecedentes.pc.pa.gov.br/consulta> |
| PB-AAC | "Emitir atestado de antecedentes criminais" | Polícia Civil da Paraíba | <https://antecedentes.pc.pb.gov.br/EmitirAAC> |
| PE-AAC | "Certidão de Antecedentes Criminais" (`certidaoAntecedentesCriminaisEmitir.jsf`) | SDS Pernambuco (IITB) | IA 2026-07-20 21:55:51 of <http://www.servicos.sds.pe.gov.br/antecedentes/public/pages/certidaoAntecedentesCriminais/certidaoAntecedentesCriminaisEmitir.jsf> (host: timeout on 2026-11-16) |
| PR-AAC | "Solicitação de Atestado de Antecedentes Criminais", etapa 1 de 3 | Polícia Civil do Paraná (Instituto de Identificação) | <https://www.atestados.pr.gov.br/solicitante/validar>; <https://www.atestados.pr.gov.br/info/aac> |
| RJ-AAC | "Apresentação do Serviço" of the Atestado de Antecedentes Criminais | IIFP / Detran-RJ | <https://atestadodic.detran.rj.gov.br/apresentacao.aspx> (the "Solicitar Atestado" postback answered HTTP 500) |
| RR-AAC | "Solicitação de Certidão Antecedentes Criminais" (`IUAtestado.aspx`, `Atestado.js`) | Polícia Civil de Roraima (IDNET) | <https://www.idnetbrasil.rr.gov.br/AtestadoWeb/pages/Atestado/IUAtestado.aspx> |
| RS-AAC | "Emitir Certidão de Antecedentes Policiais" and `emissaodeantecedentes.js` | Polícia Civil do Rio Grande do Sul | <https://www.pc.rs.gov.br/emitir-certidao-de-antecedentes-policiais> |
| SC-AAC | Delegacia Virtual, "Atestado de antecedentes" (Angular bundle `main-es2015.35f719b87b0ad5da7f72.js`) | Polícia Civil de Santa Catarina | <https://delegaciavirtual.sc.gov.br/antecedentes> |

Searched without an official RG format (web search restricted to the unit's `<uf>.gov.br` domain for its RG and its
online antecedentes form, 2026-11-16 UTC; the hit read is named in the row): every unit not in the table above.
Secondary only (never a rule, never cited in code): RG generator and validator sites (e.g. a "Brazil RG generator"
quoting "the SSP-SP weighted modulo 11 formula" without its weights), blogs, news sites.

**What the federal decree says (verbatim).** DEC art. 3: "A Carteira de Identidade adota o número de inscrição no
Cadastro de Pessoas Físicas - CPF como registro geral nacional previsto no inciso IV do caput do art. 11." Art. 11:
"A Carteira de Identidade conterá: […] IV - o número do registro geral nacional;" (no format). Art. 25: "As Carteiras
de Identidade expedidas de acordo com os padrões anteriores aos estabelecidos neste Decreto permanecerão válidas pelo
prazo de dez anos, contado da data de entrada em vigor deste Decreto. Parágrafo único. Na hipótese prevista no caput
, a Carteira de Identidade de pessoa com idade a partir de sessenta anos na data de entrada em vigor deste Decreto
terá validade indeterminada." Art. 28: "Este Decreto entra em vigor em 1º de março de 2022." So a state RG of the old
model stays valid until 2032-03-01 (UTC date; indefinitely for a holder aged sixty or more on 2022-03-01), and the
number of a CIN is a CPF (`Document\BR\Cpf`), not a state RG.

**São Paulo (verbatim).** SP-AAC, the page: "- com a indicação do número do RG e dígito de controle
constantes na Carteira de Identidade (RG). Se não constar o dígito de controle após o Número do RG, dirija-se a um
Posto Poupatempo ou Posto de Identificação da Polícia Civil para requerer a 2a. via da Carteira." "Em ambos os casos,
o RG ou a CIN devem ter sido emitidos pelo Instituto de Identificação Ricardo Gumbleton Daunt - IIRGD de São Paulo."
The fields: `<input name="numero" id="numero" type="text" size="8" maxlength="8"
onkeyup="javascript:somente_numero(this);…"` and `<input name="digito" id="digito" size="2" maxlength="2"
onkeyup="javascript:somente_numero_Digito(this);…"`. The script: `somente_numero` keeps `var
digits="0123456789";`, `somente_numero_Digito` keeps `var digits="0123456789xX";`; `validaFormulario` refuses
`(!verificaRange(form.numero.value, 1, 99999999))` with "Por favor, preencha o número do RG." and calls
`checaRG(form.numero.value, form.digito.value.toUpperCase() )` (refused with "Por favor, preencha corretamente o
dígito de controle do RG"), and an empty check field with "Por favor, preencha o dígito de controle do RG.". (An
earlier wording, left in an HTML comment of the page, reads "O dígito de controle, após o Número do RG, é de
preenchimento obrigatório.")

```js
function checaRG(rg, dc){
	var produto = 0;
	var multiplicador = 2;
	var digito = 0;
	var tamanhoRG = rg.length;
	for (var i = tamanhoRG - 1; i >= 0; i--){
		produto = produto + (rg.charAt(i) * multiplicador);
		if(multiplicador==9){
			multiplicador = 2;
		} else{
			multiplicador = multiplicador + 1;
		}
	}
	digito = produto % 11; 	
	if (digito == 10){
		digito = "X";
	}
	var retorno = digito==dc.charAt(0);
	return retorno;
}
```

Rules applied to BR-SP, each from the quotes above:

- The number: one to eight digits (`maxlength="8"`, digits only, `verificaRange(…, 1, 99999999)`), so a number of
  zeros only is refused. The number part is stored left-padded with zeros to eight digits (final review of plan 3,
  controller ruling, as the BC and SK licences): `1.234.567-2`, `01.234.567-2` and the form fields `('1234567', null)`
  are one RG, stored `012345672`, and equal. The weights run from the right, so a leading zero does not change the
  check character.
- The check character: one character, `0`-`9` or `X` (`"0123456789xX"`, upper-cased), equal to the sum of the digits
  weighted 2, 3, …, 9 (then 2 again) from the rightmost digit, modulo 11, with 10 written `X`. `checaRG` compares only
  `dc.charAt(0)`, so the field's second position is never part of the check character; the class takes exactly one.
- A card that does not print the check character stays valid (reversed in the Task 7b fix round 1). The SSP page
  itself admits such cards. Its rendered text (re-read 2026-11-16 12:52 UTC) says: "Se não constar o dígito de
  controle após o Número do RG, dirija-se a um Posto Poupatempo ou Posto de Identificação da Polícia Civil para
  requerer a 2a. via da Carteira."; an earlier wording, kept inside an HTML comment of the same page and not rendered,
  read "Se na sua Carteira de Identidade não constar o dígito de controle após o Número do RG, dirigir-se a um Posto
  Poupatempo …". DEC art. 25 keeps old-model cards valid until 2032-03-01
  (indefinitely for a holder aged sixty or more on 2022-03-01). The form's answer (a second copy) is a condition of
  its own online service, not of the card's validity. So `SpIdentityCardNumber::fromNumberAndCheckCharacter($number,
  $checkCharacter)` mirrors the form's two fields: the number field takes digits and dots only (a hyphen, an `X` or a
  check character there is refused with "(number field: digits and dots only)", since `1234567-2` would otherwise be
  read as the eight-digit number `12345672`, another RG); with no check character (`null` or empty) the number must be
  one to eight digits, not all zeros, and the stored value is the number plus the character `checaRG` computes (canonical:
  it round-trips through `from()`); a given check character is verified. The card does not print that character;
  the stored one is computed. `from()` and the factory stay strict (number and check character in one string), since
  a bare form there would match every string of one to eight digits.
- Separators: those of `Internal\Digits::SEPARATORS` and the dot (the printed form `12.345.678-9` is common; dots are
  removed as for `Cpf`). ASCII letters are upper-cased (`x` becomes `X`).
- No display form is quoted, so there is no `formatted()`. `masked()` keeps the last four characters (plan default)
  and never the whole value (controller ruling, fix round 1, kept after the padding): the leading zeros are padding, so
  a value whose significant part (without leading zeros) has n ≤ 4 characters keeps n − 1: `1-2` (`000000012`) →
  `********2`, `10-3` → `*******03`, `123-5` → `******235`, `12.345.678-2` → `*****6782`.
- No official sample number is published; the tests use numbers computed with the quoted algorithm.

**Why the other forms do not give a format.** Each limits only the field (verbatim attributes): BA-AAC
`id="rg" tabindex="2" size="20" maxlength="8" onkeypress="return numero(event);"` with "(digite apenas números, não use
pontos ou hífens)" and "O dígito de controle, após o Número do RG, é opcional." (`id="digito" size="5"
maxlength="2"`, no algorithm); CE-AAC `name="numRg" maxlength="13"` and "Não é preciso informa o dígito verificador.";
DF-AAC `txtIdentidade" type="text" maxlength="20"`; MA-AAC `validarRg(e){return e?e.length<5?"RG deve ter ao menos 5
dígitos":!0:"Campo obrigatório"}`; MG-AAC `name="rgAux_Arg" maxlength="10"`; PA-AAC `<input id="rg" name="rg"
class="form-control" value="" onblur="oConsulta.validar(this)" required>`; PB-AAC `placeholder="Informe seu RG ou
CIN"` (free text); PE-AAC `name="formBody:campoRg" type="text" maxlength="20"` (digits); PR-AAC `type="number"
pattern="\d*"` "Somente números"; RR-AAC `maxlength="15"` … `id="txtRG"` and `maxlength="1"` … `id="txtDV"` (no
algorithm); RS-AAC `name="fRG"` `maxlength="100"` (RG or CPF, script: `fRG: { required: true }`); SC-AAC
`rg:["",wo.Validators.required],ufRg:["",wo.Validators.required]`. A maximum length is an input limit, not a published length
(MX precedent: Edomex was covered on "es de 12 dígitos", an exact length); BA's maximum of eight digits matches SP's,
but BA publishes no check algorithm and makes the control digit optional, so its shape is not known.

### RG per state — per unit

| ISO | Source | Quoted format | Normalised shape | Check digit / encoding | Official sample | Verdict |
|---|---|---|---|---|---|---|
| BR-AC | `ac.gov.br` (searched; Polícia Civil antecedentes service page accepts CIN, CNH or passport) | none | — | — | — | not covered: no published format |
| BR-AL | `al.gov.br` (searched; Polícia Científica "Nada consta" page: number, "dígito verificador, quando houver", no rule) | none | — | — | — | not covered: no published format |
| BR-AM | `am.gov.br` (searched; DPTC antecedentes page, no field rule) | none | — | — | — | not covered: no published format |
| BR-AP | `ap.gov.br` (searched; in-person service only) | none | — | — | — | not covered: no published format |
| BR-BA | BA-AAC | "maxlength=8", digits; control digit "opcional" | — | none published | — | not covered: input limit only, no check algorithm; live host unreachable on 2026-11-16 UTC, re-check |
| BR-CE | CE-AAC | "maxlength=13"; "Não é preciso informa o dígito verificador" | — | — | — | not covered: input limit only; live host unreachable on 2026-11-16 UTC, re-check |
| BR-DF | DF-AAC | "maxlength=20" | — | — | — | not covered: input limit only |
| BR-ES | `es.gov.br` (searched; the PCIES form takes the number of any of nine documents) | none | — | — | — | not covered: no published format |
| BR-GO | `go.gov.br` (searched; I.I. Virtual, no field rule read) | none | — | — | — | not covered: no published format |
| BR-MA | MA-AAC | "RG deve ter ao menos 5 dígitos" | — | — | — | not covered: minimum length only |
| BR-MG | MG-AAC | "maxlength=10" | — | — | — | not covered: input limit only; live host unreachable on 2026-11-16 UTC, re-check |
| BR-MS | `ms.gov.br` (searched; antecedentes service lists the RG, no field rule) | none | — | — | — | not covered: no published format |
| BR-MT | `mt.gov.br` (searched; SESP form takes CPF and a document type) | none | — | — | — | not covered: no published format |
| BR-PA | PA-AAC | free text, required | — | — | — | not covered: no published format |
| BR-PB | PB-AAC | free text ("RG ou CIN") | — | — | — | not covered: no published format |
| BR-PE | PE-AAC | "maxlength=20", digits | — | — | — | not covered: input limit only; live host unreachable on 2026-11-16 UTC, re-check |
| BR-PI | `pi.gov.br` (searched; requests by e-mail) | none | — | — | — | not covered: no published format |
| BR-PR | PR-AAC | "Somente números", RG or CPF | — | — | — | not covered: no published format |
| BR-RJ | RJ-AAC (request form not reached: postback HTTP 500) | none read | — | — | — | not covered: request form unreachable on 2026-11-16 UTC, re-check |
| BR-RN | `www8.defesasocial.rn.gov.br/antecedentes/` (timeout; IA offline at the attempt) | none read | — | — | — | not covered: issuer's form unreachable on 2026-11-16 UTC, re-check |
| BR-RO | `ro.gov.br` (searched; Portal do Cidadão service, no field rule) | none | — | — | — | not covered: no published format |
| BR-RR | RR-AAC | "maxlength=15", DV "maxlength=1" | — | none published | — | not covered: input limit only, no check algorithm |
| BR-RS | RS-AAC | free text (RG or CPF) | — | — | — | not covered: no published format; host answered a WAF "Request Rejected" page on a re-read (2026-11-16 12:52 UTC), re-check |
| BR-SC | SC-AAC | required, no rule | — | — | — | not covered: no published format |
| BR-SE | `se.gov.br` (searched; in-person antecedentes) | none | — | — | — | not covered: no published format |
| BR-SP | SP-AAC | number `maxlength="8"`, digits, `verificaRange(…, 1, 99999999)`; check character `"0123456789xX"`, required; `checaRG` | `[0-9]{1,8}[0-9X]` (2 to 9 characters) | `checaRG`: weights 2-9 from the right, sum mod 11, 10 = `X` | none | covered |
| BR-TO | `to.gov.br` (searched; digital request without document fields) | none | — | — | — | not covered: no published format |

### RG per state — overlap (the factory's test oracle)

Only one unit is covered, so no shape overlaps another and `fromNumber()` never throws
`AmbiguousJurisdictionException` today. An RG of one of the 26 uncovered units whose last character happens to be
the SP check character of the rest is reported as BR-SP by `fromNumber()` (the factory can only match published
formats); a caller that knows the issuer uses `fromNumberIn()`, which throws "(format not covered)" for the 26.

| Length after normalisation | Shapes | Matching units |
|---|---|---|
| 2 to 9 | `[0-9]{1,8}[0-9X]` with the SP check character | BR-SP |
| any other | — | none |

Worked examples (`jurisdictionsMatching()`, then `fromNumber()`):

| Input | Matching | `fromNumber()` |
|---|---|---|
| `` | none | "Invalid Brazilian identity card number (0 characters)." |
| `12.345.678-2` | BR-SP | `SpIdentityCardNumber` `123456782` |
| `123456782` | BR-SP | `SpIdentityCardNumber` `123456782` |
| `24.681.357-X` | BR-SP | `SpIdentityCardNumber` `24681357X` |
| `24681357x` | BR-SP | `SpIdentityCardNumber` `24681357X` |
| `1-2` | BR-SP | `SpIdentityCardNumber` `000000012` |
| `00000001-2` | BR-SP | `SpIdentityCardNumber` `000000012` |
| `99.999.999-0` | BR-SP | `SpIdentityCardNumber` `999999990` |
| `12.345.678-3` | none | "Invalid Brazilian identity card number (fits no jurisdiction)." |
| `0-0` | none | "Invalid Brazilian identity card number (fits no jurisdiction)." |
| `2` | none | "Invalid Brazilian identity card number (1 characters)." |
| `1234567890` | none | "Invalid Brazilian identity card number (10 characters)." |
| `12345678A` | none | "Invalid Brazilian identity card number (fits no jurisdiction)." |

The check script (`br_rg.py`, Task 7b scratchpad) verifies the 27 rows in ISO 3166-2:BR order, the covered set, every
verbatim quote above against the downloaded source, and the worked examples with a literal port of `checaRG`.

## Not covered

- Check digits of the título de eleitor and the CNH registration number (no government text gives the weights; the
  searches are recorded above).
- CNH número do espelho and número do formulário Renach.
- Passport formats other than the PF's two letters and six digits.
- A CPF masking rule (the plan default is used; no official rule was researched).
- A Brazilian with no surname (the CNJ code requires one; indigenous registration rules were not researched).
- RG of 26 units (all but BR-SP, above "RG per state — per unit"): no published format, or only an input
  limit; BA, CE, MG, PE, RJ, RN and RS to re-check when their hosts answer.
