<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\ReparsesThroughCodec;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use Override;

/**
 * A Saudi name in Arabic, as written in the civil register: the given name, then the father's, the grandfather's and
 * the family name, each optional beyond the given name (a recorded name may have fewer parts, and no rule is enforced
 * on them), with the mandatory Latin form of the passport, as in {@see AbstractRequiredLatinPersonName}. See
 * docs/sources/SA.md "Names".
 *
 * @phpstan-type SaPersonNameShape array{
 *     given: string,
 *     father: string|null,
 *     grandfather: string|null,
 *     family: string|null,
 *     latin: string,
 * }
 */
final readonly class PersonName implements NationalPersonName
{
    use ReparsesThroughCodec;

    public function __construct(
        public GivenName $given,
        public ?FatherName $father,
        public ?GrandfatherName $grandfather,
        public ?FamilyName $family,
        private IcaoPersonName $latin,
    ) {
    }

    #[Override]
    public function latin(): IcaoPersonName
    {
        return $this->latin;
    }

    /**
     * @return non-empty-string e.g. "محمد عبد الله سعد آل سعود"
     */
    #[Override]
    public function full(): string
    {
        $parts = [$this->given->value];
        foreach ([$this->father, $this->grandfather, $this->family] as $part) {
            if ($part !== null) {
                $parts[] = $part->value;
            }
        }

        return Text::nonEmpty(\implode(' ', $parts));
    }

    #[Override]
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->given->equals($other->given)
            && Optional::equal($this->father, $other->father)
            && Optional::equal($this->grandfather, $other->grandfather)
            && Optional::equal($this->family, $other->family)
            && $this->latin->equals($other->latin);
    }

    /**
     * @return SaPersonNameShape
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'given' => $this->given->value,
            'father' => $this->father?->value,
            'grandfather' => $this->grandfather?->value,
            'family' => $this->family?->value,
            'latin' => $this->latin->value,
        ];
    }

    #[Override]
    protected static function payloadCodec(): PersonNameCodec
    {
        return new PersonNameCodec();
    }
}
