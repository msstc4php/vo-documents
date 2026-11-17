<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * 氏名 and their 振り仮名. The derived Latin form is the MOFA Hepburn spelling of the readings ({@see PassportHepburn}),
 * FAMILY<<GIVEN. Override policy {@see AnyIcaoSpelling}: MOFA writes a non-Hepburn spelling (a foreign spelling on
 * documents, long vowels as OH: ITOH, OHNO) on request, so any valid ICAO name is kept; one equal to the derived form
 * is not kept. A reading without a Hepburn spelling needs the override, and so does a non-long-vowel おう/うう/おお
 * across a morpheme boundary, e.g. 井上 INOUE, 大内 OUCHI (derived INOE, OCHI). See docs/sources/JP.md §3-§4.
 *
 * @see https://www.ezairyu.mofa.go.jp/passport/hebon.html 外務省, 【ヘボン式によらないローマ字氏名表記】
 */
final readonly class PersonName implements NationalPersonName
{
    private IcaoPersonName $effective;

    private ?IcaoPersonName $override;

    /**
     * @throws InvalidPersonNameException when a reading has no Hepburn spelling or the Latin form is too long, and
     *                                    no override is given
     */
    public function __construct(
        public FamilyName $family,
        public GivenName $given,
        public FamilyNameReading $familyReading,
        public GivenNameReading $givenReading,
        ?IcaoPersonName $latinOverride = null,
    ) {
        $hepburn = new PassportHepburn();
        $form = new AnyIcaoSpelling()->effective(
            static fn (): IcaoPersonName => IcaoPersonName::fromParts(
                $hepburn->toLatin($familyReading),
                [$hepburn->toLatin($givenReading)],
            ),
            $latinOverride,
        );
        $this->effective = $form->effective;
        $this->override = $form->override;
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->effective;
    }

    /**
     * The Latin form given to the constructor, null when {@see latin()} is derived (also when the given form was the
     * derived one).
     */
    public function latinOverride(): ?IcaoPersonName
    {
        return $this->override;
    }

    /**
     * @return non-empty-string e.g. "佐藤 太郎"
     */
    #[Override]
    public function full(): string
    {
        return $this->family->value . ' ' . $this->given->value;
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->family->equals($other->family)
            && $this->given->equals($other->given)
            && $this->familyReading->equals($other->familyReading)
            && $this->givenReading->equals($other->givenReading)
            && Optional::equal($this->override, $other->override);
    }

    /**
     * A view, not the storage payload ({@see PersonNameCodec}): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: string, familyReading: string, givenReading: string, effectiveLatin: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given->value,
            'familyReading' => $this->familyReading->value,
            'givenReading' => $this->givenReading->value,
            'effectiveLatin' => $this->effective->value,
        ];
    }
}
