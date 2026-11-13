<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LatinSpelling::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
final class LatinSpellingTest extends TestCase
{
    public function testFindsTheSpellingInAnyFormAndVariant(): void
    {
        $forms = [
            [['MULLER', 'MUELLER'], ['ANNA']],
            [['MULLER', 'MUELLER'], ['ANNA'], ['MARIA', 'MARIE']],
        ];

        self::assertSame('MUELLER<<ANNA', LatinSpelling::find(IcaoPersonName::from('MUELLER<<ANNA'), $forms)?->value);
        self::assertSame(
            'MUELLER<<ANNA<MARIE',
            LatinSpelling::find(IcaoPersonName::from('MUELLER<<ANNA<MARIE'), $forms)?->value,
        );
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('MULLER<<MARIE'), $forms));
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('MULLER<<ANNA<MARIA<MARIE'), $forms));
    }

    public function testFillerAndHyphenAreEquivalentAndTheNationalFormIsReturned(): void
    {
        $forms = [[['RIMSKII-KORSAKOV'], ['ANNA-MARIIA']]];

        self::assertSame(
            'RIMSKII-KORSAKOV<<ANNA-MARIIA',
            LatinSpelling::find(IcaoPersonName::from('RIMSKII<KORSAKOV<<ANNA<MARIIA'), $forms)?->value,
        );
        self::assertSame(
            'FON<VIZIN<<ANNA',
            LatinSpelling::find(IcaoPersonName::from('FON-VIZIN<<ANNA'), [[['FON VIZIN'], ['ANNA']]])?->value,
        );
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('RIMSKII<<KORSAKOV<ANNA<MARIIA'), $forms));
    }

    public function testAPartWithExactlyTheCapIsSearched(): void
    {
        $variants = $this->variants(LatinSpelling::MAX_SPELLINGS);
        $last = $variants[\count($variants) - 1];

        self::assertSame(
            $last . '<<BA',
            LatinSpelling::find(IcaoPersonName::from($last . '<<BA'), [[$variants, ['BA']]])?->value,
        );
    }

    public function testAPartAboveTheCapIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (too many transliteration variants).');

        LatinSpelling::find(
            IcaoPersonName::from('BA<<BA'),
            [[['BA'], $this->variants(LatinSpelling::MAX_SPELLINGS + 1)]],
        );
    }

    public function testAPartAboveTheCapInALaterFormIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        LatinSpelling::find(
            IcaoPersonName::from('BA<<BA'),
            [[['BA'], ['BA']], [$this->variants(LatinSpelling::MAX_SPELLINGS + 1)]],
        );
    }

    public function testPartsWhoseProductIsFarAboveTheCapAreMatchedPartByPart(): void
    {
        $variants = $this->variants(LatinSpelling::MAX_SPELLINGS);
        $last = $variants[\count($variants) - 1];
        $forms = [[$variants, $variants, $variants]];

        self::assertSame(
            $last . '<<' . $last . '<' . $last,
            LatinSpelling::find(IcaoPersonName::from($last . '<<' . $last . '<' . $last), $forms)?->value,
        );
        self::assertNull(LatinSpelling::find(IcaoPersonName::from($last . '<<' . $last . '<A'), $forms));
    }

    public function testTheWordsOfTheSecondaryIdentifiersMustFitTheParts(): void
    {
        $forms = [[['SMITH'], ['ANNA MARIA'], ['JO']]];

        self::assertSame(
            'SMITH<<ANNA<MARIA<JO',
            LatinSpelling::find(IcaoPersonName::from('SMITH<<ANNA<MARIA<JO'), $forms)?->value,
        );
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('SMITH<<ANNA<MARIA'), $forms));
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('SMITH<<ANNA<MARIA<JO<JO'), $forms));
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('SMITH'), $forms));
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('SMITH<<JO'), [[['SMITH']]]));
        self::assertSame('SMITH', LatinSpelling::find(IcaoPersonName::from('smith'), [[['SMITH']]])?->value);
    }

    public function testVariantsAreComparedInUpperCase(): void
    {
        self::assertSame(
            'VAN<DER<BERG<<ANN',
            LatinSpelling::find(IcaoPersonName::from('VAN<DER<BERG<<ANN'), [[['van der Berg'], ['Ann']]])?->value,
        );
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
