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
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;

/**
 * Builds the DL/ID number of the jurisdiction a bare number belongs to, and never picks one of several: plain 7-9
 * digit numbers fit many states (overlap table in US.md), so callers that know the issuer use {@see fromNumberIn()}.
 * Only the 30 jurisdictions with a published format are matched; the other 21 have no class.
 *
 * @see docs/sources/US.md "Overlap — the factory's test oracle"
 */
final class DlIdNumberFactory
{
    private function __construct()
    {
    }

    /**
     * @throws AmbiguousJurisdictionException<Jurisdiction> when several jurisdictions match, listed in enum order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" if the length fits a
     *                                        jurisdiction, otherwise "(wrong length)"
     */
    public static function fromNumber(string $number): DlIdNumber
    {
        return self::resolver()->resolve($number);
    }

    /**
     * @throws InvalidDocumentNumberException when the number does not fit the jurisdiction's format, or "(format not
     *                                        covered)" for a jurisdiction that publishes none
     */
    public static function fromNumberIn(string $number, Jurisdiction $jurisdiction): DlIdNumber
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
     * @return SubdivisionResolver<Jurisdiction, DlIdNumber>
     */
    private static function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            'American DL/ID',
            self::coveredJurisdictions(),
            self::tryIn(...),
            self::fitsLengthIn(...),
        );
    }

    private static function tryIn(Jurisdiction $jurisdiction, string $number): ?DlIdNumber
    {
        return self::coveredClassFor($jurisdiction)::tryFrom($number);
    }

    private static function fitsLengthIn(Jurisdiction $jurisdiction, string $number): bool
    {
        return self::coveredClassFor($jurisdiction)::fitsLength($number);
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
            Jurisdiction::Al => AlDlIdNumber::class,
            Jurisdiction::Az => AzDlIdNumber::class,
            Jurisdiction::Co => CoDlIdNumber::class,
            Jurisdiction::Ct => CtDlIdNumber::class,
            Jurisdiction::Fl => FlDlIdNumber::class,
            Jurisdiction::Hi => HiDlIdNumber::class,
            Jurisdiction::Ia => IaDlIdNumber::class,
            Jurisdiction::Id => IdDlIdNumber::class,
            Jurisdiction::Il => IlDlIdNumber::class,
            Jurisdiction::In => InDlIdNumber::class,
            Jurisdiction::Ks => KsDlIdNumber::class,
            Jurisdiction::Ky => KyDlIdNumber::class,
            Jurisdiction::Ma => MaDlIdNumber::class,
            Jurisdiction::Md => MdDlIdNumber::class,
            Jurisdiction::Me => MeDlIdNumber::class,
            Jurisdiction::Mi => MiDlIdNumber::class,
            Jurisdiction::Ms => MsDlIdNumber::class,
            Jurisdiction::Nd => NdDlIdNumber::class,
            Jurisdiction::Ne => NeDlIdNumber::class,
            Jurisdiction::Nj => NjDlIdNumber::class,
            Jurisdiction::Nm => NmDlIdNumber::class,
            Jurisdiction::Ny => NyDlIdNumber::class,
            Jurisdiction::Ok => OkDlIdNumber::class,
            Jurisdiction::Pa => PaDlIdNumber::class,
            Jurisdiction::Ri => RiDlIdNumber::class,
            Jurisdiction::Tx => TxDlIdNumber::class,
            Jurisdiction::Vt => VtDlIdNumber::class,
            Jurisdiction::Wa => WaDlIdNumber::class,
            Jurisdiction::Wi => WiDlIdNumber::class,
            Jurisdiction::Wy => WyDlIdNumber::class,
            Jurisdiction::Ak, Jurisdiction::Ar, Jurisdiction::Ca, Jurisdiction::Dc, Jurisdiction::De, Jurisdiction::Ga,
            Jurisdiction::La, Jurisdiction::Mn, Jurisdiction::Mo, Jurisdiction::Mt, Jurisdiction::Nc, Jurisdiction::Nh,
            Jurisdiction::Nv, Jurisdiction::Oh, Jurisdiction::Or, Jurisdiction::Sc, Jurisdiction::Sd, Jurisdiction::Tn,
            Jurisdiction::Ut, Jurisdiction::Va, Jurisdiction::Wv => null,
        };
    }
}
