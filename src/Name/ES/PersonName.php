<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * First surname, optional second surname and given name. The derived Latin form has both surnames joined by a space
 * as the primary identifier, through the ICAO multinational Latin table (`García López, María José` →
 * `GARCIA<LOPEZ<<MARIA<JOSE`, `Muñoz` → `MUNOZ`). Override policy {@see TransliterationVariant}: no Spanish rule lets
 * a passport keep another Latin spelling, so an override must be another ICAO spelling of the parts (`ARGUELLES<<ANA`
 * for `Argüelles Ana`); an MRZ-style `<` for a hyphen is stored as the hyphen, and an override equal to the derived
 * form is not kept. See docs/sources/ES.md "Names".
 *
 * @see https://www.boe.es/buscar/pdf/2003/BOE-A-2003-13978-consolidado.pdf RD 896/2003 (pasaporte), art. 10.2 f)
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 */
final readonly class PersonName extends AbstractLatinPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long (three parts
     *                                    of 100 characters exceed {@see IcaoPersonName::MAX_LENGTH}) or $latin is not
     *                                    a transliteration of the parts
     */
    public function __construct(
        public FamilyName $firstSurname,
        public ?FamilyName $secondSurname,
        public GivenName $given,
        ?IcaoPersonName $latin = null,
    ) {
        parent::__construct(
            $secondSurname instanceof FamilyName
                ? [$firstSurname->value, $secondSurname->value]
                : [$firstSurname->value],
            [$given->value],
            $latin,
            AbstractSpanishNamePart::OMITTED_PUNCTUATION,
        );
    }

    /**
     * @return non-empty-string e.g. "María José García López"
     */
    #[Override]
    public function full(): string
    {
        $surnames = $this->secondSurname instanceof FamilyName
            ? $this->firstSurname->value . ' ' . $this->secondSurname->value
            : $this->firstSurname->value;

        return $this->given->value . ' ' . $surnames;
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->firstSurname->equals($other->firstSurname)
            && Optional::equal($this->secondSurname, $other->secondSurname)
            && $this->given->equals($other->given)
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `latin` is {@see latin()}, derived or not.
     *
     * @return array{firstSurname: string, secondSurname: string|null, given: string, latin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'firstSurname' => $this->firstSurname->value,
            'secondSurname' => $this->secondSurname?->value,
            'given' => $this->given->value,
            'latin' => $this->latin()->value,
        ];
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid Spanish person name (the Latin form is not a transliteration of the name).',
        );
    }

    #[Override]
    protected static function payloadCodec(): PersonNameCodec
    {
        return new PersonNameCodec();
    }
}
