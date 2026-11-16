<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * São Paulo (IIRGD): one to eight digits and a check character, `12.345.678-2` stored as `123456782`. The
 * Secretaria da Segurança Pública's own antecedentes form takes the number (`maxlength="8"`, digits only,
 * `verificaRange(…, 1, 99999999)`, so all zeros is refused) and the required "dígito de controle" (`0`-`9` or `X`),
 * and checks it with its `checaRG`: the digits weighted 2 to 9 (then 2 again) from the rightmost, summed modulo 11,
 * 10 written `X`. No display form is quoted, so there is no `formatted()`.
 *
 * @see https://www2.ssp.sp.gov.br/aacweb/carrega-formulario SSP-SP, Atestado de Antecedentes Criminais, "Pesquisa de
 *      RG"
 * @see https://www2.ssp.sp.gov.br/AACWEBSTATIC/style/aac.js SSP-SP, `validaFormulario` and `checaRG`
 * @see docs/sources/BR.md "RG per state (Task 7b)"
 */
final class SpIdentityCardNumber extends AbstractIdentityCardNumber
{
    private const int MODULUS = 11;

    private const int REMAINDER_WRITTEN_X = 10;

    /**
     * `checaRG`'s weights 2, 3, … from the rightmost digit, on the number padded to eight digits; its wrap from 9 back
     * to 2 never applies to eight digits, and a leading zero adds nothing.
     */
    private const array WEIGHTS = [9, 8, 7, 6, 5, 4, 3, 2];

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Sp;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes(
            '[0-9][0-9X]',
            '[0-9]{2}[0-9X]',
            '[0-9]{3}[0-9X]',
            '[0-9]{4}[0-9X]',
            '[0-9]{5}[0-9X]',
            '[0-9]{6}[0-9X]',
            '[0-9]{7}[0-9X]',
            '[0-9]{8}[0-9X]',
        );
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        $digits = \substr($normalized, 0, -1);
        if (\ltrim($digits, '0') === '') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (zeros only).', $document));
        }

        if ($this->checkCharacter($digits) !== \substr($normalized, -1)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }

        return $normalized;
    }

    private function checkCharacter(string $digits): string
    {
        $padded = \str_pad($digits, \count(self::WEIGHTS), '0', \STR_PAD_LEFT);
        $remainder = Checksum::weightedMod($padded, self::WEIGHTS, self::MODULUS);

        return $remainder === self::REMAINDER_WRITTEN_X ? 'X' : (string) $remainder;
    }
}
