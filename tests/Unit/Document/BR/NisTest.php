<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Nis;
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
 * Expectations follow docs/sources/BR.md "NIS (PIS/PASEP/NIT)": eleven digits and the ANS `isDvPisPasepValido` check
 * digit. `11816788893` is the eSocial manual's sample NIS (sum 283, 283 mod 11 = 8, 11 - 8 = 3).
 */
#[CoversClass(Nis::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NisTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'eSocial manual sample' => ['11816788893', '11816788893'];
        yield 'dots and hyphen' => ['118.16788.89-3', '11816788893'];
        yield 'remainder 0 gives 0' => ['10000000040', '10000000040'];
        yield 'remainder 1 gives 0' => ['10000000130', '10000000130'];
        yield 'remainder 2 gives 9' => ['10000000059', '10000000059'];
        yield 'repeated digits pass the arithmetic, accepted' => ['00000000000', '00000000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = Nis::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasksKeepingFourDigits(): void
    {
        $number = Nis::from('11816788893');

        self::assertSame('118.16788.89-3', $number->formatted());
        self::assertSame('***.****8.89-3', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield "ten digits, the eSocial manual's rejected sample" => [
            '1800474420',
            'Invalid Brazilian NIS (10 characters).',
        ];
        yield 'wrong check digit' => ['11816788890', 'Invalid Brazilian NIS checksum.'];
        yield 'base digit changed' => ['11816788983', 'Invalid Brazilian NIS checksum.'];
        yield 'remainder 1 written as 10 minus one' => ['10000000139', 'Invalid Brazilian NIS checksum.'];
        yield 'twelve digits' => ['118167888930', 'Invalid Brazilian NIS (12 characters).'];
        yield 'letter' => ['1181678889X', 'Invalid Brazilian NIS (invalid character).'];
        yield 'empty' => ['', 'Invalid Brazilian NIS (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Nis => Nis::from($value), $input, $message);
    }
}
