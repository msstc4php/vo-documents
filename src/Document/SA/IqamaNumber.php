<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\SA;

use Override;

/**
 * The number of a resident's iqama (الإقامة): ten digits, the first being 2 by ZATCA's rule BR-KSA-F-11 for the scheme
 * "IQA" (a warning there; not checked here). The driving licence carries the same number and has no class of its own.
 * No display form is quoted, so there is no `formatted()`. See docs/sources/SA.md "National ID and iqama".
 */
final class IqamaNumber extends AbstractIdentityNumber
{
    public const int MAX_LENGTH = 10;

    #[Override]
    protected function document(): string
    {
        return 'Saudi Arabian iqama number';
    }
}
