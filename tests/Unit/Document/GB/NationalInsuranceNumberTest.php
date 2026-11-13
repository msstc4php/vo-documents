<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\NationalInsuranceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "National Insurance number" (HMRC NIM39110). HMRC's illustration
 * `QQ 12 34 56 A` uses an excluded letter, so the valid examples are synthetic.
 */
#[CoversClass(NationalInsuranceNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NationalInsuranceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'display form' => ['AB 12 34 56 C', 'AB123456C'];
        yield 'compact, lower case' => ['ab123456c', 'AB123456C'];
        yield 'NBSP and hyphens' => ["AB\u{00A0}12-34-56 D", 'AB123456D'];
        yield 'suffix A' => ['CE 00 00 00 A', 'CE000000A'];
        yield 'O as the first letter' => ['OA 12 34 56 B', 'OA123456B'];
        yield 'PZ (not in the exclusion list)' => ['PZ 12 34 56 A', 'PZ123456A'];
        yield 'last letters of the alphabet' => ['YZ 99 99 99 D', 'YZ999999D'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = NationalInsuranceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAsTheHmrcLayout(): void
    {
        self::assertSame('AB 12 34 56 C', NationalInsuranceNumber::from('AB123456C')->formatted());
    }

    public function testMasksInTheHmrcLayout(): void
    {
        self::assertSame('** ** *4 56 C', NationalInsuranceNumber::from('AB123456C')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $prefix = 'Invalid British National Insurance number (prefix).';
        $character = 'Invalid British National Insurance number (invalid character).';

        yield 'HMRC illustration (Q excluded)' => ['QQ 12 34 56 A', $prefix];
        yield 'D first' => ['DA 12 34 56 A', $prefix];
        yield 'F second' => ['AF 12 34 56 A', $prefix];
        yield 'I first' => ['IA 12 34 56 A', $prefix];
        yield 'U second' => ['AU 12 34 56 A', $prefix];
        yield 'V first' => ['VA 12 34 56 A', $prefix];
        yield 'O second (administrative OO)' => ['AO 12 34 56 A', $prefix];
        yield 'BG' => ['BG 12 34 56 A', $prefix];
        yield 'GB' => ['GB 12 34 56 A', $prefix];
        yield 'KN' => ['KN 12 34 56 A', $prefix];
        yield 'NK' => ['NK 12 34 56 A', $prefix];
        yield 'NT' => ['NT 12 34 56 A', $prefix];
        yield 'TN (temporary number)' => ['TN 12 34 56 A', $prefix];
        yield 'ZZ' => ['ZZ 12 34 56 A', $prefix];
        yield 'suffix E' => ['AB 12 34 56 E', $character];
        yield 'PP999999P' => ['PP 99 99 99 P', $character];
        yield 'digit in the prefix' => ['A1 23 45 67 C', $character];
        yield 'letter among the digits' => ['AB 12 3X 56 C', $character];
        yield 'slash' => ['AB/123456C', 'Invalid British National Insurance number (10 characters).'];
        yield 'without suffix' => ['AB 12 34 56', 'Invalid British National Insurance number (8 characters).'];
        yield 'TRN' => ['11 a1 11 11', 'Invalid British National Insurance number (8 characters).'];
        yield 'empty' => ['', 'Invalid British National Insurance number (0 characters).'];
        yield 'far too long' => [\str_repeat('A', 50), 'Invalid British National Insurance number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): NationalInsuranceNumber => NationalInsuranceNumber::from($value),
            $input,
            $message,
        );
    }
}
