<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\TR;

use Override;

/**
 * Soyadı, letter case kept (`Öztürk`); a married woman may carry her former surname with her husband's (`Yılmaz
 * Kaya`). See {@see AbstractTurkishNamePart} and docs/sources/TR.md "Names".
 *
 * @see https://www.mevzuat.gov.tr/MevzuatMetin/21.5.2505.pdf Nüfus Hizmetleri Uygulama Yönetmeliği, Madde 24
 */
final class FamilyName extends AbstractTurkishNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Turkish family name';
    }
}
