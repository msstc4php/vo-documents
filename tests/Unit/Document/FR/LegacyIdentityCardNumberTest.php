<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Document\FR\LegacyIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/FR.md "Carte nationale d'identité".
 */
#[CoversClass(LegacyIdentityCardNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class LegacyIdentityCardNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'twelve digits' => ['880692302345', '880692302345'];
        yield 'with spaces, NBSP' => ["8806 923\u{00A0}02345", '880692302345'];
        yield 'January' => ['990175100001', '990175100001'];
        yield 'December' => ['011275100001', '011275100001'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheDigits(string $input, string $expected): void
    {
        $number = LegacyIdentityCardNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testExposesTheFieldsOfTheInstruction(): void
    {
        $number = LegacyIdentityCardNumber::from('880692302345');

        self::assertSame('88', $number->issueYearDigits());
        self::assertSame(6, $number->issueMonth());
        self::assertSame('923', $number->issuePlaceCode());
        self::assertSame('02345', $number->serialNumber());
        self::assertSame(12, LegacyIdentityCardNumber::from('011275100001')->issueMonth());
    }

    public function testMasks(): void
    {
        self::assertSame('********2345', LegacyIdentityCardNumber::from('880692302345')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $month = 'Invalid French identity card number (invalid issue month).';

        yield 'month 00' => ['880092302345', $month];
        yield 'month 13' => ['881392302345', $month];
        yield 'thirteen digits (with the MRZ check digit)' => [
            '8806923023451',
            'Invalid French identity card number (13 characters).',
        ];
        yield 'nine-character 2021 card number' => [
            'X4RTBPFW4',
            'Invalid French identity card number (9 characters).',
        ];
        yield 'Corsican letter in the place code' => [
            '88062A102345',
            'Invalid French identity card number (invalid character).',
        ];
        yield 'empty' => ['', 'Invalid French identity card number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): LegacyIdentityCardNumber => LegacyIdentityCardNumber::from($value),
            $input,
            $message,
        );
    }
}
