<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\GB;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * Surname and optional forenames: a person with one name gets `XXX` as the passport forename, which "does not form
 * part of their name", here `null`. The derived Latin form is SURNAME<<FORENAMES, or SURNAME alone, through the ICAO
 * multinational Latin table (`O'Brien Siân` → `OBRIEN<<SIAN`). Override policy {@see AnyIcaoSpelling}: HM Passport
 * Office writes a spelling shown by an official document or a foreign authority's letter when the table has none the
 * holder uses, so any valid ICAO name is kept as the override; one equal to the derived form is not kept. See
 * docs/sources/GB.md "Names".
 *
 * @see https://www.gov.uk/government/publications/names-names-that-cannot-be-used-in-passports/names-names-that-cannot-be-used-in-passports-accessible
 *      HM Passport Office, Names: names that cannot be used in passports, v19.0, § 2.2
 * @see https://www.gov.uk/government/publications/how-to-show-uncommon-names-in-passports-caseworker-guidance/names-how-to-show-uncommon-names-in-passports
 *      HM Passport Office, Names: how to show uncommon names in passports, v18.0, "When the customer only has one name"
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $derived;

    private ?IcaoPersonName $latin;

    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling
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
        $this->latin = $builder->resolve($latin, new AnyIcaoSpelling());
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
     * @return non-empty-string e.g. "Mary Ann Smith-Jones"
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
