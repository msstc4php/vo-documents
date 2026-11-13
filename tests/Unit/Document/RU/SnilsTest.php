<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\Snils;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "SNILS".
 */
#[CoversClass(Snils::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
final class SnilsTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'official example' => ['112-233-445 95', '11223344595'];
        yield 'digits only' => ['11223344595', '11223344595'];
        yield 'spaces' => ['112 233 445 95', '11223344595'];
        yield 'hyphens only' => ['112-233-445-95', '11223344595'];
        yield 'tabs, NBSP and outer spaces' => [" 112\t233\u{00A0}445\u{202F}95 ", '11223344595'];
        yield 'sum below 100' => ['011-650-257 99', '01165025799'];
        yield 'sum 100 gives 00' => ['141-008-408 00', '14100840800'];
        yield 'sum 101 gives 00' => ['205-132-059 00', '20513205900'];
        yield 'sum 102 gives 01' => ['030-214-885 01', '03021488501'];
        yield 'remainder 100 gives 00' => ['952-530-077 00', '95253007700'];
        yield 'last unchecked number, any control' => ['001-001-998 12', '00100199812'];
        yield 'small number, any control' => ['000-000-001 00', '00000000100'];
        yield 'first checked number' => ['001-001-999 65', '00100199965'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $snils = Snils::from($input);

        self::assertSame($expected, $snils->value);
        self::assertSame($expected, (string) $snils);
        self::assertSame($expected, $snils->jsonSerialize());
    }

    public function testFormatsAndMasksLikeTheOfficialExample(): void
    {
        $snils = Snils::from('11223344595');

        self::assertSame('112-233-445 95', $snils->formatted());
        self::assertSame('***-***-*45 95', $snils->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'control digit changed' => ['112-233-445 96', 'Invalid SNILS checksum.'];
        yield 'number digit changed' => ['112-233-446 95', 'Invalid SNILS checksum.'];
        yield 'first checked number with a wrong control' => ['001-001-999 64', 'Invalid SNILS checksum.'];
        yield 'remainder 100 written as 100 mod 101' => ['141-008-408 99', 'Invalid SNILS checksum.'];
        yield 'zeros only' => ['000-000-000 00', 'Invalid SNILS (zeros only).'];
        yield 'too short' => ['112-233-445 9', 'Invalid SNILS (10 characters).'];
        yield 'too long' => ['112-233-445 950', 'Invalid SNILS (12 characters).'];
        yield 'letters' => ['112-233-445 9O', 'Invalid SNILS (11 characters).'];
        yield 'Cyrillic letter' => ['112-233-445 9О', 'Invalid SNILS (11 characters).'];
        yield 'other separator' => ['112.233.445 95', 'Invalid SNILS (13 characters).'];
        yield 'empty' => ['', 'Invalid SNILS (0 characters).'];
        yield 'pasted several times' => [\str_repeat('112-233-445 95 ', 4), 'Invalid SNILS (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Snils => Snils::from($value), $input, $message);
    }
}
