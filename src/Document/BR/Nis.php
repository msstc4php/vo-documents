<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número de Inscrição Social, the one number named PIS (Caixa), PASEP (Banco do Brasil) or NIT (INSS): "11 dígitos
 * numéricos, incluído o dv - dígito verificador, que é composto de um dígito numérico" (Manual de Orientação do
 * eSocial 2.4). Format only: no publication of the issuers gives the check-digit algorithm. The display form is the
 * Previdência's "XXX.XXXXX.XX-X" (SIPREV validation rules, RV_03); `masked()` keeps the last four digits (no official
 * masking rule): `***.****8.89-3`.
 *
 * @see https://www.gov.br/esocial/pt-br/documentacao-tecnica/manuais/mos-manual-de-orientacao-do-esocial-2-4-publicada.pdf
 *      Manual de Orientação do eSocial, versão 2.4 (March 2018), "Regras da Qualificação Cadastral - NIS"
 * @see https://www.gov.br/previdencia/pt-br/outros/imagens/2015/07/rgrva_RegrasValidacao.pdf Ministério da
 *      Previdência (Dataprev), SIPREV Gestão - Cadastros, Regras de Validação, versão 1.14, RV_03
 * @see docs/sources/BR.md "NIS (PIS/PASEP/NIT)"
 */
final class Nis extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian NIS';

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
        return Digits::exactly(\str_replace('.', '', $value), 11, self::DOCUMENT);
    }
}
