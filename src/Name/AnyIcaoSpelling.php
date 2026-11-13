<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use Override;

/**
 * Any valid ICAO name is accepted as the override, for countries whose passport authority writes the Latin name as an
 * earlier document spells it, whatever the transliteration table gives (RU: "так, как это указано в подтверждающем
 * документе", docs/sources/RU.md §4).
 *
 * @internal
 */
final readonly class AnyIcaoSpelling extends LatinOverridePolicy
{
    /**
     * @throws InvalidPersonNameException $reason when no override is given
     */
    #[Override]
    public function withoutDerivedForm(?IcaoPersonName $override, InvalidPersonNameException $reason): IcaoPersonName
    {
        return $override ?? throw $reason;
    }

    #[Override]
    protected function spelling(IcaoPersonName $override): IcaoPersonName
    {
        return $override;
    }
}
