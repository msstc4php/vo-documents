<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Document\SA\AbstractIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Document\SA\NationalIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/SA.md "National ID and iqama": 10 digits, the first being 1 (ZATCA BR-KSA-F-10).
 * The numbers are made up for the tests.
 */
#[CoversClass(NationalIdentityNumber::class)]
#[CoversClass(AbstractIdentityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NationalIdentityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['1012345678', '1012345678'];
        yield 'spaces' => ['1012 3456 78', '1012345678'];
        yield 'hyphens' => ['101-234-5678', '1012345678'];
        yield 'NBSP and ideographic space' => ["1012\u{00A0}3456\u{3000}78", '1012345678'];
        yield 'fullwidth digits' => [
            "\u{FF11}\u{FF10}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}",
            '1012345678',
        ];
        yield 'Arabic-Indic digits' => ['١٠١٢٣٤٥٦٧٨', '1012345678'];
        yield 'Extended Arabic-Indic digits' => ['۱۰۱۲۳۴۵۶۷۸', '1012345678'];
        yield 'mixed digit systems' => ["\u{0661}\u{0660}12\u{FF13}\u{0664}\u{FF15}678", '1012345678'];
        yield 'zeros after the first digit' => ['1000000000', '1000000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = NationalIdentityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('******5678', NationalIdentityNumber::from('1012345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Saudi Arabian national identity number (invalid character).';
        $first = 'Invalid Saudi Arabian national identity number (invalid first digit).';

        yield 'empty' => ['', 'Invalid Saudi Arabian national identity number (0 characters).'];
        yield '9 digits' => ['101234567', 'Invalid Saudi Arabian national identity number (9 characters).'];
        yield '11 digits' => ['10123456789', 'Invalid Saudi Arabian national identity number (11 characters).'];
        yield 'far too long' => [
            '1' . \str_repeat('0', 41),
            'Invalid Saudi Arabian national identity number (too long).',
        ];
        yield 'letter' => ['101234567X', $character];
        yield 'Arabic letter' => ["101234567\u{0639}", $character];
        yield 'full stop is no separator' => [
            '1012.345.678',
            'Invalid Saudi Arabian national identity number (12 characters).',
        ];
        yield 'an iqama number' => ['2012345678', $first];
        yield 'first digit 0' => ['0012345678', $first];
        yield 'first digit 3' => ['3012345678', $first];
        yield 'first digit 9' => ['9012345678', $first];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): NationalIdentityNumber => NationalIdentityNumber::from($value),
            $input,
            $message,
        );
    }
}
