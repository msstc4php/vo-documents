<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;

/**
 * The serial number shared by the Personalausweis and the Reisepass: nine characters from
 * `C F G H J K L M N P R T V W X Y Z` and `1-9`. Masks keep the last four characters.
 *
 * @see https://www.gesetze-im-internet.de/pauswv/anlage_3.html PAuswV Anlage 3 Abschnitt 1 Nr. 9
 * @see https://www.gesetze-im-internet.de/passv_2007/anlage_11.html PassV Anlage 11 Nr. 9
 *
 * @internal
 */
final class SerialNumberFormat
{
    private const string ALPHABET = 'CFGHJKLMNPRTVWXYZ1-9';

    private const int LENGTH = 9;

    /**
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException
     */
    public static function parse(string $input, string $document): string
    {
        return new NumberFormat(self::ALPHABET, self::LENGTH, self::LENGTH)->parse($input, $document);
    }

    /**
     * @param non-empty-string $value
     *
     * @return non-empty-string
     */
    public static function masked(string $value): string
    {
        return Mask::keepLastCharacters($value, 4);
    }
}
