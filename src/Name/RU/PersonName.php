<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * Фамилия, имя, отчество (при наличии). The derived Latin form is FAMILY<<GIVEN: the passport for travel abroad
 * omits the patronymic (docs/sources/RU.md §2). Override policy {@see AnyIcaoSpelling}: any valid ICAO name is kept as
 * the Latin form, because the MVD writes the name in Latin "так, как это указано в подтверждающем документе" (an
 * earlier passport may say IVANOVA<<YULIYA for Иванова Юлия; RU.md §4). An override with the derived form's MRZ
 * spelling (`RIMSKII<KORSAKOV<<NIKOLAI` for Римский-Корсаков Николай) is the derived form and is not kept.
 *
 * @see http://publication.pravo.gov.ru/document/0001202002280021 Order of the MVD of Russia No. 996 of 31.12.2019
 *      (Ministry of Justice reg. No. 57627), appendix 9, §1, §3, §16.1.1, §32
 * @see http://publication.pravo.gov.ru/document/0001202105190040 Order of the MVD of Russia No. 186 of 31.03.2021
 *      (Ministry of Justice reg. No. 63515), §139.2, §139.3, §140
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $derived;

    private ?IcaoPersonName $latin;

    /**
     * @throws InvalidPersonNameException when a part has no ICAO spelling or its Latin form is too long
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        public ?Patronymic $patronymic = null,
        ?IcaoPersonName $latin = null,
    ) {
        $transliteration = new RussianTransliteration();
        $this->derived = IcaoPersonName::fromParts(
            $transliteration->toLatin($family),
            [$transliteration->toLatin($given)],
        );
        $this->latin = new AnyIcaoSpelling()->resolve($latin, $this->derived);
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
        return $one instanceof ValueObject
            ? $other instanceof ValueObject && $one->equals($other)
            : !$other instanceof ValueObject;
    }
}
