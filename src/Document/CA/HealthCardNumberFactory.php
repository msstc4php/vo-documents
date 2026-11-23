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
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardVersionCode;
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
 * use {@see fromNumberIn()}. All 13 jurisdictions publish a format. A number is the health number only: an Ontario
 * card's version code (`9876543217 YM`) fits no jurisdiction here, and
 * {@see OnHealthCardNumber::fromCard()} splits it off.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Health card numbers — overlap (the factory's test oracle)"
 */
final class HealthCardNumberFactory
{
    private const string HINT = 'Invalid Canadian health card number (fits no jurisdiction; '
        . 'an Ontario card with a version code: use OnHealthCardNumber::fromCard()).';

    private function __construct()
    {
    }

    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several jurisdictions match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits a
     *                                        jurisdiction, otherwise the length; the message adds a pointer to
     *                                        {@see OnHealthCardNumber::fromCard()} when the input is an Ontario card
     *                                        with a version code
     */
    public static function fromNumber(string $number): HealthCardNumber
    {
        try {
            return self::resolver()->resolve($number);
        } catch (InvalidDocumentNumberException $exception) {
            if (self::carriesOntarioVersionCode($number)) {
                throw new InvalidDocumentNumberException(self::HINT, $exception->getCode(), previous: $exception);
            }

            throw $exception;
        }
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

    private static function carriesOntarioVersionCode(string $number): bool
    {
        try {
            return OnHealthCardNumber::fromCard($number)->versionCode() instanceof OnHealthCardVersionCode;
        } catch (InvalidDocumentNumberException) {
            return false;
        }
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
            Jurisdiction::AB => AbHealthCardNumber::class,
            Jurisdiction::BC => BcHealthCardNumber::class,
            Jurisdiction::MB => MbHealthCardNumber::class,
            Jurisdiction::NB => NbHealthCardNumber::class,
            Jurisdiction::NL => NlHealthCardNumber::class,
            Jurisdiction::NS => NsHealthCardNumber::class,
            Jurisdiction::NT => NtHealthCardNumber::class,
            Jurisdiction::NU => NuHealthCardNumber::class,
            Jurisdiction::ON => OnHealthCardNumber::class,
            Jurisdiction::PE => PeHealthCardNumber::class,
            Jurisdiction::QC => QcHealthCardNumber::class,
            Jurisdiction::SK => SkHealthCardNumber::class,
            Jurisdiction::YT => YtHealthCardNumber::class,
        };
    }
}
