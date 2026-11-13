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
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the licence number of the jurisdiction a bare number belongs to, and never picks one of several: the
 * published formats overlap so much that today every valid number fits two or more jurisdictions (table in AU.md),
 * so callers that know the issuer use {@see fromNumberIn()}.
 *
 * @see docs/sources/AU.md "The jurisdiction factory"
 */
final class DriverLicenceNumberFactory
{
    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several jurisdictions match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches
     */
    public static function fromNumber(string $number): DriverLicenceNumber
    {
        return self::resolver()->resolve($number);
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
        return self::resolver()->matching($number);
    }

    /**
     * @return SubdivisionResolver<Jurisdiction, DriverLicenceNumber>
     */
    private static function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            'Australian driver licence',
            Jurisdiction::cases(),
            static fn (Jurisdiction $jurisdiction, string $number): ?DriverLicenceNumber => self::classFor($jurisdiction)::tryFrom($number),
        );
    }

    /**
     * @return class-string<AbstractDriverLicenceNumber>
     */
    private static function classFor(Jurisdiction $jurisdiction): string
    {
        return match ($jurisdiction) {
            Jurisdiction::Act => ActDriverLicenceNumber::class,
            Jurisdiction::Nsw => NswDriverLicenceNumber::class,
            Jurisdiction::Nt => NtDriverLicenceNumber::class,
            Jurisdiction::Qld => QldDriverLicenceNumber::class,
            Jurisdiction::Sa => SaDriverLicenceNumber::class,
            Jurisdiction::Tas => TasDriverLicenceNumber::class,
            Jurisdiction::Vic => VicDriverLicenceNumber::class,
            Jurisdiction::Wa => WaDriverLicenceNumber::class,
        };
    }
}
