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
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the driver's licence number of a given jurisdiction and lists the covered jurisdictions a bare number fits
 * (every number of 1-8 digits fits both BC and SK, overlap table in CA.md). Only the 6 jurisdictions with a published
 * format are matched; the other 7 have no class and {@see fromNumberIn()} refuses them.
 * There is no `fromNumber()`: a jurisdiction without a published format could have issued any number, so a single
 * match among the covered ones does not identify the issuer. It returns once every jurisdiction is covered.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Driver's licence numbers — overlap (the factory's test oracle)"
 */
final class DriverLicenceNumberFactory
{
    private function __construct()
    {
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

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?DriverLicenceNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
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
            Jurisdiction::AB => AbDriverLicenceNumber::class,
            Jurisdiction::BC => BcDriverLicenceNumber::class,
            Jurisdiction::NS => NsDriverLicenceNumber::class,
            Jurisdiction::ON => OnDriverLicenceNumber::class,
            Jurisdiction::QC => QcDriverLicenceNumber::class,
            Jurisdiction::SK => SkDriverLicenceNumber::class,
            Jurisdiction::MB, Jurisdiction::NB, Jurisdiction::NL, Jurisdiction::NT, Jurisdiction::NU, Jurisdiction::PE,
            Jurisdiction::YT => null,
        };
    }
}
