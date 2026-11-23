<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\MexDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the driver's licence number of a given entity and lists the covered entities a bare number fits. Only the
 * entities with a published format are matched (today MX-MEX alone); {@see fromNumberIn()} refuses the 31 other
 * entities with "(format not covered)".
 * There is no `fromNumber()`: a jurisdiction without a published format could have issued any number, so a single match
 * among the covered ones does not identify the issuer. It returns once every jurisdiction is covered.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/MX.md "State driving licences — overlap (the factory's test oracle)"
 */
final class DriverLicenceNumberFactory
{
    private function __construct()
    {
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
            Jurisdiction::MEX => MexDriverLicenceNumber::class,
            Jurisdiction::AGU, Jurisdiction::BCN, Jurisdiction::BCS, Jurisdiction::CAM, Jurisdiction::CHH,
            Jurisdiction::CHP, Jurisdiction::CMX, Jurisdiction::COA, Jurisdiction::COL, Jurisdiction::DUR,
            Jurisdiction::GRO, Jurisdiction::GUA, Jurisdiction::HID, Jurisdiction::JAL, Jurisdiction::MIC,
            Jurisdiction::MOR, Jurisdiction::NAY, Jurisdiction::NLE, Jurisdiction::OAX, Jurisdiction::PUE,
            Jurisdiction::QUE, Jurisdiction::ROO, Jurisdiction::SIN, Jurisdiction::SLP, Jurisdiction::SON,
            Jurisdiction::TAB, Jurisdiction::TAM, Jurisdiction::TLA, Jurisdiction::VER, Jurisdiction::YUC,
            Jurisdiction::ZAC => null,
        };
    }
}
