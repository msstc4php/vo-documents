<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\ActDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NswDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NtDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\QldDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\SaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\TasDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\VicDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\WaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the licence number of a given jurisdiction. There is no `fromNumber()`: the published formats overlap so much
 * that every valid number fits two or more jurisdictions (table in AU.md), so a bare number never identifies its
 * issuer; {@see jurisdictionsMatching()} lists the candidates.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/AU.md "The jurisdiction factory"
 */
final class DriverLicenceNumberFactory
{
    private function __construct()
    {
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the jurisdiction's format
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): DriverLicenceNumber
    {
        return self::classFor($jurisdiction)::from($number);
    }

    /**
     * @return list<Jurisdiction> in enum order
     */
    public static function jurisdictionsMatching(string $number): array
    {
        return SubdivisionResolver::matches(Jurisdiction::cases(), self::tryIn(...), $number);
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?DriverLicenceNumber
    {
        return self::classFor($jurisdiction)::tryFrom($number);
    }

    /**
     * @return class-string<AbstractDriverLicenceNumber>
     */
    private static function classFor(Jurisdiction $jurisdiction): string
    {
        return match ($jurisdiction) {
            Jurisdiction::ACT => ActDriverLicenceNumber::class,
            Jurisdiction::NSW => NswDriverLicenceNumber::class,
            Jurisdiction::NT => NtDriverLicenceNumber::class,
            Jurisdiction::QLD => QldDriverLicenceNumber::class,
            Jurisdiction::SA => SaDriverLicenceNumber::class,
            Jurisdiction::TAS => TasDriverLicenceNumber::class,
            Jurisdiction::VIC => VicDriverLicenceNumber::class,
            Jurisdiction::WA => WaDriverLicenceNumber::class,
        };
    }
}
