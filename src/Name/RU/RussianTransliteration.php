<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;

/**
 * The Russian passport rule on top of ICAO table 6.B: the MVD table has the same spelling for every Russian letter
 * and no row for Ь, which is therefore omitted (Игорь → IGOR). See docs/sources/RU.md §3.
 *
 * @see http://publication.pravo.gov.ru/document/0001202002280021 Order of the MVD of Russia No. 996 of 31.12.2019
 *      (Ministry of Justice reg. No. 57627), appendix 9, "Транслитерация кириллических знаков (извлечение)"
 *
 * @internal
 */
final readonly class RussianTransliteration
{
    private const array SOFT_SIGNS = ['Ь', 'ь'];

    public function __construct(
        private Transliterator $transliterator = new Transliterator(),
    ) {
    }

    /**
     * @return non-empty-string
     *
     * @throws InvalidPersonNameException
     */
    public function toLatin(AbstractNamePart $part): string
    {
        return $this->transliterator->toLatin(\str_replace(self::SOFT_SIGNS, '', $part->value));
    }
}
