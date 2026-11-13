<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;

/**
 * Derives the ICAO Latin form of a name made of family parts (joined by a space into the primary identifier) and
 * given parts (one secondary identifier each), and applies a country's override policy to it.
 *
 * @internal
 */
final readonly class LatinPersonNameBuilder
{
    private Transliterator $transliterator;

    /**
     * @param list<string> $family
     * @param list<string> $given
     */
    public function __construct(
        private array $family,
        private array $given,
    ) {
        $this->transliterator = new Transliterator();
    }

    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling
     */
    public function derive(): IcaoPersonName
    {
        return IcaoPersonName::fromParts(
            $this->transliterator->toLatin(\implode(' ', $this->family)),
            \array_map($this->transliterator->toLatin(...), $this->given),
        );
    }

    /**
     * Accepts only another ICAO spelling of the parts; the spellings are listed only when an override is given.
     *
     * @param non-empty-string $refusal exception message for an override that is no spelling of the name
     */
    public function transliterationVariant(string $refusal): TransliterationVariant
    {
        return new TransliterationVariant(
            fn (): array => [[
                $this->transliterator->variants(\implode(' ', $this->family)),
                ...\array_map($this->transliterator->variants(...), $this->given),
            ]],
            $refusal,
        );
    }

    /**
     * @return IcaoPersonName|null the override to keep, null when there is none or it is the derived form
     *
     * @throws InvalidPersonNameException when the policy refuses the override
     */
    public function resolve(?IcaoPersonName $override, LatinOverridePolicy $policy): ?IcaoPersonName
    {
        return $policy->resolve($override, $this->derive());
    }
}
