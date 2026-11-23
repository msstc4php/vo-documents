<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\BcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\NsDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\OnDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\QcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\SkDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the driver's licence number of the jurisdiction a bare number belongs to, and never picks one of several:
 * every number of 1-8 digits fits both BC and SK (overlap table in CA.md), so callers that know the issuer use
 * {@see fromNumberIn()}. Only the 6 jurisdictions with a published format are matched; the other 7 have no class.
 *
 * @see docs/sources/CA.md "Driver's licence numbers — overlap (the factory's test oracle)"
 */
final class DriverLicenceNumberFactory
{
    private function __construct()
    {
    }

    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several jurisdictions match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits a
     *                                        jurisdiction, otherwise "(wrong length)"
     */
    public static function fromNumber(string $number): DriverLicenceNumber
    {
        return self::resolver()->resolve($number);
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the jurisdiction's format, or "(format not
     *                                        covered)" for a jurisdiction that publishes none
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): DriverLicenceNumber
    {
        return self::coveredClassFor($jurisdiction)::from($number);
    }

    /**
     * @return list<Jurisdiction> in enum order
     */
    public static function jurisdictionsMatching(string $number): array
    {
        return self::resolver()->matching($number);
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
     * @return SubdivisionResolver<Jurisdiction, DriverLicenceNumber>
     */
    private static function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            "Canadian driver's licence",
            self::coveredJurisdictions(),
            self::tryIn(...),
            self::fitsLengthIn(...),
        );
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?DriverLicenceNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
    }

    private static function fitsLengthIn(Jurisdiction $jurisdiction, string $number): bool
    {
        return self::coveredClassFor($jurisdiction)::fitsLength($number);
    }

    /**
     * @return class-string<AbstractDriverLicenceNumber>
     *
     * @throws InvalidDocumentNumberException "(format not covered)" for a jurisdiction that publishes no format
     */
    private static function coveredClassFor(Jurisdiction $jurisdiction): string
    {
        $message = \sprintf("Invalid %s driver's licence number (format not covered).", $jurisdiction->isoCode());

        return self::classFor($jurisdiction) ?? throw new InvalidDocumentNumberException($message);
    }

    /**
     * @return class-string<AbstractDriverLicenceNumber>|null null for a jurisdiction with no published format (CA.md
     *                                                        "Provincial documents not covered")
     */
    private static function classFor(Jurisdiction $jurisdiction): ?string
    {
        return match ($jurisdiction) {
            Jurisdiction::Ab => AbDriverLicenceNumber::class,
            Jurisdiction::Bc => BcDriverLicenceNumber::class,
            Jurisdiction::Ns => NsDriverLicenceNumber::class,
            Jurisdiction::On => OnDriverLicenceNumber::class,
            Jurisdiction::Qc => QcDriverLicenceNumber::class,
            Jurisdiction::Sk => SkDriverLicenceNumber::class,
            Jurisdiction::Mb, Jurisdiction::Nb, Jurisdiction::Nl, Jurisdiction::Nt, Jurisdiction::Nu, Jurisdiction::Pe,
            Jurisdiction::Yt => null,
        };
    }
}
