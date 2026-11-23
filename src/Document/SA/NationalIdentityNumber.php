<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\SA;

use Override;

/**
 * The number of a Saudi citizen's national ID card (الهوية الوطنية): ten digits, the first being 1 by ZATCA's rule
 * BR-KSA-F-10 for the scheme "NAT" (a warning there; not checked here). The driving licence carries the same number and
 * has no class of its own. No display form is quoted, so there is no `formatted()`. See docs/sources/SA.md "National ID
 * and iqama".
 */
final class NationalIdentityNumber extends AbstractIdentityNumber
{
    #[Override]
    protected function document(): string
    {
        return 'Saudi Arabian national identity number';
    }
}
