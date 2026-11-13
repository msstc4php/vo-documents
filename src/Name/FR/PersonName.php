<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\FR;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Cœur` → `COEUR`, `Lætitia` → `LAETITIA`, `Hélène` → `HELENE`, `D'Artagnan` → `DARTAGNAN`). Override policy
 * {@see TransliterationVariant}: no French text lets a passport keep another Latin spelling, so an override must be
 * another ICAO spelling of both parts (`MULLER<<JOEL` for `Müller Joël`); an MRZ-style `<` for a hyphen is stored as
 * the hyphen, and an override equal to the derived form is not kept. See docs/sources/FR.md "Names".
 *
 * @see https://www.legifrance.gouv.fr/circulaire/id/38565 Circulaire du 23 juillet 2014 relative à l'état civil, § 1
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
        $builder = new LatinPersonNameBuilder([$family->value], [$given->value]);
        $this->derived = $builder->derive();
        $this->latin = $builder->resolve($latin, $builder->transliterationVariant(
            'Invalid French person name (the Latin form is not a transliteration of the name).',
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
     * @return non-empty-string e.g. "Jean-Pierre de La Fontaine"
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
