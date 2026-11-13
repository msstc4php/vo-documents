<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\RU\AbstractRussianNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\Patronymic;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\RussianTransliteration;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
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
#[UsesClass(AbstractRussianNamePart::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(Patronymic::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Optional::class)]
#[UsesClass(LatinForm::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

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

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedOverrides(): iterable
    {
        yield 'same spelling in another case' => ['Иванов', 'Иван', 'Ivanov<<Ivan'];
        yield 'filler for the hyphen' => ['Римский-Корсаков', 'Николай', 'RIMSKII<KORSAKOV<<NIKOLAI'];
        yield 'hyphen for a space' => ['фон Визин', 'Анна-Мария', 'FON-VIZIN<<ANNA<MARIIA'];
        yield 'spaced and padded' => ['Иванов', 'Иван', 'IVANOV << IVAN<<<<'];
    }

    #[DataProvider('derivedOverrides')]
    public function testAnOverrideThatIsTheDerivedFormIsNotKept(string $family, string $given, string $latin): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), null, IcaoPersonName::from($latin));
        $derived = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertNull($name->latinOverride());
        self::assertSame($derived->latin()->value, $name->latin()->value);
        self::assertTrue($name->equals($derived));
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function documentedSpellings(): iterable
    {
        // Order 996 §32 / Order 186 §140: the spelling of a supporting document, not of the table.
        yield 'earlier passport spelling' => ['Иванова', 'Юлия', 'IVANOVA<<YULIYA', 'IVANOVA<<YULIYA'];
        yield 'German civil-status spelling' => ['Шмидт', 'Юрий', 'schmidt<<jurij', 'SCHMIDT<<JURIJ'];
        yield 'MRZ-form spelling kept as given' => [
            'Римский-Корсаков',
            'Николай',
            'RIMSKY<KORSAKOV<<NIKOLAY',
            'RIMSKY<KORSAKOV<<NIKOLAY',
        ];
        yield 'mononym' => ['Иванов', 'Иван', 'IVANOV', 'IVANOV'];
    }

    #[DataProvider('documentedSpellings')]
    public function testAnyValidIcaoNameIsKeptAsTheOverride(
        string $family,
        string $given,
        string $latin,
        string $stored,
    ): void {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), null, IcaoPersonName::from($latin));

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsNoIcaoNameIsRefusedBeforeTheName(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => $this->ivanov(latin: IcaoPersonName::from('IVANOV1<<IVAN')),
            'IVANOV1<<IVAN',
            'Invalid ICAO personal name.',
        );
    }

    public function testAPartWhoseLatinFormIsTooLongIsRefusedWithoutAnOverride(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(
                FamilyName::from(\str_repeat('Щ', 100)),
                GivenName::from('Иван'),
            ),
            'Иван',
            'Invalid ICAO personal name (Latin form too long).',
        );
    }

    public function testAPartWhoseLatinFormIsTooLongIsAcceptedWithAnOverride(): void
    {
        $name = new PersonName(
            FamilyName::from(\str_repeat('Щ', 100)),
            GivenName::from('Иван'),
            null,
            IcaoPersonName::from('SHCH<<IVAN'),
        );

        self::assertSame('SHCH<<IVAN', $name->latin()->value);
        self::assertSame('SHCH<<IVAN', $name->latinOverride()?->value);
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
        $name = new PersonName(
            FamilyName::from('Алиев'),
            GivenName::from('Анна-Мария Ёлка'),
            Patronymic::from('Гусейн оглы'),
        );

        self::assertSame('Алиев А.-М. Ё. Г. о.', $name->short());
        self::assertSame('А.-М. Ё. Г. о. Алиев', $name->initialsFirst());
    }

    public function testJsonSerializeGivesTheFullValue(): void
    {
        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'effectiveLatin' => 'IVANOV<<IVAN'],
            $this->ivanov()->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => null, 'effectiveLatin' => 'IVANOV<<IVAN'],
            $this->ivanov(withPatronymic: false)->jsonSerialize(),
        );
    }

    public function testEquality(): void
    {
        $name = $this->ivanov();

        self::assertTrue($name->equals($this->ivanov()));
        self::assertFalse($name->equals($this->ivanov(withPatronymic: false)));
        self::assertFalse($this->ivanov(withPatronymic: false)->equals($name));
        self::assertFalse(
            $name->equals(
                new PersonName(FamilyName::from('Петров'), GivenName::from('Иван'), Patronymic::from('Иванович')),
            ),
        );
        self::assertFalse(
            $name->equals(
                new PersonName(FamilyName::from('Иванов'), GivenName::from('Пётр'), Patronymic::from('Иванович')),
            ),
        );
        self::assertFalse(
            $name->equals(
                new PersonName(FamilyName::from('Иванов'), GivenName::from('Иван'), Patronymic::from('Петрович')),
            ),
        );
        self::assertFalse($name->equals($this->ivanov(latin: IcaoPersonName::from('IWANOW<<IWAN'))));
        self::assertFalse($this->ivanov(latin: IcaoPersonName::from('IWANOW<<IWAN'))->equals($name));
        self::assertTrue(
            $this->ivanov(latin: IcaoPersonName::from('IWANOW<<IWAN'))->equals(
                $this->ivanov(latin: IcaoPersonName::from('iwanow<<iwan')),
            ),
        );
        self::assertFalse(
            $this->ivanov(latin: IcaoPersonName::from('IWANOW<<IWAN'))->equals(
                $this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN<IVANOVICH')),
            ),
        );
        self::assertTrue($name->equals($this->ivanov(latin: IcaoPersonName::from('IVANOV<<IVAN'))));
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
