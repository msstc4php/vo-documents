<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * Фамилия, имя, отчество (при наличии). The derived Latin form is FAMILY<<GIVEN: the passport for travel abroad
 * omits the patronymic. A Latin override must be an ICAO spelling of the family name, the given name and, optionally,
 * the patronymic (docs/sources/RU.md §2, §4).
 *
 * @see https://base.garant.ru/73680659/e21d36dd59d557f67db63a47391d35b1/ Order of the MVD of Russia No. 996 of
 *      31.12.2019, appendix 9, §1, §3
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $derived;

    /**
     * @throws InvalidPersonNameException when $latin is not a transliteration of the parts
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        public ?Patronymic $patronymic = null,
        private ?IcaoPersonName $latin = null,
    ) {
        $transliteration = new RussianTransliteration();
        $families = $transliteration->variants($family);
        $givens = $transliteration->variants($given);
        $this->derived = IcaoPersonName::fromParts($families[0], [$givens[0]]);

        if ($latin instanceof IcaoPersonName && !\in_array($latin->value, $this->spellings($transliteration, $families, $givens), true)) {
            throw new InvalidPersonNameException('Invalid Russian person name (the Latin form is not a transliteration of the name).');
        }
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
     * @return array{family: string, given: string, patronymic: string|null, latin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given->value,
            'patronymic' => $this->patronymic?->value,
            'latin' => $this->latin()->value,
        ];
    }

    /**
     * @param non-empty-list<non-empty-string> $families
     * @param non-empty-list<non-empty-string> $givens
     *
     * @return list<string> every accepted canonical Latin form
     *
     * @throws InvalidPersonNameException
     */
    private function spellings(RussianTransliteration $transliteration, array $families, array $givens): array
    {
        $patronymics = $this->patronymic instanceof Patronymic ? $transliteration->variants($this->patronymic) : [];
        $spellings = [];
        foreach ($families as $family) {
            foreach ($givens as $given) {
                $spellings[] = IcaoPersonName::fromParts($family, [$given])->value;
                foreach ($patronymics as $patronymic) {
                    $spellings[] = IcaoPersonName::fromParts($family, [$given, $patronymic])->value;
                }
            }
        }

        return $spellings;
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
