<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número de inscrição eleitoral (título de eleitor), "composto por até 12 algarismos": an eight-digit running number
 * ("desprezando-se, na emissão, os zeros à esquerda"), the two-digit code of the unidade da Federação of origin
 * (`01` São Paulo to `27` Tocantins, `28` Exterior) and two check digits "determinados com base no "Módulo 11"" (TSE
 * Resolução nº 23.659/2021, art. 36). A number printed without its leading zeros is read with them and stored with
 * twelve digits. A state code outside the TSE table is refused. The check digits are format only: the TSE names
 * "Módulo 11" but publishes neither the weights nor the rule for a remainder of 10. The state is a string
 * ({@see stateCode()}), not an ISO 3166-2:BR code: code 28 is no state. `masked()` keeps the last four digits (no
 * official masking rule): `********0191`.
 *
 * @see https://www.tse.jus.br/legislacao/compilada/res/2021/resolucao-no-23-659-de-26-de-outubro-de-2021 TSE,
 *      Resolução nº 23.659/2021, art. 36 (read through the Internet Archive copy of 2026-11-10)
 * @see docs/sources/BR.md "Título de eleitor"
 */
final class VoterRegistrationNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 12;

    private const string DOCUMENT = 'Brazilian voter registration number';

    private const int LENGTH = 12;

    private const string ABROAD = '28';

    /** The TSE table: 01 to 27 the states and the Distrito Federal, 28 abroad. */
    private const string STATE_CODES = '/^(?:0[1-9]|1[0-9]|2[0-8])$/';

    /**
     * @return non-empty-string the TSE code of the unidade da Federação of origin, "01" to "28" (e.g. "01" for São
     *                          Paulo, "28" for a registration abroad)
     */
    public function stateCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 8, 2));
    }

    /**
     * Whether the registration was made abroad (TSE code 28, "Exterior (ZZ)").
     */
    public function isAbroad(): bool
    {
        return $this->stateCode() === self::ABROAD;
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = new NumberFormat('0-9', 5, self::LENGTH)->parse($value, self::DOCUMENT);
        $number = \str_pad($digits, self::LENGTH, '0', \STR_PAD_LEFT);
        if (\preg_match(self::STATE_CODES, \substr($number, 8, 2)) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (unknown state code).', self::DOCUMENT));
        }

        return $number;
    }
}
