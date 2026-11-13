<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A licence number checked against its jurisdiction's published shape after the separators are removed and ASCII
 * letters upper-cased; no jurisdiction publishes a check digit. `masked()` (no official rule) hides every character
 * but the last min(3, ⌊length / 2⌋): `*******890`, `*2`.
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence" (Document Verification Service field rules per state and territory)
 * @see docs/sources/AU.md "Driving licence numbers"
 */
abstract class AbstractDriverLicenceNumber extends AbstractStringValueObject implements DriverLicenceNumber
{
    private const int MAX_VISIBLE = 3;

    #[Override]
    final public function masked(): string
    {
        $value = Digits::nonEmpty($this->value);

        return Mask::keepLastCharacters($value, \min(self::MAX_VISIBLE, \intdiv(\strlen($value), 2)));
    }

    /**
     * @return non-empty-string a regex matching the whole normalised number, e.g. [0-9]{1,10}
     */
    abstract protected static function format(): string;

    #[Override]
    final protected function parse(string $value): string
    {
        $normalized = \strtoupper(Digits::strip($value));

        if (\preg_match('/^(?:' . static::format() . ')$/D', $normalized) !== 1) {
            throw Digits::invalidLength($normalized, $this->jurisdiction()->isoCode() . ' driver licence number');
        }

        return $normalized;
    }
}
