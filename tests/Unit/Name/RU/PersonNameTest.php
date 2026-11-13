<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\Patronymic;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\RussianTransliteration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md §2-§5: the derived Latin form omits the patronymic and Ь.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(RussianTransliteration::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(Patronymic::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(UnicodeText::class)]
final class PersonNameTest extends TestCase
{
    public function testDerivedLatinOmitsThePatronymic(): void
    {
        $name = $this->ivanov();

        self::assertSame('IVANOV<<IVAN', $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testDerivedLatinWithoutPatronymic(): void
    {
        self::assertSame('IVANOV<<IVAN', $this->ivanov(withPatronymic: false)->latin()->value);
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'soft sign omitted' => ['Иванов', 'Игорь', 'IVANOV<<IGOR'];
        yield 'soft sign inside' => ['Ильин', 'Ольга', 'ILIN<<OLGA'];
        yield 'apostrophe omitted' => ["д'Артаньян", 'Шарль', 'DARTANIAN<<SHARL'];
        yield 'hard sign is IE' => ['Объедков', 'Илья', 'OBIEEDKOV<<ILIA'];
        yield 'hyphen kept' => ['Римский-Корсаков', 'Николай', 'RIMSKII-KORSAKOV<<NIKOLAI'];
        yield 'space becomes filler' => ['фон Визин', 'Анна Мария', 'FON<VIZIN<<ANNA<MARIIA'];
        yield 'Ё Й Х Ц Щ Ю Я' => ['Щукин-Хацкевич', 'Йоан Юлия Ёлка', 'SHCHUKIN-KHATSKEVICH<<IOAN<IULIIA<ELKA'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivedSpelling(string $family, string $given, string $expected): void
    {
        self::assertSame(
            $expected,
            new PersonName(FamilyName::from($family), GivenName::from($given))->latin()->value,
        );
    }

    public function testOverrideWithPatronymicIsNormalisedAndAccepted(): void
    {
        $name = $this->ivanov(latin: IcaoPersonName::from('ivanov<<ivan<ivanovich'));

        self::assertSame('IVANOV<<IVAN<IVANOVICH', $name->latin()->value);
        self::assertSame('IVANOV<<IVAN<IVANOVICH', $name->latinOverride()?->value);
    }

    public function testOverrideEqualToTheDerivedFormIsAccepted(): void
    {
        self::assertSame('IVANOV<<IVAN', $this->ivanov(latin: IcaoPersonName::from('Ivanov<<Ivan'))->latin()->value);
    }

    public function testOverrideWithASoftSignSpellingIsAccepted(): void
    {
        $name = new PersonName(
            FamilyName::from('Ильин'),
            GivenName::from('Игорь'),
            Patronymic::from('Игоревич'),
            IcaoPersonName::from('ILIN<<IGOR<IGOREVICH'),
        );

        self::assertSame('ILIN<<IGOR<IGOREVICH', $name->latin()->value);
    }

    /**
     * @return iterable<string, array{non-empty-string, bool}>
     */
    public static function refusedOverrides(): iterable
    {
        yield 'other spelling' => ['IWANOW<<IWAN', true];
        yield 'other family name' => ['PETROV<<IVAN', true];
        yield 'patronymic only' => ['IVANOV<<IVANOVICH', true];
        yield 'patronymic first' => ['IVANOV<<IVANOVICH<IVAN', true];
        yield 'mononym' => ['IVANOV', true];
        yield 'patronymic not in the national name' => ['IVANOV<<IVAN<IVANOVICH', false];
    }

    #[DataProvider('refusedOverrides')]
    public function testOverrideThatIsNoTransliterationIsRefused(string $latin, bool $withPatronymic): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid Russian person name (the Latin form is not a transliteration of the name).');

        $this->ivanov($withPatronymic, IcaoPersonName::from($latin));
    }

    public function testDisplayForms(): void
    {
        $name = $this->ivanov();

        self::assertSame('Иванов Иван Иванович', $name->full());
        self::assertSame('Иванов И. И.', $name->short());
        self::assertSame('И. И. Иванов', $name->initialsFirst());
    }

    public function testDisplayFormsWithoutPatronymic(): void
    {
        $name = $this->ivanov(withPatronymic: false);

        self::assertSame('Иванов Иван', $name->full());
        self::assertSame('Иванов И.', $name->short());
        self::assertSame('И. Иванов', $name->initialsFirst());
    }

    public function testInitialsOfCompoundParts(): void
    {
        $name = new PersonName(FamilyName::from('Алиев'), GivenName::from('Анна-Мария Ёлка'), Patronymic::from('Гусейн оглы'));

        self::assertSame('Алиев А.-М. Ё. Г. о.', $name->short());
        self::assertSame('А.-М. Ё. Г. о. Алиев', $name->initialsFirst());
    }

    public function testJsonSerializeGivesTheFullValue(): void
    {
        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latin' => 'IVANOV<<IVAN'],
            $this->ivanov()->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => null, 'latin' => 'IVANOV<<IVAN'],
            $this->ivanov(withPatronymic: false)->jsonSerialize(),
        );
    }

    public function testEquality(): void
    {
        $name = $this->ivanov();

        self::assertTrue($name->equals($this->ivanov()));
        self::assertFalse($name->equals($this->ivanov(withPatronymic: false)));
        self::assertFalse($this->ivanov(withPatronymic: false)->equals($name));
        self::assertFalse($name->equals(new PersonName(FamilyName::from('Петров'), GivenName::from('Иван'), Patronymic::from('Иванович'))));
        self::assertFalse($name->equals(new PersonName(FamilyName::from('Иванов'), GivenName::from('Пётр'), Patronymic::from('Иванович'))));
        self::assertFalse($name->equals(new PersonName(FamilyName::from('Иванов'), GivenName::from('Иван'), Patronymic::from('Петрович'))));
        self::assertFalse($name->equals($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN'))));
        self::assertFalse($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN'))->equals($name));
        self::assertTrue($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN'))->equals($this->ivanov(latin: IcaoPersonName::from('ivanov<<ivan'))));
        self::assertFalse($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN'))->equals($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN<IVANOVICH'))));
        self::assertFalse($name->equals(GivenName::from('Иван')));
    }

    private function ivanov(bool $withPatronymic = true, ?IcaoPersonName $latin = null): PersonName
    {
        return new PersonName(
            FamilyName::from('Иванов'),
            GivenName::from('Иван'),
            $withPatronymic ? Patronymic::from('Иванович') : null,
            $latin,
        );
    }
}
