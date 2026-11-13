<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ES;

use MSSTC4PHP\ValueObject\Documents\Document\ES\SocialSecurityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ES.md "Número de la Seguridad Social". Format only: any check digits are accepted.
 */
#[CoversClass(SocialSecurityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class SocialSecurityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'Madrid-style province' => ['281234567840', '281234567840', '28'];
        yield 'first province' => ['010000000000', '010000000000', '01'];
        yield 'province above the AFI message range' => ['569999999999', '569999999999', '56'];
        yield 'province 66' => ['661234567840', '661234567840', '66'];
        yield 'province 00' => ['001234567840', '001234567840', '00'];
        yield 'spaces between the fields' => ['28 12345678 40', '281234567840', '28'];
        yield 'NBSP and hyphen' => ["08\u{00A0}12345678-99", '081234567899', '08'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAnyCheckDigits(string $input, string $expected, string $province): void
    {
        $number = SocialSecurityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
        self::assertSame($province, $number->provinceCode());
    }

    public function testMasks(): void
    {
        self::assertSame('********5678', SocialSecurityNumber::from('281234565678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'eleven digits' => ['28123456784', 'Invalid Spanish social security number (11 characters).'];
        yield 'thirteen digits' => ['2812345678401', 'Invalid Spanish social security number (13 characters).'];
        yield 'slashes' => ['28/12345678/40', 'Invalid Spanish social security number (14 characters).'];
        yield 'letter' => ['28123456784A', 'Invalid Spanish social security number (invalid character).'];
        yield 'empty' => ['', 'Invalid Spanish social security number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): SocialSecurityNumber => SocialSecurityNumber::from($value),
            $input,
            $message,
        );
    }
}
