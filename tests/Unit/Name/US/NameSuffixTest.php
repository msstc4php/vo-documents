<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\US;

use MSSTC4PHP\ValueObject\Documents\Name\US\NameSuffix;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "Name suffix" (AAMVA list; 8 FAM 403.1-5(B) c. for the Roman numerals).
 */
#[CoversClass(NameSuffix::class)]
final class NameSuffixTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    public function testIsTheAamvaList(): void
    {
        self::assertSame(
            [
                'JR', 'SR', '1ST', 'I', '2ND', 'II', '3RD', 'III', '4TH', 'IV',
                '5TH', 'V', '6TH', 'VI', '7TH', 'VII', '8TH', 'VIII', '9TH', 'IX',
            ],
            \array_map(static fn (NameSuffix $suffix): string => $suffix->value, NameSuffix::cases()),
        );
    }

    /**
     * @return iterable<string, array{NameSuffix, string}>
     */
    public static function icaoComponents(): iterable
    {
        yield 'JR' => [NameSuffix::Junior, 'JR'];
        yield 'SR' => [NameSuffix::Senior, 'SR'];
        yield '1ST' => [NameSuffix::First, 'I'];
        yield 'I' => [NameSuffix::I, 'I'];
        yield '2ND' => [NameSuffix::Second, 'II'];
        yield 'II' => [NameSuffix::II, 'II'];
        yield '3RD' => [NameSuffix::Third, 'III'];
        yield 'III' => [NameSuffix::III, 'III'];
        yield '4TH' => [NameSuffix::Fourth, 'IV'];
        yield 'IV' => [NameSuffix::IV, 'IV'];
        yield '5TH' => [NameSuffix::Fifth, 'V'];
        yield 'V' => [NameSuffix::V, 'V'];
        yield '6TH' => [NameSuffix::Sixth, 'VI'];
        yield 'VI' => [NameSuffix::VI, 'VI'];
        yield '7TH' => [NameSuffix::Seventh, 'VII'];
        yield 'VII' => [NameSuffix::VII, 'VII'];
        yield '8TH' => [NameSuffix::Eighth, 'VIII'];
        yield 'VIII' => [NameSuffix::VIII, 'VIII'];
        yield '9TH' => [NameSuffix::Ninth, 'IX'];
        yield 'IX' => [NameSuffix::IX, 'IX'];
    }

    #[DataProvider('icaoComponents')]
    public function testIcaoComponentUsesRomanNumerals(NameSuffix $suffix, string $expected): void
    {
        self::assertSame($expected, $suffix->icao());
    }

    public function testReadsACodeInAnyCase(): void
    {
        self::assertSame(NameSuffix::Junior, NameSuffix::fromCode(' jr '));
        self::assertSame(NameSuffix::Third, NameSuffix::fromCode('3rd'));
        self::assertSame(NameSuffix::IV, NameSuffix::fromCode('IV'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'period' => ['Jr.'];
        yield 'tenth' => ['10TH'];
        yield 'X' => ['X'];
        yield 'title' => ['Esq'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidCodes')]
    public function testRefusesACodeOffTheList(string $code): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): NameSuffix => NameSuffix::fromCode($code),
            $code,
            'Invalid American name suffix.',
        );
    }
}
