<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * São Paulo (IIRGD): one to eight digits and a check character, `12.345.678-2` stored as `123456782`. The
 * number part is left-padded with zeros to eight digits (`1.234.567-2` is stored as `012345672`), so one RG has one
 * stored value; `checaRG` weighs from the rightmost digit and a leading zero adds nothing, so the check is unchanged.
 * The Secretaria da Segurança Pública's own antecedentes form takes the number (`maxlength="8"`, digits only,
 * `verificaRange(…, 1, 99999999)`, so all zeros is refused) and the required "dígito de controle" (`0`-`9` or `X`),
 * and checks it with its `checaRG`: the digits weighted 2 to 9 (then 2 again) from the rightmost, summed modulo 11,
 * 10 written `X`. No display form is quoted, so there is no `formatted()`.
 *
 * An old-model card that does not print the check character stays valid (Decreto nº 10.977/2022, art. 25: until
 * 2032-03-01, indefinitely for a holder aged sixty or more on 2022-03-01; the SSP page: "Se na sua Carteira de
 * Identidade não constar o dígito de controle…"): {@see fromNumberAndCheckCharacter()} takes the form's two fields
 * and, without a check character, stores the one `checaRG` computes, so the value is canonical. {@see from()} and the
 * factory stay strict, because a bare form there would match every string of one to eight digits.
 *
 * @see https://www2.ssp.sp.gov.br/aacweb/carrega-formulario SSP-SP, Atestado de Antecedentes Criminais, "Pesquisa de
 *      RG"
 * @see https://www2.ssp.sp.gov.br/AACWEBSTATIC/style/aac.js SSP-SP, `validaFormulario` and `checaRG`
 * @see https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2022/decreto/d10977.htm Decreto nº 10.977/2022, art. 25
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

    private const int STORED_LENGTH = 9;

    private const string FORM_FIELDS = 'BR-SP identity card number';

    private const string NUMBER_FIELD_CHARACTERS = '0123456789.';

    /**
     * The SSP form's two fields: the number and, when the card prints one, the check character.
     *
     * @param string|null $checkCharacter null or empty for a card that does not print it: the computed one is stored
     * @param string $number digits and dots only: a hyphen or a check character here would be read as a digit of the
     *                       number and change the RG
     *
     * @throws InvalidDocumentNumberException "(number field: digits and dots only)" for any other character in
     *                                        `$number`, its length for other than one to eight digits, "(invalid
     *                                        character)" for a check character other than one `0`-`9` or `X`, zeros
     *                                        only or a wrong check character
     */
    public static function fromNumberAndCheckCharacter(string $number, ?string $checkCharacter): self
    {
        if (\strspn($number, self::NUMBER_FIELD_CHARACTERS) !== \strlen($number)) {
            $message = \sprintf('Invalid %s (number field: digits and dots only).', self::FORM_FIELDS);

            throw new InvalidDocumentNumberException($message);
        }

        $digits = self::numberField()->parse(\str_replace('.', '', $number), self::FORM_FIELDS);
        $check = \strtoupper(Digits::strip($checkCharacter ?? ''));
        if ($check === '') {
            return self::from($digits . self::checkCharacter($digits));
        }

        if (\strlen($check) !== 1 || \strspn($check, '0123456789X') !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::FORM_FIELDS));
        }

        return self::from($digits . $check);
    }

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

        if (self::checkCharacter($digits) !== \substr($normalized, -1)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }

        return \str_pad($normalized, self::STORED_LENGTH, '0', \STR_PAD_LEFT);
    }

    private static function numberField(): NumberShapes
    {
        return new NumberShapes(
            '[0-9]',
            '[0-9]{2}',
            '[0-9]{3}',
            '[0-9]{4}',
            '[0-9]{5}',
            '[0-9]{6}',
            '[0-9]{7}',
            '[0-9]{8}',
        );
    }

    private static function checkCharacter(string $digits): string
    {
        $padded = \str_pad($digits, \count(self::WEIGHTS), '0', \STR_PAD_LEFT);
        $remainder = Checksum::weightedMod($padded, self::WEIGHTS, self::MODULUS);

        return $remainder === self::REMAINDER_WRITTEN_X ? 'X' : (string) $remainder;
    }
}
