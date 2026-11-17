<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ID;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\ID\FullName;
use MSSTC4PHP\ValueObject\Documents\Name\ID\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\ID\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ID.md "Names": one Dukcapil "Nama Lengkap", the Latin form mandatory because no
 * Imigrasi rule for splitting a name into identifiers was found.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(PersonNameCodec::class)]
#[UsesClass(FullName::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Text::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected an Indonesian person name payload with string "name", string "latin", '
        . 'and no other keys.';

    public function testFullIsTheSingleRecordedName(): void
    {
        self::assertSame('Siti Nurhaliza', $this->person()->full());
    }

    public function testKeepsTheGivenLatinFormWithoutDerivingOne(): void
    {
        self::assertSame('NURHALIZA<<SITI', $this->person()->latin()->value);
        self::assertSame('SITI<NURHALIZA', $this->person('SITI<NURHALIZA')->latin()->value);
    }

    public function testAOneWordNameIsAccepted(): void
    {
        $name = new PersonName(FullName::from('Suharto'), IcaoPersonName::from('SUHARTO'));

        self::assertSame('Suharto', $name->full());
        self::assertSame(['name' => 'Suharto', 'latin' => 'SUHARTO'], $name->jsonSerialize());
    }

    public function testEqualityIncludesTheLatinForm(): void
    {
        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('SITI<NURHALIZA')));
        self::assertFalse(
            $this->person()->equals(new PersonName(FullName::from('Siti'), IcaoPersonName::from('NURHALIZA<<SITI'))),
        );
        self::assertFalse($this->person()->equals(FullName::from('Siti Nurhaliza')));
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $payload = $codec->encode($this->person());

        self::assertSame(['name' => 'Siti Nurhaliza', 'latin' => 'NURHALIZA<<SITI'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->person()));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new PersonNameCodec()->encode(FullName::from('Siti Nurhaliza'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['name' => 'Budi Santoso']];
        yield 'latin null' => [['name' => 'Budi Santoso', 'latin' => null]];
        yield 'name missing' => [['latin' => 'SANTOSO<<BUDI']];
        yield 'name null' => [['name' => null, 'latin' => 'SANTOSO<<BUDI']];
        yield 'name int' => [['name' => 1, 'latin' => 'SANTOSO<<BUDI']];
        yield 'unknown key' => [['name' => 'Budi Santoso', 'latin' => 'SANTOSO<<BUDI', 'x' => 'y']];
        yield 'family key of other countries' => [['family' => 'Santoso', 'name' => 'Budi', 'latin' => 'SANTOSO']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            self::payloadProbe($payload),
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    /**
     * @return iterable<string, array{array{name: string, latin: string}, string}>
     */
    public static function scriptMixing(): iterable
    {
        yield 'Arabic name' => [
            ['name' => 'محمد', 'latin' => 'MUHAMMAD'],
            'Invalid Indonesian full name (invalid character).',
        ];
        yield 'Cyrillic name' => [
            ['name' => 'Иван', 'latin' => 'IVAN'],
            'Invalid Indonesian full name (invalid character).',
        ];
        yield 'Arabic as the Latin form' => [
            ['name' => 'Budi Santoso', 'latin' => 'محمد'],
            'Invalid ICAO personal name.',
        ];
    }

    /**
     * @param array{name: string, latin: string} $payload
     */
    #[DataProvider('scriptMixing')]
    public function testDecodeRefusesAnotherScriptWithoutEchoingIt(array $payload, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            self::payloadProbe($payload),
            $message,
        );
    }

    private function person(string $latin = 'NURHALIZA<<SITI'): PersonName
    {
        return new PersonName(FullName::from('Siti Nurhaliza'), IcaoPersonName::from($latin));
    }
}
