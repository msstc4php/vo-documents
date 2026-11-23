<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Medicare Beneficiary Identifier (MBI), `1EG4-TE5-MK73`: 11 characters, randomly generated and "non-intelligent", in
 * the CMS position layout (1: 1-9; 2, 5, 8, 9: a letter; 3, 6: a letter or a digit; 4, 7, 10, 11: a digit), the
 * letters A-Z without S, L, O, I, B and Z. Lower case is upper-cased and the display dashes are dropped, as CMS
 * does. There is no check digit. `formatted()` is the CMS display form; `masked()` keeps the last four characters in
 * it (no official masking rule): `****-***-MK73`.
 *
 * @see https://www.cms.gov/medicare/new-medicare-card/understanding-the-mbi.pdf CMS, "Understanding the Medicare
 *      Beneficiary Identifier (MBI) Format"
 * @see docs/sources/US.md "Medicare Beneficiary Identifier"
 */
final class MedicareBeneficiaryIdentifier extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 11;

    private const string DOCUMENT = 'American Medicare Beneficiary Identifier';

    private const string LETTER = '[ACDEFGHJKMNPQRTUVWXY]';

    private const string LETTER_OR_DIGIT = '[0-9ACDEFGHJKMNPQRTUVWXY]';

    /**
     * @return non-empty-string e.g. "1EG4-TE5-MK73"
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
        $number = new NumberFormat('0-9A-Z', 11, 11)->parse($value, self::DOCUMENT);
        $pattern = '/^[1-9]' . self::LETTER . self::LETTER_OR_DIGIT . '[0-9]' . self::LETTER . self::LETTER_OR_DIGIT
            . '[0-9]' . self::LETTER . self::LETTER . '[0-9][0-9]$/';
        if (\preg_match($pattern, $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }

    /**
     * @return non-empty-string
     */
    private function layout(string $value): string
    {
        return \sprintf('%s-%s-%s', \substr($value, 0, 4), \substr($value, 4, 3), \substr($value, 7));
    }
}
