<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Document\JP\DrivingLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md "Driving licence number" (警察庁丁運発第76号). The notice publishes no example
 * number and no check weights; the numbers below are made up.
 */
#[CoversClass(DrivingLicenceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DrivingLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'digits only' => ['301912345670', '301912345670'];
        yield 'spaces' => ['3019 1234 5670', '301912345670'];
        yield 'hyphens and NBSP' => ["3019-1234\u{00A0}5670", '301912345670'];
        yield 'ideographic space' => ["3019\u{3000}1234\u{3000}5670", '301912345670'];
        yield 'fullwidth digits' => [
            "\u{FF13}\u{FF10}\u{FF11}\u{FF19}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF10}",
            '301912345670',
        ];
        yield 'any check digit is accepted' => ['301912345680', '301912345680'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = DrivingLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testReadsTheFieldsOfTheNotice(): void
    {
        $number = DrivingLicenceNumber::from('301912345673');

        self::assertSame('30', $number->publicSafetyCommissionCode());
        self::assertSame('19', $number->yearSymbol());
        self::assertSame('123456', $number->serialNumber());
        self::assertSame('3', $number->reissueSymbol());
    }

    public function testKeepsLeadingZerosOfEveryField(): void
    {
        $number = DrivingLicenceNumber::from('010000000700');

        self::assertSame('01', $number->publicSafetyCommissionCode());
        self::assertSame('00', $number->yearSymbol());
        self::assertSame('000007', $number->serialNumber());
        self::assertSame('0', $number->reissueSymbol());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('********5670', DrivingLicenceNumber::from('301912345670')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $document = 'Invalid Japanese driving licence number';

        yield 'eleven digits' => ['30191234567', $document . ' (11 characters).'];
        yield 'thirteen digits' => ['3019123456700', $document . ' (13 characters).'];
        yield 'letter' => ['30191234567O', $document . ' (invalid character).'];
        yield 'empty' => ['', $document . ' (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): DrivingLicenceNumber => DrivingLicenceNumber::from($value),
            $input,
            $message,
        );
    }
}
