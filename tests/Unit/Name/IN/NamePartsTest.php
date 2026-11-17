<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IN;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Surname;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Names": Latin letters, words joined by a space, hyphen or apostrophe.
 */
#[CoversClass(GivenName::class)]
#[CoversClass(Surname::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class NamePartsTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNames(): iterable
    {
        yield 'one word' => ['Rajesh', 'Rajesh'];
        yield 'two words, spaces collapsed' => ['  Piyush   Kumar ', 'Piyush Kumar'];
        yield 'surname of two words (MEA: Roy Choudhary)' => ['Roy Choudhary', 'Roy Choudhary'];
        yield 'surname Das Gupta' => ['Das Gupta', 'Das Gupta'];
        yield 'initial as a letter (MEA: initials are not written, but an existing name is not refused)' => [
            'Piyush K',
            'Piyush K',
        ];
        yield 'initials as letters' => ['P K', 'P K'];
        yield 'hyphen' => ['Roy-Choudhary', 'Roy-Choudhary'];
        yield 'apostrophe, typographic form stored as ASCII' => ["D\u{2019}Souza", "D'Souza"];
        yield 'diacritic' => ['Sreeniváisan', 'Sreeniváisan'];
        yield 'NBSP between words' => ["Piyush\u{00A0}Kumar", 'Piyush Kumar'];
        yield 'decomposed accent is composed' => ["Sre\u{0065}\u{0301}", 'Sreé'];
    }

    #[DataProvider('validNames')]
    public function testNamesAreNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, GivenName::from($input)->value);
        self::assertSame($expected, Surname::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        yield 'empty' => ['', '0 characters'];
        yield 'blank' => ['   ', '0 characters'];
        yield 'too long' => [\str_repeat('a', 101), 'too long'];
        yield 'Devanagari' => ['राजेश', 'invalid character'];
        yield 'Devanagari among Latin' => ['Rajेश', 'invalid character'];
        yield 'digit' => ['Rajesh2', 'invalid character'];
        yield 'full stop (an initial)' => ['P.K.', 'invalid character'];
        yield 'comma' => ['Jain, Piyush', 'invalid character'];
        yield 'leading hyphen' => ['-Rajesh', 'invalid character'];
        yield 'doubled hyphen' => ['Roy--Choudhary', 'invalid character'];
        yield 'Cyrillic' => ['Раджеш', 'invalid character'];
        yield 'fullwidth Latin' => ["\u{FF30}\u{FF49}\u{FF59}\u{FF55}\u{FF53}\u{FF48}", 'compatibility character'];
        yield 'zero-width joiner' => ["Raj\u{200D}esh", 'invalid character'];
    }

    #[DataProvider('invalidNames')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $reason): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenName => GivenName::from($input),
            $input,
            \sprintf('Invalid Indian given name (%s).', $reason),
        );
        self::assertNameRefusedWithoutEcho(
            static fn (): Surname => Surname::from($input),
            $input,
            \sprintf('Invalid Indian surname (%s).', $reason),
        );
    }
}
