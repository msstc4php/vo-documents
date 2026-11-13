<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\AU;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Zoë` → `ZOE`, `Müller` → `MUELLER`); a Latin override must be another ICAO spelling of both parts (`MULLER<<ZOE`)
 * and an MRZ-style `<` for a hyphen is stored as the hyphen. See docs/sources/AU.md "Names".
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
        $families = $transliterator->variants($family->value);
        $givens = $transliterator->variants($given->value);
        $this->derived = IcaoPersonName::fromParts($families[0], [$givens[0]]);

        if (!$latin instanceof IcaoPersonName) {
            $this->latin = null;

            return;
        }

        $this->latin = LatinSpelling::find($latin, [[$families, $givens]])
            ?? throw new InvalidPersonNameException('Invalid Australian person name (the Latin form is not a transliteration of the name).');
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->latin ?? $this->derived;
    }

    /**
     * The Latin form given to the constructor, null when {@see latin()} is derived.
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
