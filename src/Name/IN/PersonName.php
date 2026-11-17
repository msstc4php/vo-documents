<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * A name in Latin letters as the Passport Seva form takes it: the given name, then an optional surname ("In case you do
 * not use a surname - leave the Surname column blank and write your full name in Applicant's Given Name column"). The
 * derived ICAO form is `JAIN<<PIYUSH<KUMAR`; a person with no surname has the given name as the only identifier
 * (`RAJESH<KUMAR`), the Doc 9303 rule for a single name. Override policy {@see TransliterationVariant}: no MEA text
 * read lets a passport keep another spelling, so an override must be another ICAO spelling of the parts. See
 * docs/sources/IN.md "Names".
 *
 * @see https://www.passportindia.gov.in/AppOnlineProject/pdf/ApplicationformInstructionBooklet-V3.0.pdf Column 2.1
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6
 */
final readonly class PersonName extends AbstractLatinPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or $latin is
     *                                    not a transliteration of the parts
     */
    public function __construct(
        public GivenName $given,
        public ?Surname $surname = null,
        ?IcaoPersonName $latin = null,
    ) {
        if (!$surname instanceof Surname) {
            parent::__construct([$given->value], [], $latin);

            return;
        }

        parent::__construct([$surname->value], [$given->value], $latin);
    }

    /**
     * @return non-empty-string e.g. "Piyush Kumar Jain"
     */
    #[Override]
    public function full(): string
    {
        return IndianFullName::of($this->given, $this->surname);
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->given->equals($other->given)
            && Optional::equal($this->surname, $other->surname)
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{given: string, surname: string|null, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'given' => $this->given->value,
            'surname' => $this->surname?->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid Indian person name (the Latin form is not a transliteration of the name).',
        );
    }
}
