<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CH;

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
 * Amtlicher Name and optional given names: a person without a given name has `***` in the document's given-name field
 * (SR 143.111 Art. 5 Abs. 4), here `null`. The derived Latin form is FAMILY<<GIVEN, or FAMILY without given names,
 * through the ICAO multinational Latin table (`Müller` → `MUELLER`, `Schweizer Sample` → `SCHWEIZER<SAMPLE`).
 * Override policy {@see TransliterationVariant}: no Swiss rule lets a passport keep another Latin spelling, so an
 * override must be another ICAO spelling of the parts; an override equal to the derived form is not kept. See
 * docs/sources/CH.md "Names".
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111, Art. 4 and 5
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
            'Invalid Swiss person name (the Latin form is not a transliteration of the name).',
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
     * @return non-empty-string e.g. "Helvetia Schweizer Sample"
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
