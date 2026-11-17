<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\KR;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use Override;

/**
 * A Korean name in Hangul (`김민준`: family, then given, written together) with the Latin form of the passport, which
 * is mandatory: the romanisation on a passport is the holder's choice (Kim, Gim) and is never derived. It is not
 * checked against the Hangul name. A name with no family/given split (a foreigner's name kept after naturalisation) is
 * stored whole in the family part with a null given name. `full()` joins family and given with no space, also for a
 * naturalised multi-word name split into the two (존슨 + 마이클 gives 존슨마이클); such a name can be stored whole in the
 * family part instead. See docs/sources/KR.md "Names".
 *
 * @extends AbstractRequiredLatinPersonName<FamilyName, GivenName>
 */
final readonly class PersonName extends AbstractRequiredLatinPersonName
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
