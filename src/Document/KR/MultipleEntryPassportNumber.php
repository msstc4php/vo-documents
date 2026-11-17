<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\KR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the multiple-entry (복수) `M` series that MOFA's guide to the next-generation electronic passport shows, 9
 * characters: `M` and 8 digits (`M12345678`), and since the issue of 2021-12-21 `M`, 3 digits, a letter and 4 digits
 * (`M123A4567`) ("여권번호 체계를 변경(M12345678 → M123A4567)"). The guide names only the `M` series; the numbers of single-use
 * and emergency (단수, 긴급), official, diplomatic and other passports are not published and are refused. The letter's set
 * is not published, so any letter is accepted. No check character is published. `masked()` keeps the last four
 * characters (no official masking rule).
 *
 * @see https://www.passport.go.kr/home/kor/onlineExperience/index.do?menuPos=58 외교부 여권안내
 * @see docs/sources/KR.md "Multiple-entry passport number"
 */
final class MultipleEntryPassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Korean multiple-entry passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('M[0-9]{8}', 'M[0-9]{3}[A-Z][0-9]{4}')->parse($value, self::DOCUMENT);
    }
}
