<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Series and number of the passport of a citizen of the Russian Federation: "Нумерация бланка паспорта состоит из 3
 * групп цифр. Первые две группы, состоящие из 4 цифр, обозначают серию бланка паспорта, третья группа, состоящая из 6
 * цифр, обозначает номер бланка паспорта" (`45 08 123456`). The source defines no check digit and no meaning of the
 * series digits, so the value is validated by its shape only. `masked()` keeps the last four digits of `formatted()`
 * (no official masking rule): `** ** **3456`.
 *
 * @see http://publication.pravo.gov.ru/document/0001202312290097 Government Decree of 23.12.2023 No. 2267, description
 *      of the blank, §3
 * @see docs/sources/RU.md "Internal passport"
 */
final class InternalPassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Russian internal passport number';

    /**
     * @return non-empty-string four digits
     */
    public function series(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 4));
    }

    /**
     * @return non-empty-string six digits
     */
    public function number(): string
    {
        return Text::nonEmpty(\substr($this->value, 4));
    }

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 2), \substr($this->value, 2, 2), $this->number());
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 10, self::DOCUMENT);
    }
}
