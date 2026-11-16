<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\AbstractIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\SpIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the RG number of the unit a bare number belongs to, and never picks one of several. Only the units with a
 * published format are matched (today only BR-SP); the other 26 have no class, so a bare RG from them may be read as
 * BR-SP: callers that know the issuer use {@see fromNumberIn()}.
 *
 * @see docs/sources/BR.md "RG per state — overlap (the factory's test oracle)"
 */
final class IdentityCardNumberFactory
{
    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several units match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits a unit,
     *                                        otherwise the length
     */
    public static function fromNumber(string $number): IdentityCardNumber
    {
        return self::resolver()->resolve(self::withoutDots($number));
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the unit's format, or "(format not
     *                                        covered)" for a unit that publishes none
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): IdentityCardNumber
    {
        return self::coveredClassFor($jurisdiction)::from($number);
    }

    /**
     * @return list<Jurisdiction> in enum order
     */
    public static function jurisdictionsMatching(string $number): array
    {
        return self::resolver()->matching(self::withoutDots($number));
    }

    /**
     * Dots are separators of the printed RG; removing them here keeps the length in the resolver's messages right.
     */
    private static function withoutDots(string $number): string
    {
        return \str_replace('.', '', $number);
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

    /**
     * @return SubdivisionResolver<Jurisdiction, IdentityCardNumber>
     */
    private static function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            'Brazilian identity card',
            self::coveredJurisdictions(),
            self::tryIn(...),
            self::fitsLengthIn(...),
        );
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?IdentityCardNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
    }

    private static function fitsLengthIn(Jurisdiction $jurisdiction, string $number): bool
    {
        return self::coveredClassFor($jurisdiction)::fitsLength($number);
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
            Jurisdiction::Sp => SpIdentityCardNumber::class,
            Jurisdiction::Ac, Jurisdiction::Al, Jurisdiction::Am, Jurisdiction::Ap, Jurisdiction::Ba,
            Jurisdiction::Ce, Jurisdiction::Df, Jurisdiction::Es, Jurisdiction::Go, Jurisdiction::Ma,
            Jurisdiction::Mg, Jurisdiction::Ms, Jurisdiction::Mt, Jurisdiction::Pa, Jurisdiction::Pb,
            Jurisdiction::Pe, Jurisdiction::Pi, Jurisdiction::Pr, Jurisdiction::Rj, Jurisdiction::Rn,
            Jurisdiction::Ro, Jurisdiction::Rr, Jurisdiction::Rs, Jurisdiction::Sc, Jurisdiction::Se,
            Jurisdiction::To => null,
        };
    }
}
