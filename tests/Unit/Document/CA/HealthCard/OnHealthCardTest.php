<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbstractHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCard;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardVersionCode;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CA.md, CA-ON row and the "ON" decision: ON-HCV "When present on the card, a version
 * code is one letter or two letters"; red-and-white cards may have none. `9876543217` is the ON-IHCS example.
 */
#[CoversClass(OnHealthCard::class)]
#[CoversClass(OnHealthCardNumber::class)]
#[CoversClass(OnHealthCardVersionCode::class)]
#[UsesClass(AbstractHealthCardNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class OnHealthCardTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function printedCards(): iterable
    {
        yield 'none (red-and-white card)' => ['9876543217', null];
        yield 'one letter' => ['9876543217A', 'A'];
        yield 'two letters' => ['9876543217YM', 'YM'];
        yield 'spaced, lower-case' => ['9876-543-217 ym', 'YM'];
    }

    #[DataProvider('printedCards')]
    public function testSplitsThePrintedCard(string $printed, ?string $versionCode): void
    {
        $card = OnHealthCardNumber::fromCard($printed);

        self::assertSame('9876543217', $card->number()->value);
        self::assertSame($versionCode, $card->versionCode()?->value);
    }

    public function testTwoCardsOfOnePersonShareTheNumberAndDifferInTheVersionCode(): void
    {
        $old = OnHealthCardNumber::fromCard('9876543217AB');
        $new = OnHealthCardNumber::fromCard('9876543217YM');

        self::assertTrue($old->number()->equals($new->number()));
        self::assertTrue($new->number()->equals(OnHealthCardNumber::from('9876543217')));
        self::assertFalse(OnHealthCardVersionCode::from('AB')->equals(OnHealthCardVersionCode::from('YM')));
        self::assertSame('AB', $old->versionCode()?->value);
        self::assertSame('YM', $new->versionCode()?->value);
    }

    public function testKeepsTheGivenParts(): void
    {
        $number = OnHealthCardNumber::from('9876543217');
        $code = OnHealthCardVersionCode::from('YM');
        $card = new OnHealthCard($number, $code);

        self::assertSame($number, $card->number());
        self::assertSame($code, $card->versionCode());
        self::assertNull(new OnHealthCard($number, null)->versionCode());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function refusedCards(): iterable
    {
        $document = 'Invalid CA-ON health card number';

        yield 'wrong check digit' => ['9876543218YM', $document . ' checksum.'];
        yield 'three letters' => ['9876543217ABC', $document . ' (13 characters).'];
        yield 'digit as version code' => ['98765432171', $document . ' (invalid character).'];
        yield 'leading zero' => ['0123456789', $document . ' (invalid character).'];
        yield 'empty' => ['', $document . ' (0 characters).'];
    }

    #[DataProvider('refusedCards')]
    public function testRefusesABadCardWithoutEchoingIt(string $printed, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): OnHealthCard => OnHealthCardNumber::fromCard($value),
            $printed,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function versionCodes(): iterable
    {
        yield 'one letter' => ['A', 'A'];
        yield 'two letters' => ['YM', 'YM'];
        yield 'upper-cased, separators removed' => [' y-m ', 'YM'];
    }

    #[DataProvider('versionCodes')]
    public function testAcceptsAVersionCodeOfOneOrTwoLetters(string $input, string $expected): void
    {
        $code = OnHealthCardVersionCode::from($input);

        self::assertSame($expected, $code->value);
        self::assertSame($expected, (string) $code);
        self::assertSame($expected, $code->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function refusedVersionCodes(): iterable
    {
        $document = 'Invalid CA-ON health card version code';

        yield 'empty' => ['', $document . ' (0 characters).'];
        yield 'three letters' => ['ABC', $document . ' (3 characters).'];
        yield 'digit' => ['1', $document . ' (invalid character).'];
        yield 'letter and digit' => ['A1', $document . ' (invalid character).'];
        yield 'non-ASCII letter' => ['É', $document . ' (invalid character).'];
    }

    #[DataProvider('refusedVersionCodes')]
    public function testRefusesAnotherVersionCodeWithoutEchoingIt(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): OnHealthCardVersionCode => OnHealthCardVersionCode::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function maskedVersionCodes(): iterable
    {
        yield 'one letter' => ['A', '*'];
        yield 'two letters' => ['YM', '**'];
    }

    #[DataProvider('maskedVersionCodes')]
    public function testMasksEveryLetterOfTheVersionCode(string $input, string $masked): void
    {
        self::assertSame($masked, OnHealthCardVersionCode::from($input)->masked());
    }
}
