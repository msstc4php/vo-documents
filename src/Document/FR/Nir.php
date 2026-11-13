<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Numéro d'inscription au répertoire (NIR, "numéro de sécurité sociale"), `S AA MM DD CCC NNN KK`: sex (1 man,
 * 2 woman), the last two digits of the birth year, the birth month, the birth place (department `01`-`99`, `2A`, `2B`
 * and commune or country code), the order number and a two-digit key. The fields follow the DSN norm (month `01`-`12`,
 * `20`, `30`-`42` or `50`-`99`, a month above 12 meaning "not known"). Format only for the key: it is not verified
 * because no official algorithm is published. `masked()` keeps the last four characters (no official masking rule):
 * `* ** ** ** *** *32 - 50`.
 *
 * @see https://www.insee.fr/fr/information/6665188 INSEE, Courrier des statistiques, encadré 1 (sex coding, example)
 * @see https://www.service-public.gouv.fr/particuliers/vosdroits/F33078 Composition du numéro de Sécurité sociale
 * @see https://www.net-entreprises.fr/media/documentation/dsn-cahier-technique-2026.1.pdf DSN cahier technique
 *      CT2026.1, S21.G00.30.001
 * @see docs/sources/FR.md "NIR"
 */
final class Nir extends AbstractStringValueObject implements HasSex, Maskable
{
    private const string DOCUMENT = 'French NIR';

    private const string DIGITS = '0123456789';

    #[Override]
    public function sex(): Sex
    {
        return $this->value[0] === '1' ? Sex::Male : Sex::Female;
    }

    /**
     * @return non-empty-string the last two digits of the birth year; the NIR holds no century
     */
    public function birthYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 1, 2));
    }

    /**
     * @return int<1, 12>|null null when the month field says the month is not known (20, 30-42, 50-99)
     */
    public function birthMonth(): ?int
    {
        $month = (int) \substr($this->value, 3, 2);

        return $this->isCalendarMonth($month) ? $month : null;
    }

    /**
     * @return non-empty-string department (or 99 abroad) and commune (or country) code, e.g. "77131", "2A004"
     */
    public function birthPlaceCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 5, 5));
    }

    /**
     * @return non-empty-string positions 11-13
     */
    public function orderNumber(): string
    {
        return Text::nonEmpty(\substr($this->value, 10, 3));
    }

    /**
     * @return non-empty-string positions 14-15, as issued (not verified)
     */
    public function controlKey(): string
    {
        return Text::nonEmpty(\substr($this->value, 13));
    }

    /**
     * @return non-empty-string e.g. "1 21 12 77 131 232 - 50"
     */
    public function formatted(): string
    {
        return $this->layout(Text::nonEmpty($this->value));
    }

    #[Override]
    public function masked(): string
    {
        return $this->layout(Mask::keepLastCharacters(Text::nonEmpty($this->value), 4));
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9AB', 15, 15)->parse($value, self::DOCUMENT);
        $department = \substr($number, 5, 2);
        if (
            \strspn($number, self::DIGITS, 0, 5) !== 5
            || \strspn($number, self::DIGITS, 7) !== 8
            || ($department !== '2A' && $department !== '2B' && \strspn($department, self::DIGITS) !== 2)
        ) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        $this->assertFields($number, $department);

        return $number;
    }

    private function assertFields(string $number, string $department): void
    {
        $month = (int) \substr($number, 3, 2);
        $reason = match (true) {
            $number[0] !== '1' && $number[0] !== '2' => 'invalid sex digit',
            !$this->isMonthField($month) => 'invalid birth month',
            $department === '00' => 'invalid department',
            \substr($number, 7, 3) === '000' => 'invalid commune',
            \substr($number, 10, 3) === '000' => 'invalid order number',
            default => null,
        };

        if ($reason !== null) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (%s).', self::DOCUMENT, $reason));
        }
    }

    private function isMonthField(int $month): bool
    {
        return $this->isCalendarMonth($month) || $month === 20 || ($month >= 30 && $month <= 42) || $month >= 50;
    }

    /**
     * @phpstan-assert-if-true int<1, 12> $month
     */
    private function isCalendarMonth(int $month): bool
    {
        return $month >= 1 && $month <= 12;
    }

    /**
     * @param non-empty-string $number
     *
     * @return non-empty-string
     */
    private function layout(string $number): string
    {
        return \sprintf(
            '%s %s %s %s %s %s - %s',
            $number[0],
            \substr($number, 1, 2),
            \substr($number, 3, 2),
            \substr($number, 5, 2),
            \substr($number, 7, 3),
            \substr($number, 10, 3),
            \substr($number, 13),
        );
    }
}
