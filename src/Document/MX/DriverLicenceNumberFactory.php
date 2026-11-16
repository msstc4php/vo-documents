<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\MexDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the driver's licence number of the entity a bare number belongs to, and never picks one of several. Only the
 * entities with a published format are matched. Today only MX-MEX is covered, so {@see fromNumber()} assigns every
 * number that fits its shape (twelve digits) to MX-MEX, whichever state issued it; callers that know the issuer must
 * use {@see fromNumberIn()}, which refuses the 31 other entities with "(format not covered)".
 *
 * @see docs/sources/MX.md "State driving licences — overlap (the factory's test oracle)"
 */
final class DriverLicenceNumberFactory
{
    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several entities match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits an entity,
     *                                        otherwise the length
     */
    public static function fromNumber(string $number): DriverLicenceNumber
    {
        return self::resolver()->resolve($number);
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the entity's format, or "(format not
     *                                        covered)" for an entity that publishes none
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
            "Mexican driver's licence",
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
     * @throws InvalidDocumentNumberException "(format not covered)" for an entity that publishes no format
     */
    private static function coveredClassFor(Jurisdiction $jurisdiction): string
    {
        $message = \sprintf("Invalid %s driver's licence number (format not covered).", $jurisdiction->isoCode());

        return self::classFor($jurisdiction) ?? throw new InvalidDocumentNumberException($message);
    }

    /**
     * @return class-string<AbstractDriverLicenceNumber>|null null for an entity with no published format (MX.md
     *                                                        "State driving licences — per entity")
     */
    private static function classFor(Jurisdiction $jurisdiction): ?string
    {
        return match ($jurisdiction) {
            Jurisdiction::Mex => MexDriverLicenceNumber::class,
            Jurisdiction::Agu, Jurisdiction::Bcn, Jurisdiction::Bcs, Jurisdiction::Cam, Jurisdiction::Chh,
            Jurisdiction::Chp, Jurisdiction::Cmx, Jurisdiction::Coa, Jurisdiction::Col, Jurisdiction::Dur,
            Jurisdiction::Gro, Jurisdiction::Gua, Jurisdiction::Hid, Jurisdiction::Jal, Jurisdiction::Mic,
            Jurisdiction::Mor, Jurisdiction::Nay, Jurisdiction::Nle, Jurisdiction::Oax, Jurisdiction::Pue,
            Jurisdiction::Que, Jurisdiction::Roo, Jurisdiction::Sin, Jurisdiction::Slp, Jurisdiction::Son,
            Jurisdiction::Tab, Jurisdiction::Tam, Jurisdiction::Tla, Jurisdiction::Ver, Jurisdiction::Yuc,
            Jurisdiction::Zac => null,
        };
    }
}
