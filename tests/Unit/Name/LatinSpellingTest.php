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
        self::assertSame('MUELLER<<ANNA<MARIE', LatinSpelling::find(IcaoPersonName::from('MUELLER<<ANNA<MARIE'), $forms)?->value);
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('MULLER<<MARIE'), $forms));
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('MULLER<<ANNA<MARIA<MARIE'), $forms));
    }

    public function testFillerAndHyphenAreEquivalentAndTheNationalFormIsReturned(): void
    {
        $forms = [[['RIMSKII-KORSAKOV'], ['ANNA-MARIIA']]];

        self::assertSame('RIMSKII-KORSAKOV<<ANNA-MARIIA', LatinSpelling::find(IcaoPersonName::from('RIMSKII<KORSAKOV<<ANNA<MARIIA'), $forms)?->value);
        self::assertSame('FON<VIZIN<<ANNA', LatinSpelling::find(IcaoPersonName::from('FON-VIZIN<<ANNA'), [[['FON VIZIN'], ['ANNA']]])?->value);
        self::assertNull(LatinSpelling::find(IcaoPersonName::from('RIMSKII<<KORSAKOV<ANNA<MARIIA'), $forms));
    }

    public function testExactlyTheCapIsSearched(): void
    {
        $forms = [[$this->variants(64), $this->variants(64)]];

        self::assertSame('BL<<BL', LatinSpelling::find(IcaoPersonName::from('BL<<BL'), $forms)?->value);
    }

    public function testOneFormAboveTheCapIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (too many transliteration variants).');

        LatinSpelling::find(IcaoPersonName::from('BA<<BA'), [[$this->variants(64), $this->variants(65)]]);
    }

    public function testFormsTogetherAboveTheCapAreRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        LatinSpelling::find(
            IcaoPersonName::from('BA<<BA'),
            [[$this->variants(64), $this->variants(32)], [$this->variants(64), $this->variants(32)], [['BA'], ['BA']]],
        );
    }

    public function testAHugeFormDoesNotOverflowTheCount(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        LatinSpelling::find(IcaoPersonName::from('BA<<BA'), [[$this->variants(4097), ['BA'], ['BA']]]);
    }

    /**
     * @param positive-int $count
     *
     * @return non-empty-list<non-empty-string> distinct two-character spellings: BA, BB, …, BZ, CA, …
     */
    private function variants(int $count): array
    {
        $variants = ['BA'];
        for ($i = 1; $i < $count; $i++) {
            $variants[] = \chr(66 + intdiv($i, 26)) . \chr(65 + $i % 26);
        }

        return $variants;
    }
}
