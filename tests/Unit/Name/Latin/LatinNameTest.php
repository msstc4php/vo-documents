<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
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
final class LatinNameTest extends TestCase
{
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
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid test name (invalid character).');

        new class('Иван') extends AbstractLatinNamePart {
            #[Override]
            protected function label(): string
            {
                return 'test name';
            }
        };
    }

    public function testDeriveJoinsFamilyPartsAndKeepsEachGivenPartSeparate(): void
    {
        $builder = new LatinPersonNameBuilder(['García', 'Müller'], ['José', 'María']);

        self::assertSame('GARCIA<MUELLER<<JOSE<MARIA', $builder->derive()->value);
    }

    public function testResolveDropsAnOverrideEqualToTheDerivedForm(): void
    {
        $builder = new LatinPersonNameBuilder(['Rimsky-Korsakov'], ['Ana']);
        $policy = $builder->transliterationVariant('Refused.');

        self::assertNull($builder->resolve(IcaoPersonName::from('RIMSKY<KORSAKOV<<ANA'), $policy));
        self::assertNull($builder->resolve(null, $policy));
    }

    public function testResolveKeepsAnotherIcaoSpellingOfEveryPart(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller', 'Ruiz'], ['José']);
        $kept = $builder->resolve(
            IcaoPersonName::from('MULLER<RUIZ<<JOSE'),
            $builder->transliterationVariant('Refused.'),
        );

        self::assertSame('MULLER<RUIZ<<JOSE', $kept?->value);
    }

    public function testResolveRefusesAForeignSpellingWithoutEchoingIt(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller'], ['Zoë']);

        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Refused.');

        $builder->resolve(IcaoPersonName::from('SMITH<<ZOE'), $builder->transliterationVariant('Refused.'));
    }

    public function testVariantsAreListedOnlyWhenAnOverrideIsGiven(): void
    {
        $builder = new LatinPersonNameBuilder(['Müller'], ['Zoë']);
        $policy = $builder->transliterationVariant('Refused.');

        self::assertNull($policy->resolve(null, $builder->derive()));
    }
}
