<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Document\SA\AbstractIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Document\SA\IdentityNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\SA\IqamaNumber;
use MSSTC4PHP\ValueObject\Documents\Document\SA\NationalIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/SA.md "National ID and iqama": the first digit routes (1: national ID, 2: iqama,
 * ZATCA BR-KSA-F-10/F-11); any other first digit fits neither class. The numbers are made up for the tests.
 */
#[CoversClass(IdentityNumberFactory::class)]
#[UsesClass(AbstractIdentityNumber::class)]
#[UsesClass(NationalIdentityNumber::class)]
#[UsesClass(IqamaNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class IdentityNumberFactoryTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const string NO_CLASS = 'Saudi identity number fits neither the national ID nor the iqama number.';

    /**
     * @return iterable<string, array{string, class-string<AbstractIdentityNumber>, string}>
     */
    public static function routedNumbers(): iterable
    {
        yield 'first digit 1' => ['1012345678', NationalIdentityNumber::class, '1012345678'];
        yield 'first digit 2' => ['2012345678', IqamaNumber::class, '2012345678'];
        yield 'national ID typed with spaces' => ['1012 3456 78', NationalIdentityNumber::class, '1012345678'];
        yield 'iqama in Arabic-Indic digits' => ['٢٣٤٥٦٧٨٩٠١', IqamaNumber::class, '2345678901'];
    }

    /**
     * @param class-string<AbstractIdentityNumber> $class
     */
    #[DataProvider('routedNumbers')]
    public function testRoutesByTheFirstDigit(string $number, string $class, string $value): void
    {
        $routed = IdentityNumberFactory::fromNumber($number);

        self::assertInstanceOf($class, $routed);
        self::assertSame($value, $routed->value);
        self::assertTrue($class::from($number)->equals($routed));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function otherFirstDigits(): iterable
    {
        foreach (['0', '3', '4', '5', '6', '7', '8', '9'] as $digit) {
            yield 'first digit ' . $digit => [$digit . '012345678'];
        }
    }

    #[DataProvider('otherFirstDigits')]
    public function testRefusesAnotherFirstDigitInsteadOfGuessing(string $number): void
    {
        self::assertRefusedWithoutEcho(IdentityNumberFactory::fromNumber(...), $number, self::NO_CLASS);
    }

    #[DataProvider('otherFirstDigits')]
    public function testBothClassesStillAcceptAnotherFirstDigit(string $number): void
    {
        self::assertSame($number, NationalIdentityNumber::from($number)->value);
        self::assertSame($number, IqamaNumber::from($number)->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function numbersNeitherClassAccepts(): iterable
    {
        yield 'empty' => [''];
        yield 'nine digits' => ['101234567'];
        yield 'eleven digits' => ['20123456789'];
        yield 'a letter' => ['1O12345678'];
    }

    #[DataProvider('numbersNeitherClassAccepts')]
    public function testRefusesANumberNeitherClassAccepts(string $number): void
    {
        self::assertRefusedWithoutEcho(IdentityNumberFactory::fromNumber(...), $number, self::NO_CLASS);
    }

    public function testKeepsTheClassRefusalAsThePrevious(): void
    {
        try {
            IdentityNumberFactory::fromNumber('101234567');
            self::fail('Expected an InvalidDocumentNumberException.');
        } catch (InvalidDocumentNumberException $exception) {
            self::assertInstanceOf(InvalidDocumentNumberException::class, $exception->getPrevious());
        }
    }

    /**
     * @param class-string<AbstractIdentityNumber> $class
     */
    #[DataProvider('routedNumbers')]
    public function testTryFromNumberBuildsWhatFromNumberBuilds(string $number, string $class, string $value): void
    {
        $built = IdentityNumberFactory::tryFromNumber($number);

        self::assertInstanceOf($class, $built);
        self::assertSame($value, $built->value);
        self::assertEquals(IdentityNumberFactory::fromNumber($number), $built);
    }

    #[DataProvider('otherFirstDigits')]
    #[DataProvider('numbersNeitherClassAccepts')]
    public function testTryFromNumberIsNullWhereFromNumberRefuses(string $number): void
    {
        self::assertNull(IdentityNumberFactory::tryFromNumber($number));
    }
}
