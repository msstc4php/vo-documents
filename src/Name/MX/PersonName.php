<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * First surname, second surname and given names, as RENAPO captures them: two surnames, one ("solo tiene un
 * apellido", `$second` null) or none ("no cuenta con apellidos", both null; a second surname without a first is
 * refused). The derived Latin form has the surnames joined by a space as the primary identifier and the given names
 * as the secondary one, through the ICAO multinational Latin table (`Hernández de la Cruz, José Ángel` →
 * `HERNANDEZ<DE<LA<CRUZ<<JOSE<ANGEL`); a person without surnames has the given names as the only identifier
 * (`JUAN`). Override policy {@see TransliterationVariant}: no SRE rule that could be read lets a passport keep another
 * Latin spelling, so an override must be another ICAO spelling of the parts (`NUNXXEZ<<ANA` for `Núñez Ana`). See
 * docs/sources/MX.md "Names".
 *
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021), 5.1, 1.10-1.11
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 */
final readonly class PersonName extends AbstractLatinPersonName
{
    private const string SECOND_WITHOUT_FIRST = 'Invalid Mexican person name (second surname without a first surname).';

    /**
     * @throws InvalidPersonNameException when a second surname comes without a first, a letter has no ICAO spelling,
     *                                    the Latin form is too long (three parts of 100 characters exceed
     *                                    {@see IcaoPersonName::MAX_LENGTH}) or $latin is not a transliteration of
     *                                    the parts
     */
    public function __construct(
        public ?FamilyName $first,
        public ?FamilyName $second,
        public GivenName $given,
        ?IcaoPersonName $latin = null,
    ) {
        if (!$first instanceof FamilyName) {
            if ($second instanceof FamilyName) {
                throw new InvalidPersonNameException(self::SECOND_WITHOUT_FIRST);
            }

            parent::__construct([$given->value], [], $latin);

            return;
        }

        parent::__construct(
            $second instanceof FamilyName ? [$first->value, $second->value] : [$first->value],
            [$given->value],
            $latin,
        );
    }

    /**
     * @return non-empty-string e.g. "José Ángel Hernández de la Cruz"
     */
    #[Override]
    public function full(): string
    {
        return \implode(' ', [
            $this->given->value,
            ...($this->first instanceof FamilyName ? [$this->first->value] : []),
            ...($this->second instanceof FamilyName ? [$this->second->value] : []),
        ]);
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && Optional::equal($this->first, $other->first)
            && Optional::equal($this->second, $other->second)
            && $this->given->equals($other->given)
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{first: string|null, second: string|null, given: string, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'first' => $this->first?->value,
            'second' => $this->second?->value,
            'given' => $this->given->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid Mexican person name (the Latin form is not a transliteration of the name).',
        );
    }
}
