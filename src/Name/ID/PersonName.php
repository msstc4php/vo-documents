<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ID;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * An Indonesian name as Dukcapil records it, one "Nama Lengkap", with the Latin form of the passport, which is
 * mandatory: no Imigrasi rule for splitting a name into the primary and secondary identifier was found, so none is
 * guessed and the form is never derived. It is not checked against the recorded name beyond being a valid ICAO
 * personal name. Equality includes the Latin form. See docs/sources/ID.md "Names".
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
     * @return array{name: string, latin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return ['name' => $this->name->value, 'latin' => $this->latin->value];
    }
}
