<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AbstractDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AzDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\CoDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\CtDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\FlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\HiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\InDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\KsDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\KyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MeDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MsDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NeDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NjDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NmDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\OkDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\PaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\RiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\TxDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\VtDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

/**
 * Builds the DL/ID number of a given jurisdiction and lists the covered jurisdictions a bare number fits (plain 7-9
 * digit numbers fit many states, overlap table in US.md). Only the 30 jurisdictions with a published format are
 * matched; the other 21 have no class and {@see fromNumberIn()} refuses them.
 * There is no `fromNumber()`: a jurisdiction without a published format could have issued any number, so a single
 * match among the covered ones does not identify the issuer. It returns once every jurisdiction is covered.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "Overlap — the factory's test oracle"
 */
final class DlIdNumberFactory
{
    private function __construct()
    {
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the jurisdiction's format, or "(format not
     *                                        covered)" for a jurisdiction that publishes none
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): DlIdNumber
    {
        return self::coveredClassFor($jurisdiction)::from($number);
    }

    /** Null when no candidate or several fit; fromNumberIn() says which. */
    public static function tryFromNumberIn(string $number, Jurisdiction $jurisdiction): ?DlIdNumber
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

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?DlIdNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
    }

    /**
     * @return class-string<AbstractDlIdNumber>
     *
     * @throws InvalidDocumentNumberException "(format not covered)" for a jurisdiction that publishes no format
     */
    private static function coveredClassFor(Jurisdiction $jurisdiction): string
    {
        $message = \sprintf('Invalid %s DL/ID number (format not covered).', $jurisdiction->isoCode());

        return self::classFor($jurisdiction) ?? throw new InvalidDocumentNumberException($message);
    }

    /**
     * @return class-string<AbstractDlIdNumber>|null null for a jurisdiction with no published format (US.md "DL/ID
     *                                               numbers not covered")
     */
    private static function classFor(Jurisdiction $jurisdiction): ?string
    {
        return match ($jurisdiction) {
            Jurisdiction::AL => AlDlIdNumber::class,
            Jurisdiction::AZ => AzDlIdNumber::class,
            Jurisdiction::CO => CoDlIdNumber::class,
            Jurisdiction::CT => CtDlIdNumber::class,
            Jurisdiction::FL => FlDlIdNumber::class,
            Jurisdiction::HI => HiDlIdNumber::class,
            Jurisdiction::IA => IaDlIdNumber::class,
            Jurisdiction::ID => IdDlIdNumber::class,
            Jurisdiction::IL => IlDlIdNumber::class,
            Jurisdiction::IN => InDlIdNumber::class,
            Jurisdiction::KS => KsDlIdNumber::class,
            Jurisdiction::KY => KyDlIdNumber::class,
            Jurisdiction::MA => MaDlIdNumber::class,
            Jurisdiction::MD => MdDlIdNumber::class,
            Jurisdiction::ME => MeDlIdNumber::class,
            Jurisdiction::MI => MiDlIdNumber::class,
            Jurisdiction::MS => MsDlIdNumber::class,
            Jurisdiction::ND => NdDlIdNumber::class,
            Jurisdiction::NE => NeDlIdNumber::class,
            Jurisdiction::NJ => NjDlIdNumber::class,
            Jurisdiction::NM => NmDlIdNumber::class,
            Jurisdiction::NY => NyDlIdNumber::class,
            Jurisdiction::OK => OkDlIdNumber::class,
            Jurisdiction::PA => PaDlIdNumber::class,
            Jurisdiction::RI => RiDlIdNumber::class,
            Jurisdiction::TX => TxDlIdNumber::class,
            Jurisdiction::VT => VtDlIdNumber::class,
            Jurisdiction::WA => WaDlIdNumber::class,
            Jurisdiction::WI => WiDlIdNumber::class,
            Jurisdiction::WY => WyDlIdNumber::class,
            Jurisdiction::AK, Jurisdiction::AR, Jurisdiction::CA, Jurisdiction::DC, Jurisdiction::DE, Jurisdiction::GA,
            Jurisdiction::LA, Jurisdiction::MN, Jurisdiction::MO, Jurisdiction::MT, Jurisdiction::NC, Jurisdiction::NH,
            Jurisdiction::NV, Jurisdiction::OH, Jurisdiction::OR, Jurisdiction::SC, Jurisdiction::SD, Jurisdiction::TN,
            Jurisdiction::UT, Jurisdiction::VA, Jurisdiction::WV => null,
        };
    }
}
