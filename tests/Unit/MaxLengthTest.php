<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionClassConstant;
use SplFileInfo;

/**
 * Every string value object has a public `MAX_LENGTH` (in characters), frozen in 1.x: a storage column relies on it,
 * so a change needs a major release. A new class is added to the list.
 */
#[CoversNothing]
final class MaxLengthTest extends TestCase
{
    private const string NAMESPACE = 'MSSTC4PHP\\ValueObject\\Documents\\';

    private const array FROZEN = [
        'Document\\AU\\DriverLicence\\ActDriverLicenceNumber' => 10,
        'Document\\AU\\DriverLicence\\NswDriverLicenceNumber' => 8,
        'Document\\AU\\DriverLicence\\NtDriverLicenceNumber' => 10,
        'Document\\AU\\DriverLicence\\QldDriverLicenceNumber' => 9,
        'Document\\AU\\DriverLicence\\SaDriverLicenceNumber' => 6,
        'Document\\AU\\DriverLicence\\TasDriverLicenceNumber' => 8,
        'Document\\AU\\DriverLicence\\VicDriverLicenceNumber' => 10,
        'Document\\AU\\DriverLicence\\WaDriverLicenceNumber' => 7,
        'Document\\AU\\MedicareCardNumber' => 10,
        'Document\\AU\\TaxFileNumber' => 9,
        'Document\\BR\\Cns' => 15,
        'Document\\BR\\Cpf' => 11,
        'Document\\BR\\DriverLicenceNumber' => 11,
        'Document\\BR\\IdentityCard\\SpIdentityCardNumber' => 9,
        'Document\\BR\\Nis' => 11,
        'Document\\BR\\PassportNumber' => 8,
        'Document\\BR\\VoterRegistrationNumber' => 12,
        'Document\\CA\\DriverLicence\\AbDriverLicenceNumber' => 9,
        'Document\\CA\\DriverLicence\\BcDriverLicenceNumber' => 8,
        'Document\\CA\\DriverLicence\\NsDriverLicenceNumber' => 14,
        'Document\\CA\\DriverLicence\\OnDriverLicenceNumber' => 15,
        'Document\\CA\\DriverLicence\\QcDriverLicenceNumber' => 13,
        'Document\\CA\\DriverLicence\\SkDriverLicenceNumber' => 8,
        'Document\\CA\\HealthCard\\AbHealthCardNumber' => 9,
        'Document\\CA\\HealthCard\\BcHealthCardNumber' => 10,
        'Document\\CA\\HealthCard\\MbHealthCardNumber' => 9,
        'Document\\CA\\HealthCard\\NbHealthCardNumber' => 9,
        'Document\\CA\\HealthCard\\NlHealthCardNumber' => 12,
        'Document\\CA\\HealthCard\\NsHealthCardNumber' => 10,
        'Document\\CA\\HealthCard\\NtHealthCardNumber' => 8,
        'Document\\CA\\HealthCard\\NuHealthCardNumber' => 9,
        'Document\\CA\\HealthCard\\OnHealthCardNumber' => 10,
        'Document\\CA\\HealthCard\\OnHealthCardVersionCode' => 2,
        'Document\\CA\\HealthCard\\PeHealthCardNumber' => 8,
        'Document\\CA\\HealthCard\\QcHealthCardNumber' => 12,
        'Document\\CA\\HealthCard\\SkHealthCardNumber' => 9,
        'Document\\CA\\HealthCard\\YtHealthCardNumber' => 9,
        'Document\\CA\\PassportNumber' => 9,
        'Document\\CA\\SocialInsuranceNumber' => 9,
        'Document\\CH\\AhvNumber' => 13,
        'Document\\CH\\DriverLicenceNumber' => 15,
        'Document\\CH\\IdentityDocumentNumber' => 8,
        'Document\\CN\\CitizenIdentityNumber' => 18,
        'Document\\CN\\ExitEntryPermitToHongKongMacauNumber' => 9,
        'Document\\CN\\ExitEntryPermitToTaiwanNumber' => 9,
        'Document\\CN\\ForeignerPermanentResidenceNumber' => 18,
        'Document\\CN\\LegacyForeignerPermanentResidenceNumber' => 15,
        'Document\\CN\\OrdinaryPassportNumber' => 9,
        'Document\\DE\\IdentityCardNumber' => 9,
        'Document\\DE\\PassportNumber' => 9,
        'Document\\DE\\PensionInsuranceNumber' => 12,
        'Document\\DE\\TaxIdentificationNumber' => 11,
        'Document\\ES\\Dni' => 9,
        'Document\\ES\\Nie' => 10,
        'Document\\ES\\SocialSecurityNumber' => 12,
        'Document\\FR\\DriverLicenceNumber' => 12,
        'Document\\FR\\LegacyIdentityCardNumber' => 12,
        'Document\\FR\\Nir' => 15,
        'Document\\FR\\PassportNumber' => 9,
        'Document\\FR\\TaxIdentificationNumber' => 13,
        'Document\\GB\\ChiNumber' => 10,
        'Document\\GB\\GreatBritainDriverLicenceNumber' => 16,
        'Document\\GB\\HealthAndCareNumber' => 10,
        'Document\\GB\\NationalInsuranceNumber' => 9,
        'Document\\GB\\NhsNumber' => 10,
        'Document\\GB\\NorthernIrelandDriverLicenceNumber' => 8,
        'Document\\GB\\PassportNumber' => 9,
        'Document\\ID\\FamilyCardNumber' => 16,
        'Document\\ID\\NationalIdentityNumber' => 16,
        'Document\\ID\\TaxpayerNumber' => 16,
        'Document\\IN\\AadhaarNumber' => 12,
        'Document\\IN\\AbhaNumber' => 14,
        'Document\\IN\\PassportNumber' => 8,
        'Document\\IN\\PermanentAccountNumber' => 10,
        'Document\\IT\\FiscalCode' => 16,
        'Document\\IT\\PassportNumber' => 9,
        'Document\\JP\\BasicPensionNumber' => 10,
        'Document\\JP\\DriverLicenceNumber' => 12,
        'Document\\JP\\MyNumber' => 12,
        'Document\\JP\\PassportNumber' => 9,
        'Document\\JP\\ResidenceCardNumber' => 12,
        'Document\\KR\\DriverLicenceNumber' => 15,
        'Document\\KR\\ForeignerRegistrationNumber' => 13,
        'Document\\KR\\MultipleEntryPassportNumber' => 9,
        'Document\\KR\\ResidentRegistrationNumber' => 13,
        'Document\\MX\\Curp' => 18,
        'Document\\MX\\DriverLicence\\MexDriverLicenceNumber' => 12,
        'Document\\MX\\ElectorKey' => 18,
        'Document\\MX\\Rfc' => 13,
        'Document\\MX\\SocialSecurityNumber' => 11,
        'Document\\NL\\Bsn' => 9,
        'Document\\NL\\IdentityDocumentNumber' => 9,
        'Document\\RU\\DriverLicenceNumber' => 10,
        'Document\\RU\\Inn' => 12,
        'Document\\RU\\InternalPassportNumber' => 10,
        'Document\\RU\\InternationalPassportNumber' => 9,
        'Document\\RU\\OmsPolicyNumber' => 16,
        'Document\\RU\\PassportIssuingUnitCode' => 6,
        'Document\\RU\\Snils' => 11,
        'Document\\SA\\IqamaNumber' => 10,
        'Document\\SA\\NationalIdentityNumber' => 10,
        'Document\\TR\\IdentityNumber' => 11,
        'Document\\TR\\PassportNumber' => 9,
        'Document\\US\\DlId\\AlDlIdNumber' => 8,
        'Document\\US\\DlId\\AzDlIdNumber' => 9,
        'Document\\US\\DlId\\CoDlIdNumber' => 9,
        'Document\\US\\DlId\\CtDlIdNumber' => 9,
        'Document\\US\\DlId\\FlDlIdNumber' => 13,
        'Document\\US\\DlId\\HiDlIdNumber' => 9,
        'Document\\US\\DlId\\IaDlIdNumber' => 9,
        'Document\\US\\DlId\\IdDlIdNumber' => 9,
        'Document\\US\\DlId\\IlDlIdNumber' => 12,
        'Document\\US\\DlId\\InDlIdNumber' => 10,
        'Document\\US\\DlId\\KsDlIdNumber' => 9,
        'Document\\US\\DlId\\KyDlIdNumber' => 9,
        'Document\\US\\DlId\\MaDlIdNumber' => 9,
        'Document\\US\\DlId\\MdDlIdNumber' => 13,
        'Document\\US\\DlId\\MeDlIdNumber' => 7,
        'Document\\US\\DlId\\MiDlIdNumber' => 13,
        'Document\\US\\DlId\\MsDlIdNumber' => 10,
        'Document\\US\\DlId\\NdDlIdNumber' => 9,
        'Document\\US\\DlId\\NeDlIdNumber' => 9,
        'Document\\US\\DlId\\NjDlIdNumber' => 15,
        'Document\\US\\DlId\\NmDlIdNumber' => 9,
        'Document\\US\\DlId\\NyDlIdNumber' => 9,
        'Document\\US\\DlId\\OkDlIdNumber' => 10,
        'Document\\US\\DlId\\PaDlIdNumber' => 8,
        'Document\\US\\DlId\\RiDlIdNumber' => 8,
        'Document\\US\\DlId\\TxDlIdNumber' => 8,
        'Document\\US\\DlId\\VtDlIdNumber' => 8,
        'Document\\US\\DlId\\WaDlIdNumber' => 12,
        'Document\\US\\DlId\\WiDlIdNumber' => 14,
        'Document\\US\\DlId\\WyDlIdNumber' => 9,
        'Document\\US\\Itin' => 9,
        'Document\\US\\MedicareBeneficiaryIdentifier' => 11,
        'Document\\US\\PassportBookNumber' => 9,
        'Document\\US\\PassportCardNumber' => 9,
        'Document\\US\\SocialSecurityNumber' => 9,
        'Icao\\Mrz\\Td1' => 92,
        'Icao\\Mrz\\Td2' => 92,
        'Icao\\Mrz\\Td3' => 92,
        'Icao\\PersonName' => 255,
        'Name\\AU\\FamilyName' => 100,
        'Name\\AU\\GivenName' => 100,
        'Name\\BR\\FamilyName' => 100,
        'Name\\BR\\GivenName' => 100,
        'Name\\CA\\FamilyName' => 100,
        'Name\\CA\\GivenName' => 100,
        'Name\\CH\\FamilyName' => 100,
        'Name\\CH\\GivenName' => 100,
        'Name\\CN\\FamilyName' => 100,
        'Name\\CN\\GivenName' => 100,
        'Name\\DE\\FamilyName' => 100,
        'Name\\DE\\GivenName' => 100,
        'Name\\ES\\FamilyName' => 100,
        'Name\\ES\\GivenName' => 100,
        'Name\\FR\\FamilyName' => 100,
        'Name\\FR\\GivenName' => 100,
        'Name\\GB\\FamilyName' => 100,
        'Name\\GB\\GivenName' => 100,
        'Name\\ID\\FullName' => 100,
        'Name\\IN\\Devanagari\\GivenName' => 100,
        'Name\\IN\\Devanagari\\Surname' => 100,
        'Name\\IN\\GivenName' => 100,
        'Name\\IN\\Surname' => 100,
        'Name\\IT\\FamilyName' => 100,
        'Name\\IT\\GivenName' => 100,
        'Name\\JP\\FamilyName' => 100,
        'Name\\JP\\FamilyNameReading' => 100,
        'Name\\JP\\GivenName' => 100,
        'Name\\JP\\GivenNameReading' => 100,
        'Name\\KR\\FamilyName' => 100,
        'Name\\KR\\GivenName' => 100,
        'Name\\MX\\FamilyName' => 100,
        'Name\\MX\\GivenName' => 100,
        'Name\\NL\\FamilyName' => 100,
        'Name\\NL\\GivenName' => 100,
        'Name\\RU\\FamilyName' => 100,
        'Name\\RU\\GivenName' => 100,
        'Name\\RU\\Patronymic' => 100,
        'Name\\SA\\FamilyName' => 100,
        'Name\\SA\\FatherName' => 100,
        'Name\\SA\\GivenName' => 100,
        'Name\\SA\\GrandfatherName' => 100,
        'Name\\TR\\FamilyName' => 100,
        'Name\\TR\\GivenName' => 100,
        'Name\\US\\FamilyName' => 100,
        'Name\\US\\GivenName' => 100,
        'Name\\US\\MiddleName' => 100,
    ];

    public function testEveryStringValueObjectHasItsFrozenMaxLength(): void
    {
        $source = \dirname(__DIR__, 2) . '/src/';
        $found = [];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($files as $file) {
            \assert($file instanceof SplFileInfo);
            $relative = \str_replace('/', '\\', \substr($file->getPathname(), \strlen($source), -4));
            $class = self::NAMESPACE . $relative;
            if (!\class_exists($class) || !\is_subclass_of($class, AbstractStringValueObject::class)) {
                continue;
            }

            if (new ReflectionClass($class)->isAbstract()) {
                continue;
            }

            $constant = new ReflectionClassConstant($class, 'MAX_LENGTH');
            self::assertTrue($constant->isPublic(), $class);
            $found[$relative] = $constant->getValue();
        }

        \ksort($found);
        self::assertSame(self::FROZEN, $found);
    }
}
