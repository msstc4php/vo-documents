<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\RequiredLatin;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin\TestFamily;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin\TestGiven;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin\TestPersonName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin\TestPersonNameCodec;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractRequiredLatinPersonName::class)]
#[CoversClass(AbstractRequiredLatinPersonNameCodec::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Optional::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Text::class)]
#[UsesClass(UnicodeText::class)]
final class RequiredLatinPersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected a test person name payload with string "family", '
        . 'string, null or missing "given", string "latin", and no other keys.';

    public function testFullJoinsFamilyAndGivenWithTheNationalSeparator(): void
    {
        self::assertSame('Zhang/Wei', $this->person()->full());
    }

    public function testFullIsTheFamilyAloneWithoutAGivenName(): void
    {
        self::assertSame('Zhang', $this->wholeName()->full());
        self::assertSame(
            ['family' => 'Zhang', 'given' => null, 'latin' => 'ZHANG<<WEI'],
            $this->wholeName()->jsonSerialize(),
        );
    }

    public function testEqualityHandlesAMissingGivenName(): void
    {
        self::assertTrue($this->wholeName()->equals($this->wholeName()));
        self::assertFalse($this->wholeName()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->wholeName()));
    }

    public function testCodecRoundTripWithoutAGivenName(): void
    {
        $codec = new TestPersonNameCodec();
        $payload = $codec->encode($this->wholeName());

        self::assertSame(['family' => 'Zhang', 'given' => null, 'latin' => 'ZHANG<<WEI'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->wholeName()));
    }

    public function testCodecReadsAMissingGivenAsNull(): void
    {
        $decoded = new TestPersonNameCodec()->decode(['family' => 'Zhang', 'latin' => 'ZHANG<<WEI']);

        self::assertTrue($decoded->equals($this->wholeName()));
    }

    public function testLatinIsTheGivenFormNotADerivedOne(): void
    {
        self::assertSame('CHANG<<WAY', $this->person('CHANG<<WAY')->latin()->value);
    }

    public function testEqualityIncludesTheLatinForm(): void
    {
        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('CHANG<<WAY')));
    }

    public function testEqualityComparesFamilyAndGiven(): void
    {
        $base = $this->person();
        $latin = IcaoPersonName::from('ZHANG<<WEI');

        self::assertFalse($base->equals(new TestPersonName(TestFamily::from('Li'), TestGiven::from('Wei'), $latin)));
        self::assertFalse($base->equals(new TestPersonName(TestFamily::from('Zhang'), TestGiven::from('Li'), $latin)));
        self::assertFalse($base->equals(TestFamily::from('Zhang')));
    }

    public function testJsonViewHoldsTheThreeFields(): void
    {
        self::assertSame(
            ['family' => 'Zhang', 'given' => 'Wei', 'latin' => 'ZHANG<<WEI'],
            $this->person()->jsonSerialize(),
        );
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new TestPersonNameCodec();
        $payload = $codec->encode($this->person('CHANG<<WAY'));

        self::assertSame(['family' => 'Zhang', 'given' => 'Wei', 'latin' => 'CHANG<<WAY'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->person('CHANG<<WAY')));
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new TestPersonNameCodec()->encode(TestFamily::from('Zhang'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['family' => 'Zhang', 'given' => 'Wei']];
        yield 'latin null' => [['family' => 'Zhang', 'given' => 'Wei', 'latin' => null]];
        yield 'latin int' => [['family' => 'Zhang', 'given' => 'Wei', 'latin' => 1]];
        yield 'given int' => [['family' => 'Zhang', 'given' => 1, 'latin' => 'ZHANG<<WEI']];
        yield 'family int' => [['family' => 1, 'given' => 'Wei', 'latin' => 'ZHANG<<WEI']];
        yield 'unknown key' => [['family' => 'Zhang', 'given' => 'Wei', 'latin' => 'ZHANG<<WEI', 'x' => 'y']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): TestPersonName => new TestPersonNameCodec()->decode($payload),
            'Zhang',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testDecodeRefusesAnInvalidLatinFormWithoutEchoingIt(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): TestPersonName => new TestPersonNameCodec()->decode(
                ['family' => 'Zhang', 'given' => 'Wei', 'latin' => 'Zhang Wei 123'],
            ),
            'Zhang Wei 123',
            'Invalid ICAO personal name.',
            InvalidPersonNameException::class,
        );
    }

    private function wholeName(): TestPersonName
    {
        return new TestPersonName(TestFamily::from('Zhang'), null, IcaoPersonName::from('ZHANG<<WEI'));
    }

    private function person(string $latin = 'ZHANG<<WEI'): TestPersonName
    {
        return new TestPersonName(TestFamily::from('Zhang'), TestGiven::from('Wei'), IcaoPersonName::from($latin));
    }
}
