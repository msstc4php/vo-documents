<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Nis;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "NIS (PIS/PASEP/NIT)": eleven digits, format only. `11816788893` is the
 * eSocial manual's sample NIS.
 */
#[CoversClass(Nis::class)]
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
        yield 'any check digit' => ['11816788890', '11816788890'];
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
        yield "ten digits, the eSocial manual's rejected sample" => ['1800474420', 'Invalid Brazilian NIS (10 characters).'];
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
