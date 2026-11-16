<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número de Inscrição Social, the one number named PIS (Caixa), PASEP (Banco do Brasil) or NIT (INSS): "11 dígitos
 * numéricos, incluído o dv - dígito verificador, que é composto de um dígito numérico" (Manual de Orientação do
 * eSocial 2.4), the last checked with the ANS routine `isDvPisPasepValido`: the ten digits weighted 2 to 9 from the
 * right, cycling (3, 2, 9, 8, 7, 6, 5, 4, 3, 2 from the left), "foundDv = 11 - sum % 11", and 0 for a result of 10 or
 * 11. Repeated digits are not refused (an intake rule of SIPREV, not of the issuers). The display form is the
 * Previdência's "XXX.XXXXX.XX-X" (SIPREV validation rules, RV_03); `masked()` keeps the last four digits (no official
 * masking rule): `***.****8.89-3`.
 *
 * @see https://www.gov.br/esocial/pt-br/documentacao-tecnica/manuais/mos-manual-de-orientacao-do-esocial-2-4-publicada.pdf
 *      Manual de Orientação do eSocial, versão 2.4 (March 2018), "Regras da Qualificação Cadastral - NIS"
 * @see https://www.gov.br/ans/pt-br/centrais-de-conteudo/manuais-do-portal-operadoras/sib-manual-de-instalacao-historico-de-versao-e-outros-arquivos/manual/algoritmos-do-aplicativo-de-carga
 *      ANS, "Algoritmos do Aplicativo de Carga" (SIB-XML, 2021-04-06), 7 - PISPASEP
 * @see https://www.gov.br/previdencia/pt-br/outros/imagens/2015/07/rgrva_RegrasValidacao.pdf Ministério da
 *      Previdência (Dataprev), SIPREV Gestão - Cadastros, Regras de Validação, versão 1.14, RV_03
 * @see docs/sources/BR.md "NIS (PIS/PASEP/NIT)"
 */
final class Nis extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian NIS';

    private const array WEIGHTS = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * @return non-empty-string e.g. "118.16788.89-3"
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s.%s.%s-%s',
            \substr($this->value, 0, 3),
            \substr($this->value, 3, 5),
            \substr($this->value, 8, 2),
            \substr($this->value, 10),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly(\str_replace('.', '', $value), 11, self::DOCUMENT);
        $check = 11 - Checksum::weightedMod(\substr($number, 0, 10), self::WEIGHTS, 11);
        if ($number[10] !== (string) ($check >= 10 ? 0 : $check)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }
}
