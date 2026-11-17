<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ID;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use Override;

/**
 * An Indonesian name as Dukcapil records it, one "Nama Lengkap", with the Latin form of the passport, which is
 * mandatory, as in {@see AbstractRequiredLatinPersonName}: no Imigrasi rule for splitting a name into the primary and
 * secondary identifier was found, so none is guessed. See docs/sources/ID.md "Names".
 *
 * @phpstan-type IdPersonNameShape array{name: string, latin: string}
 */
final readonly class PersonName implements NationalPersonName
{
    public function __construct(
        public FullName $name,
        private IcaoPersonName $latin,
    ) {
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->latin;
    }

    /**
     * @return non-empty-string e.g. "Siti Nurhaliza"
     */
    #[Override]
    public function full(): string
    {
        return Text::nonEmpty($this->name->value);
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->name->equals($other->name) && $this->latin->equals($other->latin);
    }

    /**
     * @return IdPersonNameShape
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return ['name' => $this->name->value, 'latin' => $this->latin->value];
    }
}
