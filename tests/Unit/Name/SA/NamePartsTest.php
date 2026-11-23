<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\SA;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\SA\AbstractArabicNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\SA\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\FatherName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\GrandfatherName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/SA.md "Names": letters of Script=Arabic plus the harakat U+064B-0652 and the
 * superscript alef U+0670, tatweel removed.
 */
#[CoversClass(AbstractArabicNamePart::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(FatherName::class)]
#[CoversClass(GrandfatherName::class)]
#[CoversClass(FamilyName::class)]
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
        yield 'plain' => ['محمد', 'محمد'];
        yield 'two words' => ['عبد الله', 'عبد الله'];
        yield 'one word' => ['عبدالله', 'عبدالله'];
        yield 'particle Al as a word' => ['آل سعود', 'آل سعود'];
        yield 'particle bin' => ['بن لادن', 'بن لادن'];
        yield 'hyphen' => ['عبد-الرحمن', 'عبد-الرحمن'];
        yield 'surrounding and inner whitespace' => ["  عبد \u{00A0} الله\t", 'عبد الله'];
        yield 'harakat kept, shadda ordered after fatha by NFC' => [
            "\u{0645}\u{064F}\u{062D}\u{064E}\u{0645}\u{0651}\u{064E}\u{062F}",
            "\u{0645}\u{064F}\u{062D}\u{064E}\u{0645}\u{064E}\u{0651}\u{062F}",
        ];
        yield 'harakat on the last letter' => [
            "\u{0639}\u{064E}\u{0644}\u{0650}\u{064A}\u{0651}",
            "\u{0639}\u{064E}\u{0644}\u{0650}\u{064A}\u{0651}",
        ];
        yield 'superscript alef kept' => [
            "\u{0627}\u{0644}\u{0631}\u{062D}\u{0645}\u{0670}\u{0646}",
            "\u{0627}\u{0644}\u{0631}\u{062D}\u{0645}\u{0670}\u{0646}",
        ];
        yield 'one letter with a haraka' => ["\u{0628}\u{064E}", "\u{0628}\u{064E}"];
        yield 'tatweel removed' => ["\u{0645}\u{0640}\u{062D}\u{0640}\u{0645}\u{0640}\u{062F}", 'محمد'];
        yield 'tatweel between words' => [
            "\u{0639}\u{0640}\u{0628}\u{062F} \u{0627}\u{0644}\u{0644}\u{0647}",
            'عبد الله',
        ];
        yield 'alef with hamza above composed' => [
            "\u{0623}\u{062D}\u{0645}\u{062F}",
            "\u{0623}\u{062D}\u{0645}\u{062F}",
        ];
        yield 'alef with hamza above decomposed' => [
            "\u{0627}\u{0654}\u{062D}\u{0645}\u{062F}",
            "\u{0623}\u{062D}\u{0645}\u{062F}",
        ];
        yield 'alef with hamza below decomposed' => [
            "\u{0627}\u{0655}\u{0628}\u{0631}\u{0627}\u{0647}\u{064A}\u{0645}",
            "\u{0625}\u{0628}\u{0631}\u{0627}\u{0647}\u{064A}\u{0645}",
        ];
        yield 'alef with madda decomposed' => ["\u{0627}\u{0653}\u{0644}", "\u{0622}\u{0644}"];
        yield 'waw with hamza decomposed' => [
            "\u{0645}\u{0648}\u{0654}\u{064A}\u{062F}",
            "\u{0645}\u{0624}\u{064A}\u{062F}",
        ];
        yield 'taa marbuta' => ['فاطمة', 'فاطمة'];
        yield 'alef maksura' => ['موسى', 'موسى'];
        yield 'Persian kaf stored as the Arabic kaf' => ["\u{06A9}ريم", "\u{0643}ريم"];
        yield 'Persian yeh stored as the Arabic yeh' => ["\u{06CC}وسف", "\u{064A}وسف"];
        yield 'Persian kaf and yeh with harakat' => [
            "\u{06A9}\u{064E}\u{0631}\u{06CC}\u{0645}",
            "\u{0643}\u{064E}\u{0631}\u{064A}\u{0645}",
        ];
    }

    #[DataProvider('validNames')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        self::assertSame($expected, GivenName::from($input)->value);
        self::assertSame($expected, FatherName::from($input)->value);
        self::assertSame($expected, GrandfatherName::from($input)->value);
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testComposedAndDecomposedHamzaAreOneValue(): void
    {
        self::assertTrue(GivenName::from("\u{0627}\u{0654}\u{062D}\u{0645}\u{062F}")->equals(GivenName::from('أحمد')));
    }

    public function testStretchedAndPlainSpellingAreOneValue(): void
    {
        self::assertTrue(GivenName::from("\u{0645}\u{0640}\u{062D}\u{0645}\u{062F}")->equals(GivenName::from('محمد')));
    }

    public function testPersianAndArabicKafAndYehAreOneValue(): void
    {
        self::assertTrue(GivenName::from("\u{06A9}\u{0631}\u{06CC}\u{0645}")->equals(GivenName::from('كريم')));
    }

    public function testAWordFinalPersianYehIsStoredAsYehNotAsAlefMaqsura(): void
    {
        self::assertSame("\u{0645}\u{0635}\u{0637}\u{0641}\u{064A}", GivenName::from("مصطف\u{06CC}")->value);
        self::assertFalse(GivenName::from("مصطف\u{06CC}")->equals(GivenName::from("مصطف\u{0649}")));
    }

    public function testTatweelBetweenALetterAndItsHarakatIsRemoved(): void
    {
        self::assertSame(
            "\u{0645}\u{064F}\u{062D}\u{064E}\u{0645}\u{064E}\u{0651}\u{062F}",
            GivenName::from("\u{0645}\u{064F}\u{0640}\u{062D}\u{064E}\u{0645}\u{0651}\u{064E}\u{062F}")->value,
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        $character = 'Invalid Saudi Arabian given name (invalid character).';
        $compatibility = 'Invalid Saudi Arabian given name (compatibility character).';

        yield 'empty' => ['', 'Invalid Saudi Arabian given name (empty).'];
        yield 'blank' => [" \u{00A0}\t", 'Invalid Saudi Arabian given name (empty).'];
        yield 'only tatweel' => ["\u{0640}\u{0640}", 'Invalid Saudi Arabian given name (empty).'];
        yield 'too long' => [\str_repeat('ع', 101), 'Invalid Saudi Arabian given name (too long).'];
        yield 'Latin' => ['Mohammed', $character];
        yield 'Arabic and Latin in one name' => ['محمد Ali', $character];
        yield 'Latin letter inside an Arabic word' => ['محaمد', $character];
        yield 'Han' => ['金', $character];
        yield 'Devanagari' => ['राजेश', $character];
        yield 'Hebrew' => ['משה', $character];
        yield 'Arabic-Indic digit' => ['محمد ٣', $character];
        yield 'Extended Arabic-Indic digit' => ['محمد ۳', $character];
        yield 'ASCII digit' => ['محمد3', $character];
        yield 'Arabic comma' => ['محمد، علي', $character];
        yield 'Arabic full stop' => ["\u{0645}\u{06D4}", $character];
        yield 'Arabic question mark' => ['محمد؟', $character];
        yield 'ASCII full stop' => ['محمد.', $character];
        yield 'apostrophe is no separator' => ["عبد'الله", $character];
        yield 'two hyphens in a row' => ['عبد--الله', $character];
        yield 'leading hyphen' => ['-عبد', $character];
        yield 'trailing hyphen' => ['عبد-', $character];
        yield 'lone fatha' => ["\u{064E}", $character];
        yield 'fatha at the start of a word' => ["\u{064E}\u{0645}\u{062D}\u{0645}\u{062F}", $character];
        yield 'fatha after a space' => [
            "\u{0645}\u{062D}\u{0645}\u{062F} \u{064E}\u{0639}\u{0644}\u{064A}",
            $character,
        ];
        yield 'fatha after a hyphen' => [
            "\u{0639}\u{0628}\u{062F}-\u{064E}\u{0627}\u{0644}\u{0644}\u{0647}",
            $character,
        ];
        yield 'maddah above after a letter that does not compose' => ["\u{0628}\u{0653}", $character];
        yield 'Quranic small high sign' => ["\u{0645}\u{06D6}", $character];
        yield 'zero-width joiner' => ["\u{0645}\u{200D}\u{062D}", $character];
        yield 'zero-width non-joiner' => ["\u{0645}\u{200C}\u{062D}", $character];
        yield 'right-to-left mark' => ["\u{200F}\u{0645}\u{062D}\u{0645}\u{062F}", $character];
        yield 'Arabic number sign' => ["\u{0600}\u{0645}\u{062D}\u{0645}\u{062F}", $character];
        yield 'presentation form isolated meem' => ["\u{FEE1}", $compatibility];
        yield 'presentation form ligature lam-alef' => ["\u{FEFB}", $compatibility];
        yield 'ligature Allah U+FDF2' => ["\u{FDF2}", $compatibility];
        yield 'emoji' => ["\u{0645}\u{062D}\u{0645}\u{062F}\u{1F600}", $character];
    }

    #[DataProvider('invalidNames')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenName => GivenName::from($input),
            $input,
            $message,
        );
    }

    public function testEachPartNamesItselfInTheMessage(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): FatherName => FatherName::from('Ali'),
            'Ali',
            'Invalid Saudi Arabian father name (invalid character).',
        );
        self::assertNameRefusedWithoutEcho(
            static fn (): GrandfatherName => GrandfatherName::from('Ali'),
            'Ali',
            'Invalid Saudi Arabian grandfather name (invalid character).',
        );
        self::assertNameRefusedWithoutEcho(
            static fn (): FamilyName => FamilyName::from('Ali'),
            'Ali',
            'Invalid Saudi Arabian family name (invalid character).',
        );
    }
}
