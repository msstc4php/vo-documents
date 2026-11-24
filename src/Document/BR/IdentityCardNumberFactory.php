<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\AbstractIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\SpIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

/**
 * Builds the RG number of a given unit and lists the covered units a bare number fits. Only the units with a published
 * format are matched (today BR-SP alone); {@see fromNumberIn()} refuses the 26 other units with "(format not
 * covered)". There is no `fromNumber()`: a unit without a published format could have issued any number, so a single match
 * among the covered ones does not identify the issuer. It returns once every unit is covered.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/BR.md "RG per state — overlap (the factory's test oracle)"
 */
final class IdentityCardNumberFactory
{
    private function __construct()
    {
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the unit's format, or "(format not
     *                                        covered)" for a unit that publishes none
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): IdentityCardNumber
    {
        return self::coveredClassFor($jurisdiction)::from($number);
    }

    /** Null when the number does not fit that jurisdiction; fromNumberIn() says why. */
    public static function tryFromNumberIn(string $number, Jurisdiction $jurisdiction): ?IdentityCardNumber
    {
        try {
            return self::fromNumberIn($number, $jurisdiction);
        } catch (InvalidValueObjectException) {
            return null;
        }
    }

    /**
     * @return list<Jurisdiction> in enum order
     */
    public static function jurisdictionsMatching(string $number): array
    {
        return SubdivisionResolver::matches(self::coveredJurisdictions(), self::tryIn(...), $number);
    }

    /**
     * @return list<Jurisdiction> in enum order
     */
    private static function coveredJurisdictions(): array
    {
        $covered = [];
        foreach (Jurisdiction::cases() as $jurisdiction) {
            if (self::classFor($jurisdiction) !== null) {
                $covered[] = $jurisdiction;
            }
        }

        return $covered;
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?IdentityCardNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
    }

    /**
     * @return class-string<AbstractIdentityCardNumber>
     *
     * @throws InvalidDocumentNumberException "(format not covered)" for a unit that publishes no format
     */
    private static function coveredClassFor(Jurisdiction $jurisdiction): string
    {
        $message = \sprintf('Invalid %s identity card number (format not covered).', $jurisdiction->isoCode());

        return self::classFor($jurisdiction) ?? throw new InvalidDocumentNumberException($message);
    }

    /**
     * @return class-string<AbstractIdentityCardNumber>|null null for a unit with no published format (BR.md "RG per
     *                                                       state — per unit")
     */
    private static function classFor(Jurisdiction $jurisdiction): ?string
    {
        return match ($jurisdiction) {
            Jurisdiction::SP => SpIdentityCardNumber::class,
            Jurisdiction::AC, Jurisdiction::AL, Jurisdiction::AM, Jurisdiction::AP, Jurisdiction::BA,
            Jurisdiction::CE, Jurisdiction::DF, Jurisdiction::ES, Jurisdiction::GO, Jurisdiction::MA,
            Jurisdiction::MG, Jurisdiction::MS, Jurisdiction::MT, Jurisdiction::PA, Jurisdiction::PB,
            Jurisdiction::PE, Jurisdiction::PI, Jurisdiction::PR, Jurisdiction::RJ, Jurisdiction::RN,
            Jurisdiction::RO, Jurisdiction::RR, Jurisdiction::RS, Jurisdiction::SC, Jurisdiction::SE,
            Jurisdiction::TO => null,
        };
    }
}
