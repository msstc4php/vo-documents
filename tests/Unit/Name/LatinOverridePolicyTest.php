<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LatinOverridePolicy::class)]
#[CoversClass(AnyIcaoSpelling::class)]
#[CoversClass(TransliterationVariant::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
final class LatinOverridePolicyTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

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

    public function testTheDerivedFormIsRecognisedBeforeTheSpellingsAreComputed(): void
    {
        $policy = new TransliterationVariant(
            static fn (): never => throw new LogicException('The spellings were computed.'),
            self::REFUSAL,
        );

        self::assertNull(
            $policy->resolve(IcaoPersonName::from('MUELLER<SMITH<<ZOE'), IcaoPersonName::from('MUELLER-SMITH<<ZOE')),
        );
    }

    public function testAnyIcaoSpellingKeepsAnyOtherName(): void
    {
        $kept = new AnyIcaoSpelling()->resolve(
            IcaoPersonName::from('IVANOVA<<YULIYA'),
            IcaoPersonName::from('IVANOVA<<IULIIA'),
        );

        self::assertSame('IVANOVA<<YULIYA', $kept?->value);
    }

    public function testTheDerivedFormInItsMrzSpellingIsNotKept(): void
    {
        $derived = IcaoPersonName::from('RIMSKII-KORSAKOV<<NIKOLAI');

        self::assertNull(new AnyIcaoSpelling()->resolve(IcaoPersonName::from('RIMSKII<KORSAKOV<<NIKOLAI'), $derived));
        self::assertNull(new AnyIcaoSpelling()->resolve(IcaoPersonName::from('rimskii-korsakov<<nikolai'), $derived));
        self::assertNotNull(
            new AnyIcaoSpelling()->resolve(IcaoPersonName::from('RIMSKII<<KORSAKOV<NIKOLAI'), $derived),
        );
    }

    public function testTransliterationVariantKeepsAnotherSpellingInTheNationalForm(): void
    {
        $policy = new TransliterationVariant(
            static fn (): array => [
                new IdentifierSpellings([['MUELLER', 'MULLER'], ['SMITH']], ['-']),
                new IdentifierSpellings([['ANN']], []),
            ],
            self::REFUSAL,
        );

        self::assertSame(
            'MULLER-SMITH<<ANN',
            $policy->resolve(
                IcaoPersonName::from('MULLER<SMITH<<ANN'),
                IcaoPersonName::from('MUELLER-SMITH<<ANN'),
            )?->value,
        );
        self::assertNull(
            $policy->resolve(IcaoPersonName::from('MUELLER<SMITH<<ANN'), IcaoPersonName::from('MUELLER-SMITH<<ANN')),
        );
    }

    public function testTransliterationVariantRefusesAnotherName(): void
    {
        $policy = new TransliterationVariant(
            static fn (): array => [
                new IdentifierSpellings([['MUELLER', 'MULLER']], []),
                new IdentifierSpellings([['ZOE']], []),
            ],
            self::REFUSAL,
        );

        self::assertNameRefusedWithoutEcho(
            static fn (): ?IcaoPersonName => $policy->resolve(
                IcaoPersonName::from('MILLER<<ZOE'),
                IcaoPersonName::from('MUELLER<<ZOE'),
            ),
            'Miller Zoe',
            self::REFUSAL,
        );
    }

    public function testWithoutADerivedFormOnlyAnyIcaoSpellingAcceptsTheOverride(): void
    {
        $reason = new InvalidPersonNameException('Invalid personal name (no ICAO transliteration for a character).');
        $override = IcaoPersonName::from('SMITH<<FRED');
        $policy = new TransliterationVariant(
            static fn (): never => throw new LogicException('The spellings were computed.'),
            self::REFUSAL,
        );

        self::assertSame($override, new AnyIcaoSpelling()->withoutDerivedForm($override, $reason));
        self::assertNameRefusedWithoutEcho(
            static fn (): IcaoPersonName => new AnyIcaoSpelling()->withoutDerivedForm(null, $reason),
            '',
            $reason->getMessage(),
        );
        self::assertNameRefusedWithoutEcho(
            static fn (): IcaoPersonName => $policy->withoutDerivedForm($override, $reason),
            'Smith Fred',
            $reason->getMessage(),
        );
    }
}
