<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\CN\FamilyName;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PayloadFields::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
final class PayloadFieldsTest extends TestCase
{
    public function testAcceptsKnownKeysAndReadsStrings(): void
    {
        $payload = ['a' => 'x', 'b' => null];

        PayloadFields::assertOnlyKeys($payload, ['a', 'b', 'c'], 'Shape.');

        self::assertSame('x', PayloadFields::string($payload, 'a', 'Shape.'));
        self::assertNull(PayloadFields::nullableString($payload, 'b', 'Shape.'));
        self::assertNull(PayloadFields::nullableString($payload, 'c', 'Shape.'));
        self::assertSame('x', PayloadFields::nullableString($payload, 'a', 'Shape.'));
    }

    public function testRefusesAnUnknownKey(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::assertOnlyKeys(['a' => 'x', 0 => 'y'], ['a'], 'Shape.');
    }

    public function testRefusesAMissingString(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::string([], 'a', 'Shape.');
    }

    public function testRefusesANonString(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::string(['a' => 1], 'a', 'Shape.');
    }

    public function testRefusesANonStringNullable(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::nullableString(['a' => 1], 'a', 'Shape.');
    }

    public function testEncodableReturnsAnInstanceOfTheClass(): void
    {
        $name = IcaoPersonName::from('ZHANG<<WEI');

        self::assertSame($name, PayloadFields::encodable($name, IcaoPersonName::class, self::class));
    }

    public function testEncodableRefusesAnotherClassNamingBothClasses(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            self::class . ' encodes ' . FamilyName::class . ', ' . IcaoPersonName::class . ' given.',
        );

        PayloadFields::encodable(IcaoPersonName::from('ZHANG<<WEI'), FamilyName::class, self::class);
    }

    public function testReadsTheRequiredLatinForm(): void
    {
        self::assertSame('ZHANG<<WEI', PayloadFields::latin(['latin' => 'ZHANG<<WEI'], 'Shape.')->value);
    }

    /**
     * @return iterable<string, array{array<string, int|null>}>
     */
    public static function missingLatinForms(): iterable
    {
        yield 'missing' => [[]];
        yield 'null' => [['latin' => null]];
        yield 'int' => [['latin' => 1]];
    }

    /**
     * @param array<string, int|null> $payload
     */
    #[DataProvider('missingLatinForms')]
    public function testRefusesAMissingOrNonStringLatinForm(array $payload): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::latin($payload, 'Shape.');
    }

    public function testRefusesAnInvalidLatinForm(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        PayloadFields::latin(['latin' => '张伟'], 'Shape.');
    }

    public function testReadsAnOptionalLatinOverride(): void
    {
        self::assertSame(
            'ZHANG<<WEI',
            PayloadFields::latinOverride(['latinOverride' => 'ZHANG<<WEI'], 'Shape.')?->value,
        );
        self::assertNull(PayloadFields::latinOverride(['latinOverride' => null], 'Shape.'));
        self::assertNull(PayloadFields::latinOverride(['latin' => 'ZHANG<<WEI'], 'Shape.'));
        self::assertNull(PayloadFields::latinOverride([], 'Shape.'));
    }

    public function testRefusesANonStringLatinOverride(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::latinOverride(['latinOverride' => 1], 'Shape.');
    }

    public function testUpgradedMovesA0xKeyToIts10Key(): void
    {
        self::assertSame(
            ['family' => 'A', 'latinOverride' => 'B'],
            PayloadFields::upgraded(['family' => 'A', 'latin' => 'B'], PayloadFields::LEGACY_LATIN_OVERRIDE, 'Shape.'),
        );
        self::assertSame(
            ['latinOverride' => null],
            PayloadFields::upgraded(['latin' => null], PayloadFields::LEGACY_LATIN_OVERRIDE, 'Shape.'),
        );
        self::assertSame(
            ['family' => 'A', 'latinOverride' => 'B'],
            PayloadFields::upgraded(['family' => 'A', 'latinOverride' => 'B'], PayloadFields::LEGACY_LATIN_OVERRIDE, 'Shape.'),
        );
        self::assertSame(
            ['b2' => 2],
            PayloadFields::upgraded(['b1' => 2], ['a1' => 'a2', 'b1' => 'b2'], 'Shape.'),
        );
        self::assertSame(
            ['b2' => 2, 'a2' => 1],
            PayloadFields::upgraded(['a1' => 1, 'b1' => 2], ['b1' => 'b2', 'a1' => 'a2'], 'Shape.'),
        );
    }

    public function testUpgradedRefusesBothKeysOfAField(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::upgraded(['latin' => 'A', 'latinOverride' => 'A'], PayloadFields::LEGACY_LATIN_OVERRIDE, 'Shape.');
    }
}
