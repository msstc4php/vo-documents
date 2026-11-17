<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\KR;

use Override;

/**
 * The family name in Hangul (`김`, `남궁`); a name with no family/given split (a foreigner's name kept after
 * naturalisation) is stored whole here, with a null given name. See {@see AbstractKoreanNamePart} and
 * docs/sources/KR.md "Names".
 */
final class FamilyName extends AbstractKoreanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Korean family name';
    }
}
