<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LatinSpelling::class)]
#[CoversClass(IdentifierSpellings::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
final class LatinSpellingTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string TOO_MANY = 'Invalid personal name (too many transliteration variants).';

    public function testFindsTheSpellingAmongTheVariantsOfEachWord(): void
    {
        $primary = new IdentifierSpellings([['MUELLER', 'MULLER']], []);
        $secondary = new IdentifierSpellings([['ANNA'], ['MARIE', 'MARIA']], [' ']);

        self::assertSame('MULLER<<ANNA<MARIA', $this->find('MULLER<<ANNA<MARIA', $primary, $secondary));
        self::assertSame('MUELLER<<ANNA<MARIE', $this->find('MUELLER<<ANNA<MARIE', $primary, $secondary));
        self::assertNull($this->find('MULLER<<MARIE<ANNA', $primary, $secondary));
        self::assertNull($this->find('MILLER<<ANNA<MARIA', $primary, $secondary));
    }

    public function testFillerAndHyphenAreEquivalentAndTheNationalSeparatorIsReturned(): void
    {
        $hyphenated = new IdentifierSpellings([['RIMSKII'], ['KORSAKOV']], ['-']);
        $spaced = new IdentifierSpellings([['FON'], ['VIZIN']], [' ']);
        $given = new IdentifierSpellings([['ANNA'], ['MARIIA']], ['-']);

        self::assertSame(
            'RIMSKII-KORSAKOV<<ANNA-MARIIA',
            $this->find('RIMSKII<KORSAKOV<<ANNA<MARIIA', $hyphenated, $given),
        );
        self::assertSame('FON<VIZIN<<ANNA-MARIIA', $this->find('FON-VIZIN<<ANNA-MARIIA', $spaced, $given));
        self::assertNull($this->find('RIMSKII<<KORSAKOV<ANNA<MARIIA', $hyphenated, $given));
    }

    public function testTheWordCountsMustFit(): void
    {
        $primary = new IdentifierSpellings([['SMITH']], []);
        $secondary = new IdentifierSpellings([['ANNA'], ['MARIA'], ['JO']], [' ', ' ']);

        self::assertSame('SMITH<<ANNA<MARIA<JO', $this->find('SMITH<<ANNA<MARIA<JO', $primary, $secondary));
        self::assertNull($this->find('SMITH<<ANNA<MARIA', $primary, $secondary));
        self::assertNull($this->find('SMITH<<ANNA<MARIA<JO<JO', $primary, $secondary));
        self::assertNull($this->find('SMITH<JO<<ANNA<MARIA', $primary, $secondary));
        self::assertNull($this->find('SMITH', $primary, $secondary));
        self::assertNull($this->find('SMITH<<JO', $primary, null));
        self::assertSame('SMITH', $this->find('smith', $primary, null));
        self::assertNull($this->find('SMITH<JONES', $primary, null));
    }

    public function testVariantsAreComparedInUpperCase(): void
    {
        $primary = new IdentifierSpellings([['van'], ['der'], ['Berg']], [' ', ' ']);

        self::assertSame(
            'VAN<DER<BERG<<ANN',
            $this->find('VAN<DER<BERG<<ANN', $primary, new IdentifierSpellings([['Ann']], [])),
        );
    }

    public function testAWordWithExactlyTheCapIsSearched(): void
    {
        $variants = $this->variants(LatinSpelling::MAX_SPELLINGS);
        $last = $variants[\count($variants) - 1];

        self::assertSame(
            $last . '<<BA',
            $this->find(
                $last . '<<BA',
                new IdentifierSpellings([$variants], []),
                new IdentifierSpellings([['BA']], []),
            ),
        );
    }

    public function testWordsWhoseProductIsFarAboveTheCapAreMatchedWordByWord(): void
    {
        $variants = $this->variants(LatinSpelling::MAX_SPELLINGS);
        $last = $variants[\count($variants) - 1];
        $primary = new IdentifierSpellings([$variants, $variants], [' ']);
        $secondary = new IdentifierSpellings([$variants, $variants], [' ']);
        $latin = $last . '<' . $last . '<<' . $last . '<' . $last;

        self::assertSame($latin, $this->find($latin, $primary, $secondary));
        self::assertNull($this->find($last . '<' . $last . '<<' . $last . '<A', $primary, $secondary));
    }

    public function testAPrimaryWordAboveTheCapIsRefused(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): ?string => $this->find(
                'BA<BA<<BA',
                new IdentifierSpellings([['BA'], $this->variants(LatinSpelling::MAX_SPELLINGS + 1)], [' ']),
                new IdentifierSpellings([['BA']], []),
            ),
            '',
            self::TOO_MANY,
        );
    }

    public function testASecondaryWordAboveTheCapIsRefused(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): ?string => $this->find(
                'BA<<BA<BA',
                new IdentifierSpellings([['BA']], []),
                new IdentifierSpellings([['BA'], $this->variants(LatinSpelling::MAX_SPELLINGS + 1)], [' ']),
            ),
            '',
            self::TOO_MANY,
        );
    }

    private function find(string $latin, IdentifierSpellings $primary, ?IdentifierSpellings $secondary): ?string
    {
        return LatinSpelling::find(IcaoPersonName::from($latin), $primary, $secondary)?->value;
    }

    /**
     * @param positive-int $count
     *
     * @return non-empty-list<non-empty-string> distinct spellings: BAAA, BAAB, …, BAAZ, BABA, …
     */
    private function variants(int $count): array
    {
        $variants = [];
        for ($i = 0; $i < $count; $i++) {
            $variants[] = 'B' . \chr(65 + intdiv($i, 676) % 26) . \chr(65 + intdiv($i, 26) % 26) . \chr(65 + $i % 26);
        }

        \assert($variants !== []);

        return $variants;
    }
}
