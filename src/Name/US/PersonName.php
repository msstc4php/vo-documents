<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Last name, optional first name, optional middle name and optional suffix. A person with one name has it as the last
 * name and no first name (8 FAM 403.1-3(D): "one-word names be printed in the last name field"); a middle name or a
 * suffix without a first name is refused (no source defines that form). The derived Latin form is
 * LAST<<FIRST<MIDDLE<SUFFIX, or LAST alone for one name, through the ICAO multinational Latin table (`O'Brien, José
 * Luis, 3RD` → `OBRIEN<<JOSE<LUIS<III`): diacritics dropped as the passport crosses them out, the suffix as
 * {@see NameSuffix::icao()}. Override policy {@see TransliterationVariant}: the State Department spells a Latin-script
 * name from the applicant's evidence and only crosses diacritics out, which is a table spelling, so an override must
 * be another ICAO spelling of the parts (`MULLER<<ANNA`, the diacritic crossed out, for `Müller Anna`). A passport
 * without the suffix (8 FAM 403.1-5(B): it "may be added or dropped") is a name without {@see $suffix}. See
 * docs/sources/US.md "Names".
 *
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-3, 403.1-5(B)
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 */
final readonly class PersonName extends AbstractLatinPersonName
{
    /**
     * @throws InvalidPersonNameException when a middle name or a suffix comes without a given name, a letter has no
     *                                    ICAO spelling, the Latin form is too long or $latin is not a transliteration
     *                                    of the parts
     */
    public function __construct(
        public FamilyName $family,
        public ?GivenName $given,
        public ?MiddleName $middle = null,
        public ?NameSuffix $suffix = null,
        ?IcaoPersonName $latin = null,
    ) {
        if (!$given instanceof GivenName && $middle instanceof MiddleName) {
            throw new InvalidPersonNameException('Invalid American person name (middle name without a given name).');
        }

        if (!$given instanceof GivenName && $suffix instanceof NameSuffix) {
            throw new InvalidPersonNameException('Invalid American person name (suffix without a given name).');
        }

        parent::__construct([$family->value], $this->secondaryParts($given, $middle, $suffix), $latin);
    }

    /**
     * @return non-empty-string e.g. "José Luis O'Brien 3RD"
     */
    #[Override]
    public function full(): string
    {
        return \implode(' ', [
            ...($this->given instanceof GivenName ? [$this->given->value] : []),
            ...($this->middle instanceof MiddleName ? [$this->middle->value] : []),
            $this->family->value,
            ...($this->suffix instanceof NameSuffix ? [$this->suffix->value] : []),
        ]);
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->family->equals($other->family)
            && Optional::equal($this->given, $other->given)
            && Optional::equal($this->middle, $other->middle)
            && $this->suffix === $other->suffix
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `latin` is {@see latin()}, derived or not.
     *
     * @return array{
     *     family: string,
     *     given: string|null,
     *     middle: string|null,
     *     suffix: string|null,
     *     latin: string,
     * }
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given?->value,
            'middle' => $this->middle?->value,
            'suffix' => $this->suffix?->value,
            'latin' => $this->latin()->value,
        ];
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid American person name (the Latin form is not a transliteration of the name).',
        );
    }

    #[Override]
    protected static function payloadCodec(): PersonNameCodec
    {
        return new PersonNameCodec();
    }

    /**
     * @return list<string> empty for a person with one name
     */
    private function secondaryParts(?GivenName $given, ?MiddleName $middle, ?NameSuffix $suffix): array
    {
        return [
            ...($given instanceof GivenName ? [$given->value] : []),
            ...($middle instanceof MiddleName ? [$middle->value] : []),
            ...($suffix instanceof NameSuffix ? [$suffix->icao()] : []),
        ];
    }
}
