<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\AbstractRussianNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\Patronymic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md §1.
 */
#[CoversClass(AbstractNamePart::class)]
#[CoversClass(AbstractRussianNamePart::class)]
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(Patronymic::class)]
#[UsesClass(UnicodeText::class)]
final class NamePartsTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<AbstractNamePart>, string, string}>
     */
    public static function validParts(): iterable
    {
        yield 'given name' => [GivenName::class, 'Иван', 'Иван'];
        yield 'double surname' => [FamilyName::class, 'Римский-Корсаков', 'Римский-Корсаков'];
        yield 'surname with an apostrophe' => [FamilyName::class, "д'Артаньян", "д'Артаньян"];
        yield 'surname with a space' => [FamilyName::class, 'фон Визин', 'фон Визин'];
        yield 'hyphenated given name' => [GivenName::class, 'Анна-Мария', 'Анна-Мария'];
        yield 'several given names' => [GivenName::class, 'Анна Мария', 'Анна Мария'];
        yield 'patronymic' => [Patronymic::class, 'Иванович', 'Иванович'];
        yield 'patronymic of national custom' => [Patronymic::class, 'Гусейн оглы', 'Гусейн оглы'];
        yield 'Ё and soft sign' => [GivenName::class, 'Артём', 'Артём'];
        yield 'case kept' => [FamilyName::class, 'иВАНОВ', 'иВАНОВ'];
        yield 'whitespace normalised' => [GivenName::class, " Анна\u{00A0}\t Мария ", 'Анна Мария'];
        yield 'NFC й' => [GivenName::class, "Андреи\u{0306}", 'Андрей'];
        yield 'NFC ё' => [FamilyName::class, "Фе\u{0308}доров", 'Фёдоров'];
        yield 'U+2019 apostrophe stored as U+0027' => [FamilyName::class, "д\u{2019}Артаньян", "д'Артаньян"];
        yield 'word starting with Ы' => [FamilyName::class, 'Ытыкова', 'Ытыкова'];
        yield 'Ь and Ъ inside a word' => [FamilyName::class, 'Объедков-Ильин', 'Объедков-Ильин'];
    }

    /**
     * @param class-string<AbstractNamePart> $class
     */
    #[DataProvider('validParts')]
    public function testAcceptsAndNormalises(string $class, string $input, string $expected): void
    {
        self::assertSame($expected, $class::from($input)->value);
    }

    /**
     * @return iterable<string, array{class-string<AbstractNamePart>, string, string}>
     */
    public static function invalidParts(): iterable
    {
        yield 'Latin' => [GivenName::class, 'Ivan', 'Invalid Russian given name (4 characters).'];
        yield 'Latin a inside Cyrillic' => [GivenName::class, 'Ивaн', 'Invalid Russian given name (4 characters).'];
        yield 'Latin o inside a surname' => [FamilyName::class, 'Иванoв', 'Invalid Russian family name (6 characters).'];
        yield 'digit' => [GivenName::class, 'Иван2', 'Invalid Russian given name (5 characters).'];
        yield 'leading hyphen' => [GivenName::class, '-Иван', 'Invalid Russian given name (5 characters).'];
        yield 'trailing hyphen' => [FamilyName::class, 'Иванов-', 'Invalid Russian family name (7 characters).'];
        yield 'two separators' => [FamilyName::class, 'Иван--Петров', 'Invalid Russian family name (12 characters).'];
        yield 'hyphen then space' => [FamilyName::class, 'Иван- Петров', 'Invalid Russian family name (12 characters).'];
        yield 'empty' => [FamilyName::class, '  ', 'Invalid Russian family name (0 characters).'];
        yield 'Ukrainian Ї' => [GivenName::class, 'Їржи', 'Invalid Russian given name (4 characters).'];
        yield 'Cyrillic І' => [GivenName::class, 'Іван', 'Invalid Russian given name (4 characters).'];
        yield 'apostrophe in a given name' => [GivenName::class, "Д'Анна", 'Invalid Russian given name (6 characters).'];
        yield 'apostrophe in a patronymic' => [Patronymic::class, "Д'Иванович", 'Invalid Russian patronymic (10 characters).'];
        yield 'other punctuation' => [FamilyName::class, 'Иванов.', 'Invalid Russian family name (7 characters).'];
        yield 'soft sign alone' => [FamilyName::class, 'Ь', 'Invalid Russian family name (1 characters).'];
        yield 'word starting with Ь' => [FamilyName::class, 'Ь-Иванов', 'Invalid Russian family name (8 characters).'];
        yield 'second word starting with Ь' => [GivenName::class, 'Иван Ь', 'Invalid Russian given name (6 characters).'];
        yield 'word starting with Ъ' => [Patronymic::class, 'Ъеремеевич', 'Invalid Russian patronymic (10 characters).'];
        yield 'lower-case ь first' => [GivenName::class, 'ьван', 'Invalid Russian given name (4 characters).'];
        yield 'U+2019 in a given name' => [GivenName::class, "Д\u{2019}Анна", 'Invalid Russian given name (6 characters).'];
        yield 'separator alone' => [FamilyName::class, "'", 'Invalid Russian family name (1 characters).'];
    }

    /**
     * @param class-string<AbstractNamePart> $class
     */
    #[DataProvider('invalidParts')]
    public function testRefusesWithoutEchoingTheInput(string $class, string $input, string $message): void
    {
        try {
            $class::from($input);
            self::fail('Expected an exception.');
        } catch (InvalidPersonNameException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }

    public function testPartsOfDifferentKindsAreNotEqual(): void
    {
        self::assertFalse(GivenName::from('Иван')->equals(FamilyName::from('Иван')));
        self::assertTrue(GivenName::from('Иван')->equals(GivenName::from(' Иван ')));
    }
}
