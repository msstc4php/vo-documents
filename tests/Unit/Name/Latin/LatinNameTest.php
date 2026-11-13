<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractLatinNamePart::class)]
#[CoversClass(LatinPersonNameBuilder::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(TransliterationVariant::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(LatinOverridePolicy::class)]
final class LatinNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    public function testPartAcceptsLatinLettersWithDiacritics(): void
    {
        $part = new class('  Ñandú   Pérez ') extends AbstractLatinNamePart {
            #[Override]
            protected function label(): string
            {
                return 'test name';
            }
        };

        self::assertSame('Ñandú Pérez', $part->value);
    }

    public function testPartRefusesOtherScripts(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): AbstractLatinNamePart => new class('Иван') extends AbstractLatinNamePart {
                #[Override]
                protected function label(): string
                {
                    return 'test name';
                }
            },
            'Иван',
            'Invalid test name (invalid character).',
        );
    }

    public function testDeriveJoinsFamilyPartsAndKeepsEachGivenPartSeparate(): void
    {
        $builder = new LatinPersonNameBuilder(['García', 'Müller'], ['José', 'María']);

        self::assertSame('GARCIA<MUELLER<<JOSE<MARIA', $builder->derive()->value);
    }

    public function testTheDerivedFormIsKeptWithoutComputingAnyVariant(): void
    {
        $builder = new LatinPersonNameBuilder([\str_repeat('Ü', 13)], ['Ana']);
        $policy = $builder->transliterationVariant('Refused.');

        self::assertNull($policy->resolve(null, $builder->derive()));
        self::assertNull($policy->resolve($builder->derive(), $builder->derive()));
        self::assertNameRefusedWithoutEcho(
            static fn (): ?IcaoPersonName => $policy->resolve(
                IcaoPersonName::from(\str_repeat('U', 13) . '<<ANA'),
                $builder->derive(),
            ),
            'Ana',
            'Invalid personal name (too many transliteration variants).',
        );
    }

    public function testTheVariantCapAppliesToEachWord(): void
    {
        $words = \array_fill(0, 13, 'Jürgen');
        $builder = new LatinPersonNameBuilder(['Müller Müller-Müller'], [\implode(' ', $words), 'Jörg']);
        $policy = $builder->transliterationVariant('Refused.');
        $latin = 'MULLER<MUELLER<MULLER<<' . \str_repeat('JURGEN<', 12) . 'JUERGEN<JORG';

        self::assertSame(
            'MULLER<MUELLER-MULLER<<' . \str_repeat('JURGEN<', 12) . 'JUERGEN<JORG',
            $policy->resolve(IcaoPersonName::from($latin), $builder->derive())?->value,
        );
    }

    public function testKeepsAnotherIcaoSpellingOfEveryFamilyPart(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller', 'Ruiz'], ['José']);

        self::assertSame(
            'MULLER<RUIZ<<JOSE',
            $builder->transliterationVariant('Refused.')
                ->resolve(IcaoPersonName::from('MULLER<RUIZ<<JOSE'), $builder->derive())?->value,
        );
    }

    public function testRefusesAForeignSpellingWithoutEchoingIt(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller'], ['Zoë']);

        self::assertNameRefusedWithoutEcho(
            static fn (): ?IcaoPersonName => $builder->transliterationVariant('Refused.')
                ->resolve(IcaoPersonName::from('SMITH<<ZOE'), $builder->derive()),
            'Smith Zoë',
            'Refused.',
        );
    }

    public function testMatchesEachOfSeveralGivenPartsAgainstItsOwnVariants(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller'], ['José', 'Zoë']);
        $policy = $builder->transliterationVariant('Refused.');

        self::assertSame(
            'MULLER<<JOSE<ZOE',
            $policy->resolve(IcaoPersonName::from('MULLER<<JOSE<ZOE'), $builder->derive())?->value,
        );
        self::assertSame('MUELLER<<JOSE<ZOE', $builder->derive()->value);
        self::assertNameRefusedWithoutEcho(
            static fn (): ?IcaoPersonName => $policy->resolve(IcaoPersonName::from('MULLER<<JOSE'), $builder->derive()),
            'Müller José',
            'Refused.',
        );
    }

    public function testAMononymHasNoSecondaryIdentifier(): void
    {
        $builder = new LatinPersonNameBuilder(['Ëlla'], []);
        $policy = $builder->transliterationVariant('Refused.');

        self::assertSame('ELLA', $builder->derive()->value);
        self::assertNameRefusedWithoutEcho(
            static fn (): ?IcaoPersonName => $policy->resolve(IcaoPersonName::from('ELLA<<ANN'), $builder->derive()),
            'Ella Ann',
            'Refused.',
        );
    }
}
