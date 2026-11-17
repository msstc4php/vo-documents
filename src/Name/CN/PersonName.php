<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CN;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractFamilyGivenPersonName;
use Override;

/**
 * A Chinese name in Han characters (`张伟`: family, then given, written together) with the Latin form of the passport,
 * which is mandatory: the pinyin spelling on a passport is the holder's choice and is never derived (Cantonese,
 * Hokkien and Wade-Giles spellings are common). It is not checked against the Han name. A name with no family/given
 * split (a minority-nationality name such as `阿沛·阿旺晋美`, a Tibetan name) is stored whole in the family part with a
 * null given name, like the single 姓名 field of the ID card. See docs/sources/CN.md "Names".
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    public function __construct(FamilyName $family, ?GivenName $given, IcaoPersonName $latin)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function nationalSeparator(): string
    {
        return '';
    }
}
