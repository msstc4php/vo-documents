<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\ReparsesThroughCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\IndianFullName;
use MSSTC4PHP\ValueObject\Documents\Name\NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use Override;

/**
 * An Indian name in Devanagari, given name first with an optional surname, and the mandatory Latin form of the
 * passport, as in {@see AbstractRequiredLatinPersonName}. A person who uses no surname has a null surname and the
 * whole name in the given part. See docs/sources/IN.md "Names".
 *
 * @phpstan-type InDevanagariPersonNameShape array{given: string, surname: string|null, latin: string}
 */
final readonly class PersonName implements NationalPersonName
{
    use ReparsesThroughCodec;

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
        return IndianFullName::of($this->given, $this->surname);
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
     * @return InDevanagariPersonNameShape
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

    #[Override]
    protected static function payloadCodec(): PersonNameCodec
    {
        return new PersonNameCodec();
    }
}
