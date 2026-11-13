<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name (prefixes included) and optional given names: a person known by one name only has it as the family
 * name (Paspoortuitvoeringsregeling art. 23 lid 4). The derived Latin form is FAMILY<<GIVEN, or FAMILY for one name,
 * through the ICAO multinational Latin table (`van der Berg` → `VAN<DER<BERG`, `'t Hart` → `T<HART`, `Ëlla` → `ELLA`).
 * Override policy {@see TransliterationVariant}: no Dutch rule lets a passport keep another Latin spelling, so an
 * override must be another ICAO spelling of the parts; an override equal to the derived form is not kept. See
 * docs/sources/NL.md "Names".
 *
 * @see https://wetten.overheid.nl/BWBR0012811/ Paspoortuitvoeringsregeling Nederland 2001, art. 23 lid 3 and 4
 * @see https://wetten.overheid.nl/BWBR0005212/ Paspoortwet, art. 3 lid 1
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
        public ?GivenName $given,
        ?IcaoPersonName $latin = null,
    ) {
        $builder = new LatinPersonNameBuilder(
            [$family->value],
            $given instanceof GivenName ? [$given->value] : [],
        );
        $this->derived = $builder->derive();
        $this->latin = $builder->resolve($latin, $builder->transliterationVariant(
            'Invalid Dutch person name (the Latin form is not a transliteration of the name).',
        ));
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
     * @return non-empty-string e.g. "Jan Pieter van der Berg"
     */
    #[Override]
    public function full(): string
    {
        return Text::nonEmpty(
            $this->given instanceof GivenName ? $this->given->value . ' ' . $this->family->value : $this->family->value,
        );
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->family->equals($other->family)
            && Optional::equal($this->given, $other->given)
            && Optional::equal($this->latin, $other->latin);
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: string|null, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given?->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }
}
