<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use Closure;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use Override;

/**
 * The override must be one of the ICAO table spellings of the national name ({@see LatinSpelling}), for countries
 * whose sources allow no other spelling. The spellings are computed only when an override is given.
 *
 * @internal
 */
final readonly class TransliterationVariant extends LatinOverridePolicy
{
    /**
     * `$spellings` gives the primary and the secondary identifiers' spellings as {@see LatinSpelling::find()} takes
     * them; `$refusal` is the exception message for an override that is not one of them.
     *
     * @param Closure(): array{IdentifierSpellings, IdentifierSpellings|null} $spellings
     * @param non-empty-string $refusal
     */
    public function __construct(
        private Closure $spellings,
        private string $refusal,
    ) {
    }

    /**
     * @throws InvalidPersonNameException
     */
    #[Override]
    protected function spelling(IcaoPersonName $override): IcaoPersonName
    {
        [$primary, $secondary] = ($this->spellings)();

        return LatinSpelling::find($override, $primary, $secondary)
            ?? throw new InvalidPersonNameException($this->refusal);
    }
}
