<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\AbstractIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\SpIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "RG per state — overlap (the factory's test oracle)": every worked example
 * and one input per overlap-table row. Only BR-SP is covered, so no number is ambiguous.
 */
#[CoversClass(IdentityCardNumberFactory::class)]
#[UsesClass(AbstractIdentityCardNumber::class)]
#[UsesClass(SpIdentityCardNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(SubdivisionResolver::class)]
#[UsesClass(Text::class)]
final class IdentityCardNumberFactoryTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, list<Jurisdiction>}>
     */
    public static function matchingJurisdictions(): iterable
    {
        yield 'example (empty)' => ['', []];
        yield 'example 12.345.678-2' => ['12.345.678-2', [Jurisdiction::SP]];
        yield 'example 123456782' => ['123456782', [Jurisdiction::SP]];
        yield 'example 24.681.357-X' => ['24.681.357-X', [Jurisdiction::SP]];
        yield 'example 24681357x' => ['24681357x', [Jurisdiction::SP]];
        yield 'example 1-2' => ['1-2', [Jurisdiction::SP]];
        yield 'example 00000001-2' => ['00000001-2', [Jurisdiction::SP]];
        yield 'example 99.999.999-0' => ['99.999.999-0', [Jurisdiction::SP]];
        yield 'example 12.345.678-3' => ['12.345.678-3', []];
        yield 'example 0-0' => ['0-0', []];
        yield 'example 2' => ['2', []];
        yield 'example 1234567890' => ['1234567890', []];
        yield 'example 12345678A' => ['12345678A', []];
        yield 'overlap: 2 to 9 characters' => ['1.234.567-2', [Jurisdiction::SP]];
    }

    /**
     * @param list<Jurisdiction> $expected
     */
    #[DataProvider('matchingJurisdictions')]
    public function testListsMatchingJurisdictionsInEnumOrder(string $number, array $expected): void
    {
        self::assertSame($expected, IdentityCardNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function singleMatches(): iterable
    {
        yield '12.345.678-2' => ['12.345.678-2', '123456782'];
        yield '123456782' => ['123456782', '123456782'];
        yield '24.681.357-X' => ['24.681.357-X', '24681357X'];
        yield '24681357x' => ['24681357x', '24681357X'];
        yield '1-2' => ['1-2', '000000012'];
        yield '00000001-2' => ['00000001-2', '000000012'];
        yield '99.999.999-0' => ['99.999.999-0', '999999990'];
    }

    #[DataProvider('singleMatches')]
    public function testReturnsTheOnlyMatchingJurisdiction(string $number, string $value): void
    {
        $matches = IdentityCardNumberFactory::jurisdictionsMatching($number);
        self::assertCount(1, $matches);
        $card = IdentityCardNumberFactory::fromNumberIn($number, $matches[0]);

        self::assertInstanceOf(SpIdentityCardNumber::class, $card);
        self::assertSame($value, (string) $card);
        self::assertTrue(SpIdentityCardNumber::from($number)->equals($card));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'empty' => [''];
        yield 'wrong check digit' => ['12.345.678-3'];
        yield 'zeros only' => ['0-0'];
        yield 'one character' => ['2'];
        yield 'ten digits' => ['1234567890'];
        yield 'ten characters with dots' => ['1.234.567.890'];
        yield 'letter as check' => ['12345678A'];
        yield 'pasted several times' => [\str_repeat('12.345.678-2 ', 5)];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testANumberValidNowhereMatchesNoJurisdiction(string $number): void
    {
        self::assertSame([], IdentityCardNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractIdentityCardNumber>, string, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'BR-SP' => [Jurisdiction::SP, SpIdentityCardNumber::class, '12.345.678-2', '123456782'];
    }

    /**
     * @param class-string<AbstractIdentityCardNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(
        Jurisdiction $jurisdiction,
        string $class,
        string $number,
        string $value,
    ): void {
        $card = IdentityCardNumberFactory::fromNumberIn($number, $jurisdiction);

        self::assertInstanceOf($class, $card);
        self::assertSame($jurisdiction, $card->jurisdiction());
        self::assertSame($value, (string) $card);
        self::assertContains($jurisdiction, IdentityCardNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * The 26 units of BR.md "RG per state — per unit" with a "not covered" verdict.
     *
     * @return iterable<string, array{Jurisdiction}>
     */
    public static function notCovered(): iterable
    {
        yield 'BR-AC' => [Jurisdiction::AC];
        yield 'BR-AL' => [Jurisdiction::AL];
        yield 'BR-AM' => [Jurisdiction::AM];
        yield 'BR-AP' => [Jurisdiction::AP];
        yield 'BR-BA' => [Jurisdiction::BA];
        yield 'BR-CE' => [Jurisdiction::CE];
        yield 'BR-DF' => [Jurisdiction::DF];
        yield 'BR-ES' => [Jurisdiction::ES];
        yield 'BR-GO' => [Jurisdiction::GO];
        yield 'BR-MA' => [Jurisdiction::MA];
        yield 'BR-MG' => [Jurisdiction::MG];
        yield 'BR-MS' => [Jurisdiction::MS];
        yield 'BR-MT' => [Jurisdiction::MT];
        yield 'BR-PA' => [Jurisdiction::PA];
        yield 'BR-PB' => [Jurisdiction::PB];
        yield 'BR-PE' => [Jurisdiction::PE];
        yield 'BR-PI' => [Jurisdiction::PI];
        yield 'BR-PR' => [Jurisdiction::PR];
        yield 'BR-RJ' => [Jurisdiction::RJ];
        yield 'BR-RN' => [Jurisdiction::RN];
        yield 'BR-RO' => [Jurisdiction::RO];
        yield 'BR-RR' => [Jurisdiction::RR];
        yield 'BR-RS' => [Jurisdiction::RS];
        yield 'BR-SC' => [Jurisdiction::SC];
        yield 'BR-SE' => [Jurisdiction::SE];
        yield 'BR-TO' => [Jurisdiction::TO];
    }

    #[DataProvider('notCovered')]
    public function testFromNumberInRefusesAUnitWithoutAPublishedFormat(Jurisdiction $jurisdiction): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => IdentityCardNumberFactory::fromNumberIn($value, $jurisdiction),
            '12.345.678-2',
            \sprintf('Invalid %s identity card number (format not covered).', $jurisdiction->isoCode()),
        );
    }

    public function testEveryUnitIsEitherCoveredOrListedAsNotCovered(): void
    {
        $covered = [];
        foreach (self::concreteClasses() as [$jurisdiction]) {
            $covered[] = $jurisdiction;
        }

        $notCovered = [];
        foreach (self::notCovered() as [$jurisdiction]) {
            $notCovered[] = $jurisdiction;
        }

        self::assertCount(1, $covered);
        self::assertCount(26, $notCovered);
        self::assertSame([], \array_uintersect($covered, $notCovered, $this->compare(...)));

        $all = [...$covered, ...$notCovered];
        \usort($all, $this->compare(...));
        self::assertSame(Jurisdiction::cases(), $all);
    }

    public function testFromNumberInRefusesANumberOfTheWrongShape(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => IdentityCardNumberFactory::fromNumberIn($value, Jurisdiction::SP),
            '12.345.678-3',
            'Invalid BR-SP identity card number checksum.',
        );
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
