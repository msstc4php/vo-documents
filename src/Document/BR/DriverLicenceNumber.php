<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número do registro nacional of the Carteira Nacional de Habilitação, "composto de nove caracteres e dois dígitos
 * verificadores, único para cada condutor" (CONTRAN Resolução nº 1.020/2025, art. 10, I). The resolution says
 * "caracteres", not digits, for the first nine, so letters are accepted there; the last two are digits. Format only:
 * CONTRAN names the "módulo 11" routine and that a remainder of 0 or 1 gives 0 (Resolução nº 886/2021, art. 4, § 1º)
 * but not the weights. The número do espelho and the número do formulário Renach are other numbers, not covered. No
 * display form is published; `masked()` keeps the last four characters (no official masking rule): `*******8901`.
 *
 * @see https://www.gov.br/transportes/pt-br/assuntos/transito/conteudo-contran/resolucoes/Resolucao10202025.pdf/@@display-file/file
 *      CONTRAN, Resolução nº 1.020, de 1º de dezembro de 2025, art. 10
 * @see https://www.gov.br/transportes/pt-br/pt-br/assuntos/transito/conteudo-contran/resolucoes/Resolucao88620212.pdf/@@download/file/Resolucao88620212.pdf
 *      CONTRAN, Resolução nº 886, de 13 de dezembro de 2021, art. 4
 * @see docs/sources/BR.md "CNH"
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian driving licence number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[0-9A-Z]{9}[0-9]{2}')->parse($value, self::DOCUMENT);
    }
}
