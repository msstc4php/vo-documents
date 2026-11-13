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
     * `$forms` gives the accepted forms of the name as {@see LatinSpelling::find()} takes them; `$refusal` is the
     * exception message for an override that is not one of their spellings.
     *
     * @param Closure(): non-empty-list<non-empty-list<non-empty-list<non-empty-string>>> $forms
     * @param non-empty-string $refusal
     */
    public function __construct(
        private Closure $forms,
        private string $refusal,
    ) {
    }

    /**
     * @throws InvalidPersonNameException
     */
    #[Override]
    protected function spelling(IcaoPersonName $override): IcaoPersonName
    {
        return LatinSpelling::find($override, ($this->forms)()) ?? throw new InvalidPersonNameException($this->refusal);
    }
}
