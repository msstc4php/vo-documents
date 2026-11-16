<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbstractHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\BcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\MbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NlHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NsHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NuHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\PeHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\QcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\SkHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\YtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the health card number of the jurisdiction a bare number belongs to, and never picks one of several: nine
 * digits fit six jurisdictions and ten digits up to three (overlap table in CA.md), so callers that know the issuer
 * use {@see fromNumberIn()}. All 13 jurisdictions publish a format.
 *
 * @see docs/sources/CA.md "Health card numbers — overlap (the factory's test oracle)"
 */
final class HealthCardNumberFactory
{
    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several jurisdictions match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits a
     *                                        jurisdiction, otherwise the length
     */
    public static function fromNumber(string $number): HealthCardNumber
    {
        return self::resolver()->resolve($number);
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the jurisdiction's format
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): HealthCardNumber
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
     * @return SubdivisionResolver<Jurisdiction, HealthCardNumber>
     */
    private static function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            'Canadian health card',
            Jurisdiction::cases(),
            self::tryIn(...),
            self::fitsLengthIn(...),
        );
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?HealthCardNumber
    {
        return self::classFor($jurisdiction)::tryFrom($number);
    }

    private static function fitsLengthIn(Jurisdiction $jurisdiction, string $number): bool
    {
        return self::classFor($jurisdiction)::fitsLength($number);
    }

    /**
     * @return class-string<AbstractHealthCardNumber>
     */
    private static function classFor(Jurisdiction $jurisdiction): string
    {
        return match ($jurisdiction) {
            Jurisdiction::Ab => AbHealthCardNumber::class,
            Jurisdiction::Bc => BcHealthCardNumber::class,
            Jurisdiction::Mb => MbHealthCardNumber::class,
            Jurisdiction::Nb => NbHealthCardNumber::class,
            Jurisdiction::Nl => NlHealthCardNumber::class,
            Jurisdiction::Ns => NsHealthCardNumber::class,
            Jurisdiction::Nt => NtHealthCardNumber::class,
            Jurisdiction::Nu => NuHealthCardNumber::class,
            Jurisdiction::On => OnHealthCardNumber::class,
            Jurisdiction::Pe => PeHealthCardNumber::class,
            Jurisdiction::Qc => QcHealthCardNumber::class,
            Jurisdiction::Sk => SkHealthCardNumber::class,
            Jurisdiction::Yt => YtHealthCardNumber::class,
        };
    }
}
