<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Normalizer;
use Override;

/**
 * Clave de elector of the INE voter card, 18 characters: six letters of the surnames and name, the birth date
 * `AAMMDD`, two characters of the birth state, one of the sex and three of the internal homoclave (INE lineamientos
 * 2021, glossary of the INE). The year has no century, so there is no `HasBirthDate`; a day that is no date of the
 * month in any century is refused. INE publishes neither the state codes nor the sex letters of the key, so both are
 * plain strings ({@see birthStateCode()}, {@see sexCode()}), not `Sex` or an ISO 3166-2 code; the state accepts any
 * letter or digit, the sex any letter. The six letters may include `Ñ` (INE says only "primeras letras"; unlike the
 * CURP, no INE text substitutes it), stored composed and counted as one character. Format only for the homoclave: the
 * glossary names a check digit but no algorithm. No display form is published; `masked()` keeps the last four
 * characters (no official masking rule): `**************H400`.
 *
 * @see https://repositoriodocumental.ine.mx/xmlui/bitstream/handle/123456789/124711/CGor202108-27-ap-14-L.pdf INE,
 *      Lineamientos para la conformación de la Lista Nominal de Electores Residentes en el Extranjero 2021-2022,
 *      numeral 1, III b)
 * @see https://pautas.ine.mx/transparencia/caap/glosario.html INE, Glosario, "Clave de elector"
 * @see docs/sources/MX.md "Clave de elector"
 */
final class ElectorKey extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 18;

    private const string DOCUMENT = 'Mexican elector key';

    private const int LENGTH = 18;

    private const string SHAPE = '/^[A-ZÑ]{6}[0-9]{6}[0-9A-Z]{2}[A-Z][0-9A-Z]{3}$/Du';

    /** The bytes after the six letters: all ASCII, so byte offsets hold whatever the letters are. */
    private const int TAIL_BYTES = 12;

    /**
     * @return non-empty-string the tens and units of the birth year; the key holds no century
     */
    public function birthYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->tail(), 0, 2));
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        // parse() has validated the month; the clamp only narrows the type for PHPStan.
        return \max(1, \min(12, (int) \substr($this->tail(), 2, 2)));
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        return \max(1, \min(31, (int) \substr($this->tail(), 4, 2)));
    }

    /**
     * @return non-empty-string the INE code of the birth state (positions 13-14) as written, e.g. "09"
     */
    public function birthStateCode(): string
    {
        return Text::nonEmpty(\substr($this->tail(), 6, 2));
    }

    /**
     * @return non-empty-string the letter at position 15, e.g. "H"; INE publishes no value list for it
     */
    public function sexCode(): string
    {
        return $this->tail()[8];
    }

    #[Override]
    public function masked(): string
    {
        return Text::nonEmpty(\str_repeat('*', self::LENGTH - 4) . \substr($this->value, -4));
    }

    #[Override]
    protected function parse(string $value): string
    {
        $normalized = Normalizer::normalize(\mb_strtoupper(Digits::strip($value), 'UTF-8'), Normalizer::FORM_C);
        $key = Digits::upperCasedOfLength(
            \is_string($normalized) ? $normalized : '',
            static fn (int $length): bool => $length === self::LENGTH,
            self::DOCUMENT,
        );
        if (\preg_match(self::SHAPE, $key) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        $tail = \substr($key, -self::TAIL_BYTES);
        $day = (int) \substr($tail, 4, 2);
        if (!TwoDigitYearDate::exists($day, (int) \substr($tail, 2, 2), (int) \substr($tail, 0, 2))) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', self::DOCUMENT));
        }

        return $key;
    }

    private function tail(): string
    {
        return \substr($this->value, -self::TAIL_BYTES);
    }
}
