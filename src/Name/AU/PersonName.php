<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\AU;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Zoë` → `ZOE`, `Müller` → `MUELLER`). Override policy {@see TransliterationVariant}: a Latin override must be
 * another ICAO spelling of both parts (`MULLER<<ZOE`), an MRZ-style `<` for a hyphen is stored as the hyphen, and an
 * override equal to the derived form is not kept. See docs/sources/AU.md "Names".
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $derived;

    private ?IcaoPersonName $latin;

    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling or $latin is not a transliteration of the
     *                                    parts
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        ?IcaoPersonName $latin = null,
    ) {
        $transliterator = new Transliterator();
        $this->derived = IcaoPersonName::fromParts(
            $transliterator->toLatin($family->value),
            [$transliterator->toLatin($given->value)],
        );

        $policy = new TransliterationVariant(
            static fn (): array => [[
                $transliterator->variants($family->value),
                $transliterator->variants($given->value),
            ]],
            'Invalid Australian person name (the Latin form is not a transliteration of the name).',
        );
        $this->latin = $policy->resolve($latin, $this->derived);
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->latin ?? $this->derived;
    }

    /**
     * The Latin form given to the constructor, null when {@see latin()} is derived (also when the given form was the
     * derived one).
     */
    public function latinOverride(): ?IcaoPersonName
    {
        return $this->latin;
    }

    /**
     * @return non-empty-string e.g. "Joan Catherine Citizen"
     */
    #[Override]
    public function full(): string
    {
        return $this->given->value . ' ' . $this->family->value;
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->family->equals($other->family)
            && $this->given->equals($other->given)
            && ($this->latin instanceof IcaoPersonName
                ? $other->latin instanceof IcaoPersonName && $this->latin->equals($other->latin)
                : !$other->latin instanceof IcaoPersonName);
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: string, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }
}
