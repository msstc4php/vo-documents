<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * Фамилия, имя, отчество (при наличии). The derived Latin form is FAMILY<<GIVEN: the passport for travel abroad
 * omits the patronymic. A Latin override must be an ICAO spelling of the family name, the given name and, optionally,
 * the patronymic (docs/sources/RU.md §2, §4); an MRZ-style `<` for a hyphen of the national name is accepted and the
 * override is stored with the hyphen.
 *
 * @see http://publication.pravo.gov.ru/document/0001202002280021 Order of the MVD of Russia No. 996 of 31.12.2019
 *      (Ministry of Justice reg. No. 57627), appendix 9, §1, §3, §16.1.1
 * @see http://publication.pravo.gov.ru/document/0001202105190040 Order of the MVD of Russia No. 186 of 31.03.2021
 *      (Ministry of Justice reg. No. 63515), §139.2, §139.3
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $derived;

    private ?IcaoPersonName $latin;

    /**
     * @throws InvalidPersonNameException when $latin is not a transliteration of the parts
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        public ?Patronymic $patronymic = null,
        ?IcaoPersonName $latin = null,
    ) {
        $transliteration = new RussianTransliteration();
        $families = $transliteration->variants($family);
        $givens = $transliteration->variants($given);
        $this->derived = IcaoPersonName::fromParts($families[0], [$givens[0]]);

        if (!$latin instanceof IcaoPersonName) {
            $this->latin = null;

            return;
        }

        $forms = [[$families, $givens]];
        if ($patronymic instanceof Patronymic) {
            $forms[] = [$families, $givens, $transliteration->variants($patronymic)];
        }

        $this->latin = LatinSpelling::find($latin, $forms)
            ?? throw new InvalidPersonNameException('Invalid Russian person name (the Latin form is not a transliteration of the name).');
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

    #[Override]
    public function full(): string
    {
        return $this->family->value . ' ' . $this->given->value
            . ($this->patronymic instanceof Patronymic ? ' ' . $this->patronymic->value : '');
    }

    /**
     * @return non-empty-string e.g. "Иванов И. И."
     */
    public function short(): string
    {
        return $this->family->value . ' ' . $this->initials();
    }

    /**
     * @return non-empty-string e.g. "И. И. Иванов"
     */
    public function initialsFirst(): string
    {
        return $this->initials() . ' ' . $this->family->value;
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->family->equals($other->family)
            && $this->given->equals($other->given)
            && $this->sameOrBothNull($this->patronymic, $other->patronymic)
            && $this->sameOrBothNull($this->latin, $other->latin);
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: string, patronymic: string|null, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given->value,
            'patronymic' => $this->patronymic?->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }

    /**
     * @return non-empty-string
     */
    private function initials(): string
    {
        return $this->initialsOf($this->given)
            . ($this->patronymic instanceof Patronymic ? ' ' . $this->initialsOf($this->patronymic) : '');
    }

    /**
     * @return non-empty-string "Анна-Мария" → "А.-М.", "Гусейн оглы" → "Г. о."
     */
    private function initialsOf(AbstractNamePart $part): string
    {
        $words = [];
        foreach (\explode(' ', $part->value) as $word) {
            $words[] = \implode('-', \array_map(
                static fn (string $piece): string => \mb_substr($piece, 0, 1) . '.',
                \explode('-', $word),
            ));
        }

        return \implode(' ', $words);
    }

    private function sameOrBothNull(?ValueObject $one, ?ValueObject $other): bool
    {
        return $one instanceof ValueObject ? $other instanceof ValueObject && $one->equals($other) : !$other instanceof ValueObject;
    }
}
