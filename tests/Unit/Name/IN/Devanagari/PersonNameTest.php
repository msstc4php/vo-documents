<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IN\Devanagari;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\AbstractDevanagariNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\Surname;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Names": given name first, surname optional, the Latin form mandatory.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(PersonNameCodec::class)]
#[UsesClass(AbstractDevanagariNamePart::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(Surname::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Optional::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Text::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected an Indian Devanagari person name payload with string "given", '
        . 'string or null or missing "surname", string "latin", and no other keys.';

    public function testFullIsGivenThenSurnameWithASpace(): void
    {
        self::assertSame('पीयूष कुमार जैन', $this->person()->full());
    }

    public function testKeepsTheGivenLatinFormWithoutDerivingOne(): void
    {
        self::assertSame('JAIN<<PIYUSH<KUMAR', $this->person()->latin()->value);
        self::assertSame('JAYN<<PIYUSH<KUMAR', $this->person('JAYN<<PIYUSH<KUMAR')->latin()->value);
    }

    public function testMononymHasNoSurname(): void
    {
        $name = new PersonName(GivenName::from('राजेश कुमार'), null, IcaoPersonName::from('RAJESH<KUMAR'));

        self::assertSame('राजेश कुमार', $name->full());
        self::assertNull($name->surname);
        self::assertSame(['given' => 'राजेश कुमार', 'surname' => null, 'latin' => 'RAJESH<KUMAR'], $name->jsonSerialize());
    }

    public function testEqualityIncludesTheLatinFormAndTheSplit(): void
    {
        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('JAYN<<PIYUSH<KUMAR')));
        self::assertFalse(
            new PersonName(GivenName::from('पीयूष कुमार'), null, IcaoPersonName::from('JAIN<<PIYUSH<KUMAR'))->equals($this->person()),
        );
        self::assertFalse($this->person()->equals(new PersonName(GivenName::from('पीयूष'), Surname::from('जैन'), IcaoPersonName::from('JAIN<<PIYUSH<KUMAR'))));
        self::assertFalse($this->person()->equals(GivenName::from('राजेश')));
    }

    public function testBothSpellingsOfANuktaNameAreOneValue(): void
    {
        $precomposed = new PersonName(GivenName::from("\u{0958}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}"), null, IcaoPersonName::from('QURAISHI'));
        $decomposed = new PersonName(GivenName::from("\u{0915}\u{093C}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}"), null, IcaoPersonName::from('QURAISHI'));

        self::assertTrue($precomposed->equals($decomposed));
        self::assertSame($precomposed->given->value, $decomposed->given->value);
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $payload = $codec->encode($this->person());

        self::assertSame(['given' => 'पीयूष कुमार', 'surname' => 'जैन', 'latin' => 'JAIN<<PIYUSH<KUMAR'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->person()));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRoundTripOfAMononymAndAMissingSurnameKey(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(GivenName::from('राजेश'), null, IcaoPersonName::from('RAJESH'));
        $payload = $codec->encode($name);

        self::assertSame(['given' => 'राजेश', 'surname' => null, 'latin' => 'RAJESH'], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
        self::assertTrue($codec->decode(['given' => 'राजेश', 'latin' => 'RAJESH'])->equals($name));
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new PersonNameCodec()->encode(GivenName::from('राजेश'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['given' => 'राजेश']];
        yield 'latin null' => [['given' => 'राजेश', 'latin' => null]];
        yield 'given missing' => [['surname' => 'जैन', 'latin' => 'JAIN']];
        yield 'given null' => [['given' => null, 'latin' => 'JAIN']];
        yield 'given int' => [['given' => 1, 'latin' => 'JAIN']];
        yield 'surname int' => [['given' => 'राजेश', 'surname' => 1, 'latin' => 'JAIN']];
        yield 'unknown key' => [['given' => 'राजेश', 'latin' => 'JAIN', 'x' => 'y']];
        yield 'family key of other countries' => [['family' => 'जैन', 'given' => 'राजेश', 'latin' => 'JAIN']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            'JAIN',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    /**
     * @return iterable<string, array{array{given: string, surname: string, latin: string}}>
     */
    public static function scriptMixing(): iterable
    {
        yield 'Latin given' => [['given' => 'Rajesh', 'surname' => 'जैन', 'latin' => 'JAIN<<RAJESH']];
        yield 'Latin surname' => [['given' => 'राजेश', 'surname' => 'Jain', 'latin' => 'JAIN<<RAJESH']];
        yield 'Devanagari as the Latin form' => [['given' => 'राजेश', 'surname' => 'जैन', 'latin' => 'जैन']];
        yield 'Han among Devanagari' => [['given' => 'राजेश', 'surname' => '金', 'latin' => 'JAIN<<RAJESH']];
    }

    /**
     * @param array{given: string, surname: string, latin: string} $payload
     */
    #[DataProvider('scriptMixing')]
    public function testDecodeRefusesAnotherScript(array $payload): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode($payload);
    }

    private function person(string $latin = 'JAIN<<PIYUSH<KUMAR'): PersonName
    {
        return new PersonName(GivenName::from('पीयूष कुमार'), Surname::from('जैन'), IcaoPersonName::from($latin));
    }
}
