<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ID;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Nomor Kartu Keluarga: "nomor KK terdiri dari 16 digit angka" (Ditjen Dukcapil). Format only: the national
 * authority publishes no meaning of the digits (a district Dukcapil's page reads them as a region code, an issue
 * date and a serial number, without a legal basis), so none is read. No display form is quoted;
 * `masked()` keeps the last four digits (no official masking rule): `************0001`.
 *
 * @see https://dukcapil.kemendagri.go.id/blog/read/wajib-tahu-nomor-kk-harus-diganti-ketika-kepala-keluarga-meninggal-dunia
 *      Ditjen Dukcapil
 * @see docs/sources/ID.md "Family card number"
 */
final class FamilyCardNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 16;

    private const string DOCUMENT = 'Indonesian family card number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 16, self::DOCUMENT);
    }
}
