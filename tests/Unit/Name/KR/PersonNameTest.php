<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\KR;

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
use MSSTC4PHP\ValueObject\Documents\Name\KR\AbstractKoreanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\KR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\KR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractFamilyGivenPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonName::class)]
#[CoversClass(PersonNameCodec::class)]
#[UsesClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractFamilyGivenPersonNameCodec::class)]
#[UsesClass(AbstractKoreanNamePart::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
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

    private const string SHAPE = 'Expected a Korean person name payload with string "family", string or null "given", '
        . 'string "latin", and no other keys.';

    public function testFullWritesFamilyAndGivenTogether(): void
    {
        self::assertSame('김민준', $this->person()->full());
    }

    public function testKeepsTheGivenLatinFormWithoutDerivingOne(): void
    {
        self::assertSame('KIM<<MIN<JUN', $this->person()->latin()->value);
        self::assertSame('GIM<<MINJUN', $this->person('GIM<<MINJUN')->latin()->value);
    }

    public function testNameWithoutASplitIsStoredWholeInTheFamilyPart(): void
    {
        $person = new PersonName(FamilyName::from('마이클 존슨'), null, IcaoPersonName::from('JOHNSON<<MICHAEL'));

        self::assertSame('마이클 존슨', $person->full());
        self::assertNull($person->given);
        self::assertSame(['family' => '마이클 존슨', 'given' => null, 'latin' => 'JOHNSON<<MICHAEL'], $person->jsonSerialize());
    }

    public function testWholeNameRoundTripsThroughTheCodec(): void
    {
        $codec = new PersonNameCodec();
        $person = new PersonName(FamilyName::from('마이클 존슨'), null, IcaoPersonName::from('JOHNSON<<MICHAEL'));
        $payload = $codec->encode($person);

        self::assertSame(['family' => '마이클 존슨', 'given' => null, 'latin' => 'JOHNSON<<MICHAEL'], $payload);
        self::assertTrue($codec->decode($payload)->equals($person));
    }

    public function testEqualityIncludesTheLatinFormAndTheSplit(): void
    {
        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('GIM<<MINJUN')));
        self::assertFalse(
            new PersonName(FamilyName::from('김'), null, IcaoPersonName::from('KIM<<MIN<JUN'))->equals($this->person()),
        );
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $payload = $codec->encode($this->person('GIM<<MINJUN'));

        self::assertSame(['family' => '김', 'given' => '민준', 'latin' => 'GIM<<MINJUN'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->person('GIM<<MINJUN')));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new PersonNameCodec()->encode(FamilyName::from('김'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['family' => '김', 'given' => '민준']];
        yield 'latin null' => [['family' => '김', 'given' => '민준', 'latin' => null]];
        yield 'given key missing' => [['family' => '김', 'latin' => 'KIM<<MINJUN']];
        yield 'given int' => [['family' => '김', 'given' => 1, 'latin' => 'KIM<<MINJUN']];
        yield 'family int' => [['family' => 1, 'given' => '민준', 'latin' => 'KIM<<MINJUN']];
        yield 'unknown key' => [['family' => '김', 'given' => '민준', 'latin' => 'KIM<<MINJUN', 'x' => 'y']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            'Minjun',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    /**
     * @return iterable<string, array{array{family: string, given: string, latin: string}}>
     */
    public static function scriptMixing(): iterable
    {
        yield 'Latin family' => [['family' => 'Kim', 'given' => '민준', 'latin' => 'KIM<<MINJUN']];
        yield 'Latin given' => [['family' => '김', 'given' => 'Minjun', 'latin' => 'KIM<<MINJUN']];
        yield 'Han family' => [['family' => '金', 'given' => '민준', 'latin' => 'KIM<<MINJUN']];
        yield 'Han among Hangul' => [['family' => '김', 'given' => '民준', 'latin' => 'KIM<<MINJUN']];
        yield 'Hangul as the Latin form' => [['family' => '김', 'given' => '민준', 'latin' => '김민준']];
    }

    /**
     * @param array{family: string, given: string, latin: string} $payload
     */
    #[DataProvider('scriptMixing')]
    public function testDecodeRefusesAnotherScript(array $payload): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode($payload);
    }

    private function person(string $latin = 'KIM<<MIN<JUN'): PersonName
    {
        return new PersonName(FamilyName::from('김'), GivenName::from('민준'), IcaoPersonName::from($latin));
    }
}
