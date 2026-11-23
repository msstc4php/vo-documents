<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Registro Federal de Contribuyentes of a natural person (persona física), 13 characters, as the SAT's CFDI type
 * `t_RFC_PF` states it: four of `A-Z`, `&`, `Ñ`, two digits, the month `01`-`12`, the day `01`-`31`, two letters or
 * digits and a final digit or `A`. Only those ranges are applied (no date check beyond them, no homoclave check: SAT
 * publishes no algorithm). ASCII letters and `ñ` are upper-cased; no other character is case-mapped (`ß` is refused,
 * not read as `SS`). The twelve-character RFC of a legal person is not covered. No display form is published;
 * `masked()` keeps the last four characters (no official masking rule): `*********11A2`.
 *
 * @see http://www.sat.gob.mx/sitio_internet/cfd/tipoDatos/tdCFDI/tdCFDI.xsd SAT, catálogo de tipos de datos del CFDI,
 *      simpleType t_RFC_PF
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/MX.md "RFC"
 */
final class Rfc extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 13;

    private const string DOCUMENT = 'Mexican RFC';

    private const int LENGTH = 13;

    private const string SHAPE = '/^[A-Z&Ñ]{4}[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{2}[0-9A]$/Du';

    #[Override]
    public function masked(): string
    {
        $visible = \substr($this->value, -4);

        return Text::nonEmpty(\str_repeat('*', \mb_strlen($this->value, 'UTF-8') - 4) . $visible);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $rfc = Digits::upperCasedOfLength(
            Digits::composedWithCapitalEnye($value),
            static fn (int $length): bool => $length === self::LENGTH,
            self::DOCUMENT,
        );
        if (\preg_match(self::SHAPE, $rfc) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $rfc;
    }
}
