<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número do Cartão Nacional de Saúde: "CNS deve ter 15 dígitos e conter somente números" (DATASUS, CADSUS integration
 * specification). Format only: neither the Ministério da Saúde nor DATASUS publishes the check rule in a text that
 * could be read (the ANS publishes a validation routine for its own intake; docs/sources/BR.md "CNS"). No display form
 * is published; `masked()` keeps the last four digits (no official masking rule): `***********0004`.
 *
 * @see https://datasus.saude.gov.br/wp-content/uploads/2019/12/Especificac%CC%A7a%CC%83o-Te%CC%81cnica-para-Integrac%CC%A7a%CC%83o-PIX-PDQ-com-o-Carta%CC%83o-Nacional-de-Sa%C3%BAde.pdf
 *      DATASUS, Especificação Técnica para Integração PIX-PDQ com o Cartão Nacional de Saúde v5, serviço
 *      AuditoriaCadsusPS
 * @see docs/sources/BR.md "CNS"
 */
final class Cns extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian CNS';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 15, self::DOCUMENT);
    }
}
