<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LatinOverridePolicy::class)]
#[CoversClass(AnyIcaoSpelling::class)]
#[CoversClass(TransliterationVariant::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
final class LatinOverridePolicyTest extends TestCase
{
    private const string REFUSAL = 'Invalid test person name (not a spelling).';

    public function testNoOverrideIsNoOverrideAndTheSpellingsAreNotComputed(): void
    {
        $policy = new TransliterationVariant(
            static fn (): never => throw new LogicException('The spellings were computed.'),
            self::REFUSAL,
        );

        self::assertNull($policy->resolve(null, IcaoPersonName::from('MUELLER<<ZOE')));
        self::assertNull(new AnyIcaoSpelling()->resolve(null, IcaoPersonName::from('IVANOV<<IVAN')));
    }

    public function testAnyIcaoSpellingKeepsAnyOtherName(): void
    {
        $kept = new AnyIcaoSpelling()->resolve(IcaoPersonName::from('IVANOVA<<YULIYA'), IcaoPersonName::from('IVANOVA<<IULIIA'));

        self::assertSame('IVANOVA<<YULIYA', $kept?->value);
    }

    public function testTheDerivedFormInItsMrzSpellingIsNotKept(): void
    {
        $derived = IcaoPersonName::from('RIMSKII-KORSAKOV<<NIKOLAI');

        self::assertNull(new AnyIcaoSpelling()->resolve(IcaoPersonName::from('RIMSKII<KORSAKOV<<NIKOLAI'), $derived));
        self::assertNull(new AnyIcaoSpelling()->resolve(IcaoPersonName::from('rimskii-korsakov<<nikolai'), $derived));
        self::assertNotNull(new AnyIcaoSpelling()->resolve(IcaoPersonName::from('RIMSKII<<KORSAKOV<NIKOLAI'), $derived));
    }

    public function testTransliterationVariantKeepsAnotherSpellingInTheNationalForm(): void
    {
        $policy = new TransliterationVariant(static fn (): array => [[['MUELLER-SMITH', 'MULLER-SMITH'], ['ANN']]], self::REFUSAL);

        self::assertSame('MULLER-SMITH<<ANN', $policy->resolve(IcaoPersonName::from('MULLER<SMITH<<ANN'), IcaoPersonName::from('MUELLER-SMITH<<ANN'))?->value);
        self::assertNull($policy->resolve(IcaoPersonName::from('MUELLER<SMITH<<ANN'), IcaoPersonName::from('MUELLER-SMITH<<ANN')));
    }

    public function testTransliterationVariantRefusesAnotherName(): void
    {
        $policy = new TransliterationVariant(static fn (): array => [[['MUELLER', 'MULLER'], ['ZOE']]], self::REFUSAL);

        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage(self::REFUSAL);

        $policy->resolve(IcaoPersonName::from('MILLER<<ZOE'), IcaoPersonName::from('MUELLER<<ZOE'));
    }
}
