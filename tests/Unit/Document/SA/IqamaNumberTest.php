<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Document\SA\AbstractIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Document\SA\IqamaNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/SA.md "National ID and iqama": 10 digits, the first being 2 (ZATCA BR-KSA-F-11).
 * The numbers are made up for the tests.
 */
#[CoversClass(IqamaNumber::class)]
#[CoversClass(AbstractIdentityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class IqamaNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['2012345678', '2012345678'];
        yield 'spaces' => ['2012 3456 78', '2012345678'];
        yield 'hyphens' => ['201-234-5678', '2012345678'];
        yield 'NBSP and ideographic space' => ["2012\u{00A0}3456\u{3000}78", '2012345678'];
        yield 'fullwidth digits' => [
            "\u{FF12}\u{FF10}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}",
            '2012345678',
        ];
        yield 'Arabic-Indic digits' => ['٢٠١٢٣٤٥٦٧٨', '2012345678'];
        yield 'Extended Arabic-Indic digits' => ['۲۰۱۲۳۴۵۶۷۸', '2012345678'];
        yield 'mixed digit systems' => ["\u{0662}\u{0660}12\u{FF13}\u{0664}\u{FF15}678", '2012345678'];
        yield 'pasted with a right-to-left mark' => ["\u{200F}٢٠١٢٣٤٥٦٧٨", '2012345678'];
        yield 'wrapped in bidi isolates' => ["\u{2067}٢٠١٢٣٤٥٦٧٨\u{2069}", '2012345678'];
        yield 'Arabic letter mark between groups' => ["2012\u{061C}3456\u{200E}78", '2012345678'];
        yield 'zeros after the first digit' => ['2000000000', '2000000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = IqamaNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('******5678', IqamaNumber::from('2012345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Saudi Arabian iqama number (invalid character).';
        $first = 'Invalid Saudi Arabian iqama number (invalid first digit).';

        yield 'empty' => ['', 'Invalid Saudi Arabian iqama number (wrong length).'];
        yield '9 digits' => ['201234567', 'Invalid Saudi Arabian iqama number (wrong length).'];
        yield '11 digits' => ['20123456789', 'Invalid Saudi Arabian iqama number (wrong length).'];
        yield 'far too long' => ['2' . \str_repeat('0', 41), 'Invalid Saudi Arabian iqama number (wrong length).'];
        yield 'letter' => ['201234567X', $character];
        yield 'Arabic letter' => ["201234567\u{0639}", $character];
        yield 'full stop is no separator' => ['2012.345.678', 'Invalid Saudi Arabian iqama number (wrong length).'];
        yield 'a national ID number' => ['1012345678', $first];
        yield 'first digit 0' => ['0012345678', $first];
        yield 'first digit 3' => ['3012345678', $first];
        yield 'first digit 9' => ['9012345678', $first];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): IqamaNumber => IqamaNumber::from($value),
            $input,
            $message,
        );
    }
}
