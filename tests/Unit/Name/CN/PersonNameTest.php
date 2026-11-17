<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CN;

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
use MSSTC4PHP\ValueObject\Documents\Name\CN\AbstractChineseNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CN\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonNameCodec;
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
#[UsesClass(AbstractChineseNamePart::class)]
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

    private const string SHAPE = 'Expected a Chinese person name payload with string "family", string or null "given", '
        . 'string "latin", and no other keys.';

    public function testFullWritesFamilyAndGivenTogether(): void
    {
        self::assertSame('张伟', $this->person()->full());
    }

    public function testKeepsTheGivenLatinFormWithoutDerivingOne(): void
    {
        self::assertSame('CHEUNG<<WAI', $this->person('CHEUNG<<WAI')->latin()->value);
        self::assertSame('ZHANG<<WEI', $this->person()->latin()->value);
    }

    public function testMinorityNameIsStoredWholeInTheFamilyPartWithEachFoldedDot(): void
    {
        foreach (["\u{00B7}", "\u{30FB}", "\u{2022}"] as $dot) {
            $person = new PersonName(
                FamilyName::from('阿沛' . $dot . '阿旺晋美'),
                null,
                IcaoPersonName::from('NGAPOI<<NGAWANG<JIGME'),
            );

            self::assertSame("阿沛\u{00B7}阿旺晋美", $person->full());
            self::assertNull($person->given);
            self::assertSame(
                ['family' => "阿沛\u{00B7}阿旺晋美", 'given' => null, 'latin' => 'NGAPOI<<NGAWANG<JIGME'],
                $person->jsonSerialize(),
            );
        }
    }

    public function testWholeNameRoundTripsThroughTheCodec(): void
    {
        $codec = new PersonNameCodec();
        $person = new PersonName(FamilyName::from('阿沛·阿旺晋美'), null, IcaoPersonName::from('NGAPOI<<NGAWANG<JIGME'));
        $payload = $codec->encode($person);

        self::assertSame(['family' => "阿沛\u{00B7}阿旺晋美", 'given' => null, 'latin' => 'NGAPOI<<NGAWANG<JIGME'], $payload);
        self::assertTrue($codec->decode($payload)->equals($person));
    }

    public function testAWholeNameAndASplitNameAreNotEqual(): void
    {
        $latin = IcaoPersonName::from('ZHANG<<WEI');

        self::assertFalse(new PersonName(FamilyName::from('张'), null, $latin)->equals($this->person()));
        self::assertFalse($this->person()->equals(new PersonName(FamilyName::from('张'), null, $latin)));
        self::assertTrue(
            new PersonName(
                FamilyName::from('张'),
                null,
                $latin,
            )->equals(new PersonName(FamilyName::from('张'), null, $latin)),
        );
    }

    public function testEqualityIncludesTheLatinForm(): void
    {
        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('CHEUNG<<WAI')));
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $payload = $codec->encode($this->person('CHEUNG<<WAI'));

        self::assertSame(['family' => '张', 'given' => '伟', 'latin' => 'CHEUNG<<WAI'], $payload);
        self::assertTrue($codec->decode($payload)->equals($this->person('CHEUNG<<WAI')));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new PersonNameCodec()->encode(FamilyName::from('张'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['family' => '张', 'given' => '伟']];
        yield 'latin null' => [['family' => '张', 'given' => '伟', 'latin' => null]];
        yield 'given key missing' => [['family' => '张', 'latin' => 'ZHANG<<WEI']];
        yield 'given int' => [['family' => '张', 'given' => 1, 'latin' => 'ZHANG<<WEI']];
        yield 'family int' => [['family' => 1, 'given' => '伟', 'latin' => 'ZHANG<<WEI']];
        yield 'unknown key' => [['family' => '张', 'given' => '伟', 'latin' => 'ZHANG<<WEI', 'x' => 'y']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            'Zhang',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    /**
     * @return iterable<string, array{array{family: string, given: string, latin: string}}>
     */
    public static function scriptMixing(): iterable
    {
        yield 'Latin family' => [['family' => 'Zhang', 'given' => '伟', 'latin' => 'ZHANG<<WEI']];
        yield 'Latin given' => [['family' => '张', 'given' => 'Wei', 'latin' => 'ZHANG<<WEI']];
        yield 'Latin among Han' => [['family' => '张', 'given' => '伟a', 'latin' => 'ZHANG<<WEI']];
        yield 'Han as the Latin form' => [['family' => '张', 'given' => '伟', 'latin' => '张伟']];
    }

    /**
     * @param array{family: string, given: string, latin: string} $payload
     */
    #[DataProvider('scriptMixing')]
    public function testDecodeRefusesAnotherScriptWithoutEchoingIt(array $payload): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode($payload);
    }

    private function person(string $latin = 'ZHANG<<WEI'): PersonName
    {
        return new PersonName(FamilyName::from('张'), GivenName::from('伟'), IcaoPersonName::from($latin));
    }
}
