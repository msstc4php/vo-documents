<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número do Cartão Nacional de Saúde: "CNS deve ter 15 dígitos e conter somente números" (DATASUS), checked with the
 * ANS routine `isDvCnsValido`: the all-zero number is refused, and a number is accepted when `validaCns` (a number
 * derived from a PIS: 11 digits, `000` or `001`, a check digit) or `validaCnsProv` (the 15 digits weighted 15 to 1
 * sum to a multiple of 11) passes. Every number `validaCns` accepts also passes `validaCnsProv` (its check digit makes
 * that sum a multiple of 11), so the weighted sum alone decides; docs/sources/BR.md "CNS" shows why. No display form
 * is published; `masked()` keeps the last four digits (no official masking rule): `***********0005`.
 *
 * @see https://www.gov.br/ans/pt-br/centrais-de-conteudo/manuais-do-portal-operadoras/sib-manual-de-instalacao-historico-de-versao-e-outros-arquivos/manual/algoritmos-do-aplicativo-de-carga
 *      ANS, "Algoritmos do Aplicativo de Carga" (SIB-XML, 2021-04-06), 4 - CNS
 * @see https://datasus.saude.gov.br/wp-content/uploads/2019/12/Especificac%CC%A7a%CC%83o-Te%CC%81cnica-para-Integrac%CC%A7a%CC%83o-PIX-PDQ-com-o-Carta%CC%83o-Nacional-de-Sa%C3%BAde.pdf
 *      DATASUS, Especificação Técnica para Integração PIX-PDQ com o Cartão Nacional de Saúde v5, serviço
 *      AuditoriaCadsusPS
 * @see docs/sources/BR.md "CNS"
 */
final class Cns extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 15;

    private const string DOCUMENT = 'Brazilian CNS';

    private const string ALL_ZEROS = '000000000000000';

    private const array WEIGHTS = [15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly($value, 15, self::DOCUMENT);
        if ($number === self::ALL_ZEROS || Checksum::weightedMod($number, self::WEIGHTS, 11) !== 0) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }
}
