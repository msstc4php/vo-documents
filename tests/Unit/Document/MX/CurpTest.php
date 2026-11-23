<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use DateTimeZone;
use MSSTC4PHP\ValueObject\Documents\Document\MX\Curp;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "CURP" (RENAPO Instructivo Normativo, numeral Tercero; Reglas 2021, 5.1-5.2).
 * The check digit is not published, so the examples carry arbitrary digits.
 */
#[CoversClass(Curp::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class CurpTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validCodes(): iterable
    {
        yield 'born before 2000' => ['HEGJ561231HDFRRN04', 'HEGJ561231HDFRRN04'];
        yield 'lower case, spaces, NBSP, hyphen' => ["hegj 561231-hdf\u{00A0}rrn04", 'HEGJ561231HDFRRN04'];
        yield 'born from 2000, abroad' => ['GAXJ040229MNEXRLA7', 'GAXJ040229MNEXRLA7'];
        yield 'no surnames (XXX)' => ['XXXJ800101HDFXXN01', 'XXXJ800101HDFXXN01'];
        yield 'inconvenient word, second letter X' => ['PXDO900101MCSDRF02', 'PXDO900101MCSDRF02'];
        yield 'last century letter J' => ['AAAA991231HDFBBBJ9', 'AAAA991231HDFBBBJ9'];
    }

    #[DataProvider('validCodes')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $curp = Curp::from($input);

        self::assertSame($expected, $curp->value);
        self::assertSame($expected, (string) $curp);
        self::assertSame($expected, $curp->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, string, Sex, string}>
     */
    public static function fields(): iterable
    {
        yield 'digit at 17: 1900s, man' => ['HEGJ561231HDFRRN04', '1956-12-31', Sex::Male, 'DF'];
        yield 'digit 9 at 17' => ['HEGJ000101HJCRRN94', '1900-01-01', Sex::Male, 'JC'];
        yield 'letter A at 17: 2000s, woman' => ['GAXJ040229MNEXRLA7', '2004-02-29', Sex::Female, 'NE'];
        yield 'letter at 17: 29 February 2000' => ['GAXJ000229MNLXRLB7', '2000-02-29', Sex::Female, 'NL'];
        yield 'X: non-binary' => ['PEXO900101XMCRMN08', '1990-01-01', Sex::Unspecified, 'MC'];
    }

    #[DataProvider('fields')]
    public function testReadsTheFields(string $input, string $date, Sex $sex, string $state): void
    {
        $curp = Curp::from($input);

        self::assertSame($date . ' 00:00:00', $curp->birthDate()->format('Y-m-d H:i:s'));
        self::assertEquals(new DateTimeZone('UTC'), $curp->birthDate()->getTimezone());
        self::assertSame($sex, $curp->sex());
        self::assertSame($state, $curp->birthStateCode());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**************RN04', Curp::from('HEGJ561231HDFRRN04')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'too short' => ['HEGJ561231HDFRRN0', 'Invalid Mexican CURP (wrong length).'];
        yield 'too long' => ['HEGJ561231HDFRRN045', 'Invalid Mexican CURP (wrong length).'];
        yield 'far too long' => [\str_repeat('A', 41), 'Invalid Mexican CURP (wrong length).'];
        yield 'punctuation' => ['HEGJ561231HDFRRN0.', 'Invalid Mexican CURP (invalid character).'];
        yield 'digit among the name letters' => ['HEG1561231HDFRRN04', 'Invalid Mexican CURP (invalid character).'];
        yield 'letter in the date' => ['HEGJ56123AHDFRRN04', 'Invalid Mexican CURP (invalid character).'];
        yield 'sex neither H, M nor X' => ['HEGJ561231FDFRRN04', 'Invalid Mexican CURP (invalid character).'];
        yield 'digit in the state' => ['HEGJ561231HD1RRN04', 'Invalid Mexican CURP (invalid character).'];
        yield 'digit among the consonants' => ['HEGJ561231HDFRR104', 'Invalid Mexican CURP (invalid character).'];
        yield 'century letter after J' => ['HEGJ561231HDFRRNK4', 'Invalid Mexican CURP (invalid character).'];
        yield 'letter as check digit' => ['HEGJ561231HDFRRN0A', 'Invalid Mexican CURP (invalid character).'];
        yield '30 February' => ['HEGJ560230HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
        yield '29 February 1900 (digit at 17)' => ['HEGJ000229HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
        yield '29 February 2001' => ['HEGJ010229HDFRRNA4', 'Invalid Mexican CURP (invalid birth date).'];
        yield 'month 13' => ['HEGJ561301HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
        yield 'month 00' => ['HEGJ560001HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
        yield 'day 00' => ['HEGJ561200HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
        yield 'day 32' => ['HEGJ560132HDFRRN04', 'Invalid Mexican CURP (invalid birth date).'];
    }

    #[DataProvider('invalidCodes')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Curp => Curp::from($value), $input, $message);
    }
}
