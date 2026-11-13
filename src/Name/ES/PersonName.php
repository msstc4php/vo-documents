<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * First surname, optional second surname and given name. The derived Latin form has both surnames joined by a space
 * as the primary identifier, through the ICAO multinational Latin table (`García López, María José` →
 * `GARCIA<LOPEZ<<MARIA<JOSE`, `Muñoz` → `MUNOZ`). Override policy {@see TransliterationVariant}: no Spanish rule lets
 * a passport keep another Latin spelling, so an override must be another ICAO spelling of the parts (`ARGUELLES<<ANA`
 * for `Argüelles Ana`); an override equal to the derived form is not kept. See docs/sources/ES.md "Names".
 *
 * @see https://www.boe.es/buscar/pdf/2003/BOE-A-2003-13978-consolidado.pdf RD 896/2003 (pasaporte), art. 10.2 f)
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
        public FamilyName $first,
        public ?FamilyName $second,
        public GivenName $given,
        ?IcaoPersonName $latin = null,
    ) {
        $family = $second instanceof FamilyName ? [$first->value, $second->value] : [$first->value];
        $builder = new LatinPersonNameBuilder($family, [$given->value]);
        $this->derived = $builder->derive();
        $this->latin = $builder->resolve($latin, $builder->transliterationVariant(
            'Invalid Spanish person name (the Latin form is not a transliteration of the name).',
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
     * @return non-empty-string e.g. "María José García López"
     */
    #[Override]
    public function full(): string
    {
        $surnames = $this->second instanceof FamilyName
            ? $this->first->value . ' ' . $this->second->value
            : $this->first->value;

        return $this->given->value . ' ' . $surnames;
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->first->equals($other->first)
            && $this->sameOptional($this->second, $other->second)
            && $this->given->equals($other->given)
            && $this->sameOptional($this->latin, $other->latin);
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{first: string, second: string|null, given: string, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'first' => $this->first->value,
            'second' => $this->second?->value,
            'given' => $this->given->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }

    private function sameOptional(?ValueObject $one, ?ValueObject $other): bool
    {
        if (!$one instanceof ValueObject) {
            return !$other instanceof ValueObject;
        }

        return $other instanceof ValueObject && $one->equals($other);
    }
}
