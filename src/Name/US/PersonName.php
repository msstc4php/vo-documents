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
 * Last name, first name, optional middle name and optional suffix. The derived Latin form is
 * LAST<<FIRST<MIDDLE<SUFFIX through the ICAO multinational Latin table (`O'Brien, José Luis, 3RD` →
 * `OBRIEN<<JOSE<LUIS<III`): diacritics dropped as the passport crosses them out, the suffix as
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
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or $latin is
     *                                    not a transliteration of the parts
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        public ?MiddleName $middle = null,
        public ?NameSuffix $suffix = null,
        ?IcaoPersonName $latin = null,
    ) {
        parent::__construct([$family->value], $this->secondaryParts($given, $middle, $suffix), $latin);
    }

    /**
     * @return non-empty-string e.g. "José Luis O'Brien III"
     */
    #[Override]
    public function full(): string
    {
        return \implode(' ', [
            $this->given->value,
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
            && $this->given->equals($other->given)
            && Optional::equal($this->middle, $other->middle)
            && $this->suffix === $other->suffix
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: string, middle: string|null, suffix: string|null, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given->value,
            'middle' => $this->middle?->value,
            'suffix' => $this->suffix?->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid American person name (the Latin form is not a transliteration of the name).',
        );
    }

    /**
     * @return non-empty-list<string>
     */
    private function secondaryParts(GivenName $given, ?MiddleName $middle, ?NameSuffix $suffix): array
    {
        return [
            $given->value,
            ...($middle instanceof MiddleName ? [$middle->value] : []),
            ...($suffix instanceof NameSuffix ? [$suffix->icao()] : []),
        ];
    }
}
