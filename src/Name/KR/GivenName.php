<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\KR;

use Override;

/**
 * The given name in Hangul (`민준`). See {@see AbstractKoreanNamePart} and docs/sources/KR.md "Names".
 */
final class GivenName extends AbstractKoreanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Korean given name';
    }
}
