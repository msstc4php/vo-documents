<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * An Indian name in Devanagari, given name first with an optional surname, and the Latin form of the passport, which is
 * mandatory: the passport spelling is a personal choice and is never derived. It is not checked against the
 * Devanagari name. A person who uses no surname has a null surname and the whole name in the given part. Equality
 * includes the Latin form. See docs/sources/IN.md "Names".
 */
final readonly class PersonName implements NationalPersonName
{
    public function __construct(
        public GivenName $given,
        public ?Surname $surname,
        private IcaoPersonName $latin,
    ) {
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->latin;
    }

    /**
     * @return non-empty-string e.g. "पीयूष कुमार जैन"
     */
    #[Override]
    public function full(): string
    {
        return Text::nonEmpty(
            $this->surname instanceof Surname
                ? $this->given->value . ' ' . $this->surname->value
                : $this->given->value,
        );
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->given->equals($other->given)
            && Optional::equal($this->surname, $other->surname)
            && $this->latin->equals($other->latin);
    }

    /**
     * @return array{given: string, surname: string|null, latin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'given' => $this->given->value,
            'surname' => $this->surname?->value,
            'latin' => $this->latin->value,
        ];
    }
}
