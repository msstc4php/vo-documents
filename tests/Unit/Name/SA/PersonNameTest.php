<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\SA;

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
use MSSTC4PHP\ValueObject\Documents\Name\SA\AbstractArabicNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\SA\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\FatherName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\GrandfatherName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/SA.md "Names": given name first, then the optional father, grandfather and family
 * names, and the Latin form mandatory.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(PersonNameCodec::class)]
#[UsesClass(AbstractArabicNamePart::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(FatherName::class)]
#[UsesClass(GrandfatherName::class)]
#[UsesClass(FamilyName::class)]
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

    private const string SHAPE = 'Expected a Saudi Arabian person name payload with string "given", string or null '
        . 'or missing "father", "grandfather" and "family", string "latin", and no other keys.';

    public function testFullIsTheFourPartsInOrder(): void
    {
        self::assertSame('محمد عبد الله سعد آل سعود', $this->person()->full());
    }

    public function testKeepsTheGivenLatinFormWithoutDerivingOne(): void
    {
        self::assertSame('AL<SAUD<<MOHAMMED', $this->person()->latin()->value);
        self::assertSame('SAUD<<MOHAMMED', $this->person('SAUD<<MOHAMMED')->latin()->value);
    }

    public function testNameWithoutFatherAndGrandfatherParts(): void
    {
        $name = new PersonName(GivenName::from('محمد'), null, null, null, IcaoPersonName::from('MOHAMMED'));

        self::assertSame('محمد', $name->full());
        self::assertNull($name->father);
        self::assertNull($name->grandfather);
        self::assertNull($name->family);
        self::assertSame(
            ['given' => 'محمد', 'father' => null, 'grandfather' => null, 'family' => null, 'latin' => 'MOHAMMED'],
            $name->jsonSerialize(),
        );
    }

    public function testGivenNameAndFamilyNameOnly(): void
    {
        $name = new PersonName(
            GivenName::from('سارة'),
            null,
            null,
            FamilyName::from('القحطاني'),
            IcaoPersonName::from('ALQAHTANI<<SARAH'),
        );

        self::assertSame('سارة القحطاني', $name->full());
    }

    public function testGivenAndFatherNameOnly(): void
    {
        $name = new PersonName(
            GivenName::from('خالد'),
            FatherName::from('فهد'),
            null,
            null,
            IcaoPersonName::from('KHALID<FAHD'),
        );

        self::assertSame('خالد فهد', $name->full());
    }

    public function testGrandfatherWithoutFatherIsAllowed(): void
    {
        $name = new PersonName(
            GivenName::from('خالد'),
            null,
            GrandfatherName::from('سلمان'),
            null,
            IcaoPersonName::from('KHALID'),
        );

        self::assertSame('خالد سلمان', $name->full());
    }

    public function testEqualityIncludesTheLatinFormAndEveryPart(): void
    {
        $latin = IcaoPersonName::from('AL<SAUD<<MOHAMMED');

        self::assertTrue($this->person()->equals($this->person()));
        self::assertFalse($this->person()->equals($this->person('SAUD<<MOHAMMED')));
        self::assertFalse($this->person()->equals(new PersonName(
            GivenName::from('محمد'),
            FatherName::from('عبد الله'),
            GrandfatherName::from('سعد'),
            null,
            $latin,
        )));
        self::assertFalse($this->person()->equals(new PersonName(
            GivenName::from('محمد'),
            FatherName::from('عبد الله'),
            null,
            FamilyName::from('آل سعود'),
            $latin,
        )));
        self::assertFalse($this->person()->equals(new PersonName(
            GivenName::from('محمد'),
            null,
            GrandfatherName::from('سعد'),
            FamilyName::from('آل سعود'),
            $latin,
        )));
        self::assertFalse($this->person()->equals(new PersonName(
            GivenName::from('أحمد'),
            FatherName::from('عبد الله'),
            GrandfatherName::from('سعد'),
            FamilyName::from('آل سعود'),
            $latin,
        )));
        self::assertFalse($this->person()->equals(GivenName::from('محمد')));
    }

    public function testStretchedSpellingIsTheSameName(): void
    {
        $plain = new PersonName(GivenName::from('محمد'), null, null, null, IcaoPersonName::from('MOHAMMED'));
        $stretched = new PersonName(
            GivenName::from("\u{0645}\u{0640}\u{062D}\u{0645}\u{062F}"),
            null,
            null,
            null,
            IcaoPersonName::from('MOHAMMED'),
        );

        self::assertTrue($plain->equals($stretched));
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $payload = $codec->encode($this->person());

        self::assertSame(
            [
                'given' => 'محمد',
                'father' => 'عبد الله',
                'grandfather' => 'سعد',
                'family' => 'آل سعود',
                'latin' => 'AL<SAUD<<MOHAMMED',
            ],
            $payload,
        );
        self::assertTrue($codec->decode($payload)->equals($this->person()));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRoundTripOfAGivenNameOnlyAndMissingKeys(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(GivenName::from('محمد'), null, null, null, IcaoPersonName::from('MOHAMMED'));

        self::assertTrue($codec->decode($codec->encode($name))->equals($name));
        self::assertTrue($codec->decode(['given' => 'محمد', 'latin' => 'MOHAMMED'])->equals($name));
    }

    public function testCodecDecodesEveryPartFromItsOwnKey(): void
    {
        $name = new PersonNameCodec()->decode([
            'given' => 'خالد',
            'father' => 'فهد',
            'grandfather' => 'سلمان',
            'family' => 'العتيبي',
            'latin' => 'ALOTAIBI<<KHALID',
        ]);

        self::assertSame('فهد', $name->father?->value);
        self::assertSame('سلمان', $name->grandfather?->value);
        self::assertSame('العتيبي', $name->family?->value);
    }

    public function testCodecRefusesAnotherClass(): void
    {
        $this->expectException(LogicException::class);

        new PersonNameCodec()->encode(GivenName::from('محمد'));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'latin missing' => [['given' => 'محمد']];
        yield 'latin null' => [['given' => 'محمد', 'latin' => null]];
        yield 'given missing' => [['family' => 'سعود', 'latin' => 'SAUD']];
        yield 'given null' => [['given' => null, 'latin' => 'SAUD']];
        yield 'given int' => [['given' => 1, 'latin' => 'SAUD']];
        yield 'father int' => [['given' => 'محمد', 'father' => 1, 'latin' => 'SAUD']];
        yield 'grandfather int' => [['given' => 'محمد', 'grandfather' => 1, 'latin' => 'SAUD']];
        yield 'family int' => [['given' => 'محمد', 'family' => 1, 'latin' => 'SAUD']];
        yield 'unknown key' => [['given' => 'محمد', 'latin' => 'SAUD', 'x' => 'y']];
        yield 'surname key of other countries' => [['given' => 'محمد', 'surname' => 'سعود', 'latin' => 'SAUD']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            'SAUD',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     */
    public static function scriptMixing(): iterable
    {
        yield 'Latin given' => [['given' => 'Mohammed', 'family' => 'سعود', 'latin' => 'SAUD<<MOHAMMED']];
        yield 'Latin father' => [['given' => 'محمد', 'father' => 'Abdullah', 'latin' => 'SAUD<<MOHAMMED']];
        yield 'Latin grandfather' => [['given' => 'محمد', 'grandfather' => 'Saad', 'latin' => 'SAUD<<MOHAMMED']];
        yield 'Latin family' => [['given' => 'محمد', 'family' => 'Saud', 'latin' => 'SAUD<<MOHAMMED']];
        yield 'Arabic as the Latin form' => [['given' => 'محمد', 'latin' => 'محمد']];
        yield 'Han among Arabic' => [['given' => 'محمد', 'family' => '金', 'latin' => 'SAUD<<MOHAMMED']];
    }

    /**
     * @param array<string, string> $payload
     */
    #[DataProvider('scriptMixing')]
    public function testDecodeRefusesAnotherScript(array $payload): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode($payload);
    }

    private function person(string $latin = 'AL<SAUD<<MOHAMMED'): PersonName
    {
        return new PersonName(
            GivenName::from('محمد'),
            FatherName::from('عبد الله'),
            GrandfatherName::from('سعد'),
            FamilyName::from('آل سعود'),
            IcaoPersonName::from($latin),
        );
    }
}
