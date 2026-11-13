<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\TaxFileNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Tax file number".
 */
#[CoversClass(TaxFileNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class TaxFileNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'worked example' => ['123 456 782', '123456782'];
        yield '1982 notice of amended assessment' => ['330 600 376', '330600376'];
        yield 'digits only' => ['123456782', '123456782'];
        yield 'hyphens, tabs and NBSP' => ["123-456\t78\u{00A0}2", '123456782'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $tfn = TaxFileNumber::from($input);

        self::assertSame($expected, $tfn->value);
        self::assertSame($expected, (string) $tfn);
        self::assertSame($expected, $tfn->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $tfn = TaxFileNumber::from('123456782');

        self::assertSame('123 456 782', $tfn->formatted());
        self::assertSame('*** *** 782', $tfn->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'last digit changed' => ['123 456 783', 'Invalid Australian tax file number checksum.'];
        yield 'first digit changed' => ['223 456 782', 'Invalid Australian tax file number checksum.'];
        yield 'two digits swapped' => ['213 456 782', 'Invalid Australian tax file number checksum.'];
        yield 'eight digits' => ['12 345 678', 'Invalid Australian tax file number (8 characters).'];
        yield 'ten digits' => ['123 456 7820', 'Invalid Australian tax file number (10 characters).'];
        yield 'letter' => ['123 456 78O', 'Invalid Australian tax file number (invalid character).'];
        yield 'empty' => ['', 'Invalid Australian tax file number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): TaxFileNumber => TaxFileNumber::from($value),
            $input,
            $message,
        );
    }
}
