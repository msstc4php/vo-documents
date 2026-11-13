<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AU\FamilyName as AustralianFamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\LatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\SingleWordNamePart;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(AustralianFamilyName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class AbstractNamePartTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    public function testSeparatorsAreTakenLiterally(): void
    {
        self::assertSame('ab/cd.ef]gh', LatinNamePart::from('ab/cd.ef]gh')->value);
        self::assertFalse(LatinNamePart::isValid('ab-cd'));
        self::assertFalse(LatinNamePart::isValid('ab\\cd'));
    }

    public function testNoSeparatorsMeansOneWord(): void
    {
        self::assertSame('xabc', SingleWordNamePart::from('xabc')->value);
        self::assertSame('y', SingleWordNamePart::from('y')->value);
        self::assertFalse(SingleWordNamePart::isValid('xa c'));
    }

    public function testTheWordPatternIsAppliedAsAWhole(): void
    {
        self::assertFalse(SingleWordNamePart::isValid('abc'));
        self::assertFalse(SingleWordNamePart::isValid('xab1'));
        self::assertFalse(SingleWordNamePart::isValid('1y'));
    }

    public function testTheLongestAcceptedPartHasMaxLengthCharacters(): void
    {
        $longest = \str_repeat('a', AbstractNamePart::MAX_LENGTH);

        self::assertSame($longest, LatinNamePart::from($longest)->value);
        self::assertFalse(LatinNamePart::isValid($longest . 'a'));
    }

    public function testTheLimitCountsCharactersNotBytes(): void
    {
        self::assertTrue(FamilyName::isValid('И' . \str_repeat('в', AbstractNamePart::MAX_LENGTH - 1)));
        self::assertFalse(FamilyName::isValid('И' . \str_repeat('в', AbstractNamePart::MAX_LENGTH)));
    }

    /**
     * @return iterable<string, array{string, non-empty-string}>
     */
    public static function refusals(): iterable
    {
        yield 'too long' => [\str_repeat('a', AbstractNamePart::MAX_LENGTH + 1), 'Invalid test name (101 characters).'];
        yield 'empty' => ['  ', 'Invalid test name (0 characters).'];
        yield 'invalid character at the right length' => ['abc1', 'Invalid test name (invalid character).'];
        yield 'separator at the end' => ['abc/', 'Invalid test name (invalid character).'];
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('refusals')]
    public function testTheMessageTellsALengthFromACharacterProblem(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): LatinNamePart => LatinNamePart::from($input),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function compatibilityCharacters(): iterable
    {
        yield 'fullwidth letters' => ["\u{FF33}\u{FF4D}\u{FF49}\u{FF54}\u{FF48}"];
        yield 'fullwidth letter inside a name' => ["Sm\u{FF49}th"];
        yield 'feminine ordinal indicator' => ["M\u{00AA}ria"];
        yield 'ligature fi' => ["\u{FB01}nch"];
        yield 'long s' => ["Ro\u{017F}e"];
    }

    /**
     * @param non-empty-string $input
     */
    #[DataProvider('compatibilityCharacters')]
    public function testACompatibilityCharacterIsRefusedWithoutEchoingIt(string $input): void
    {
        try {
            AustralianFamilyName::from($input);
        } catch (InvalidPersonNameException $exception) {
            self::assertSame('Invalid Australian family name (compatibility character).', $exception->getMessage());

            return;
        }

        self::fail('Expected an InvalidPersonNameException.');
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function tableListedCompatibilityCharacters(): iterable
    {
        yield 'ligature IJ' => ["\u{0132}sselmeer"];
        yield 'lower-case ligature ij' => ["V\u{0133}ver"];
        yield 'L with middle dot' => ["\u{013F}lobet"];
    }

    /**
     * @param non-empty-string $input
     */
    #[DataProvider('tableListedCompatibilityCharacters')]
    public function testACompatibilityCharacterListedByAnIcaoTableIsKept(string $input): void
    {
        self::assertSame($input, AustralianFamilyName::from($input)->value);
    }

    public function testTheCompatibilityCheckDoesNotTouchCanonicalLetters(): void
    {
        self::assertSame('Nguyễn Șerban', AustralianFamilyName::from("Nguye\u{0302}\u{0303}n S\u{0326}erban")->value);
    }
}
