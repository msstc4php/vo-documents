<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * National Insurance number (NINO), `AB 12 34 56 C`: a two-letter prefix, six digits and the suffix `A`-`D`. HMRC
 * excludes `D F I Q U V` from both prefix letters, `O` from the second, and the prefixes `BG GB KN NK NT TN ZZ`, so
 * HMRC's own illustration `QQ 12 34 56 A` is refused. There is no check digit. `masked()` keeps the last four
 * characters of `formatted()` (no official masking rule): `** ** *4 56 C`.
 *
 * @see https://www.gov.uk/hmrc-internal-manuals/national-insurance-manual/nim39110 HMRC, National Insurance Manual,
 *      NIM39110
 * @see docs/sources/GB.md "National Insurance number"
 */
final class NationalInsuranceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'British National Insurance number';

    private const string EXCLUDED_LETTERS = 'DFIQUV';

    private const array EXCLUDED_PREFIXES = ['BG', 'GB', 'KN', 'NK', 'NT', 'TN', 'ZZ'];

    /**
     * @return non-empty-string e.g. "AB 12 34 56 C"
     */
    public function formatted(): string
    {
        return $this->layout($this->value);
    }

    #[Override]
    public function masked(): string
    {
        return $this->layout(Mask::keepLastCharacters(Text::nonEmpty($this->value), 4));
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        // The value has exactly nine characters, so the pattern spans it.
        if (\preg_match('/[A-Z]{2}[0-9]{6}[A-D]/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        $prefix = \substr($number, 0, 2);
        if (\strpbrk($prefix, self::EXCLUDED_LETTERS) !== false
            || $prefix[1] === 'O'
            || \in_array($prefix, self::EXCLUDED_PREFIXES, true)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (prefix).', self::DOCUMENT));
        }

        return $number;
    }

    /**
     * @return non-empty-string
     */
    private function layout(string $value): string
    {
        return \sprintf(
            '%s %s %s %s %s',
            \substr($value, 0, 2),
            \substr($value, 2, 2),
            \substr($value, 4, 2),
            \substr($value, 6, 2),
            \substr($value, 8),
        );
    }
}
