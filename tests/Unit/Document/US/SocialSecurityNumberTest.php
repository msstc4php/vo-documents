<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\SocialSecurityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "Social Security number": the SSA refuses area 000, 666 and 900-999, group
 * 00 and serial 0000; nothing else. The IRM's "never issued" illustrations (123-45-6789, 111-11-1111) are not a rule
 * and pass the format.
 */
#[CoversClass(SocialSecurityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class SocialSecurityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'display form' => ['123-45-6789', '123456789'];
        yield 'compact' => ['123456789', '123456789'];
        yield 'spaces and NBSP' => ["123 45\u{00A0}6789", '123456789'];
        yield 'area 001' => ['001-01-0001', '001010001'];
        yield 'area 665' => ['665-12-3456', '665123456'];
        yield 'area 667' => ['667-12-3456', '667123456'];
        yield 'area 899, assigned since 2011' => ['899-99-9999', '899999999'];
        yield 'one repeated digit (not an SSA rule)' => ['111-11-1111', '111111111'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = SocialSecurityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasksAsTheIrsTruncatedForm(): void
    {
        $number = SocialSecurityNumber::from('123456789');

        self::assertSame('123-45-6789', $number->formatted());
        self::assertSame('***-**-6789', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $area = 'Invalid American Social Security number (area number).';

        yield 'area 000' => ['000-12-3456', $area];
        yield 'area 666' => ['666-12-3456', $area];
        yield 'area 900 (ITIN shape)' => ['900-70-1234', $area];
        yield 'area 999' => ['999-12-3456', $area];
        yield 'an ITIN' => ['912-70-1234', $area];
        yield 'group 00' => ['123-00-4567', 'Invalid American Social Security number (group number).'];
        yield 'serial 0000' => ['123-45-0000', 'Invalid American Social Security number (serial number).'];
        yield 'letter' => ['123-45-678X', 'Invalid American Social Security number (invalid character).'];
        yield 'eight digits' => ['123-45-678', 'Invalid American Social Security number (wrong length).'];
        yield 'slash' => ['123/45/6789', 'Invalid American Social Security number (wrong length).'];
        yield 'empty' => ['', 'Invalid American Social Security number (wrong length).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid American Social Security number (wrong length).'];
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
