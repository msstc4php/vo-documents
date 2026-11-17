<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IN;

use MSSTC4PHP\ValueObject\Documents\Document\IN\PanHolderType;
use MSSTC4PHP\ValueObject\Documents\Document\IN\PermanentAccountNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "PAN": the Income Tax Department tutorial's ten characters.
 */
#[CoversClass(PermanentAccountNumber::class)]
#[CoversClass(PanHolderType::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PermanentAccountNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string, PanHolderType}>
     */
    public static function validNumbers(): iterable
    {
        yield 'individual' => ['BLGPS1234D', 'BLGPS1234D', PanHolderType::Individual];
        yield 'lower case and separators' => ["blgps 1234-d\u{00A0}", 'BLGPS1234D', PanHolderType::Individual];
        yield 'fullwidth' => ["\u{FF22}\u{FF2C}\u{FF27}\u{FF30}\u{FF33}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF24}", 'BLGPS1234D', PanHolderType::Individual];
        yield 'company' => ['AAACR5055K', 'AAACR5055K', PanHolderType::Company];
        yield 'HUF' => ['ABCHD0001E', 'ABCHD0001E', PanHolderType::HinduUndividedFamily];
        yield 'association of persons' => ['ABCAD9999E', 'ABCAD9999E', PanHolderType::AssociationOfPersons];
        yield 'body of individuals' => ['ABCBD1234E', 'ABCBD1234E', PanHolderType::BodyOfIndividuals];
        yield 'government agency' => ['ABCGD1234E', 'ABCGD1234E', PanHolderType::GovernmentAgency];
        yield 'artificial juridical person' => ['ABCJD1234E', 'ABCJD1234E', PanHolderType::ArtificialJuridicalPerson];
        yield 'local authority' => ['ABCLD1234E', 'ABCLD1234E', PanHolderType::LocalAuthority];
        yield 'firm or LLP' => ['ABCFD1234E', 'ABCFD1234E', PanHolderType::FirmOrLimitedLiabilityPartnership];
        yield 'trust' => ['ABCTD1234E', 'ABCTD1234E', PanHolderType::Trust];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndTellsTheHolderType(string $input, string $expected, PanHolderType $type): void
    {
        $number = PermanentAccountNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
        self::assertSame($type, $number->holderType());
    }

    public function testHolderTypeCodeIsTheFourthCharacter(): void
    {
        self::assertSame('P', PanHolderType::Individual->value);
        self::assertSame(
            ['P', 'C', 'H', 'A', 'B', 'G', 'J', 'L', 'F', 'T'],
            \array_map(static fn (PanHolderType $type): string => $type->value, PanHolderType::cases()),
        );
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('******234D', PermanentAccountNumber::from('BLGPS1234D')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Permanent Account Number';

        yield 'nine characters' => ['BLGPS1234', $name . ' (9 characters).'];
        yield 'eleven characters' => ['BLGPS1234DD', $name . ' (11 characters).'];
        yield 'far too long' => [\str_repeat('B', 41), $name . ' (too long).'];
        yield 'fourth character is not a holder type' => ['BLGZS1234D', $name . ' (invalid character).'];
        yield 'fourth character K' => ['BLGKS1234D', $name . ' (invalid character).'];
        yield 'digit among the series' => ['B1GPS1234D', $name . ' (invalid character).'];
        yield 'letter among the digits' => ['BLGPSA234D', $name . ' (invalid character).'];
        yield 'digit as the check character' => ['BLGPS12345', $name . ' (invalid character).'];
        yield 'fifth character a digit' => ['BLGP11234D', $name . ' (invalid character).'];
        yield 'sequence 0000' => ['BLGPS0000D', $name . ' (invalid sequence number).'];
        yield 'punctuation' => ['BLGPS1234.', $name . ' (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): PermanentAccountNumber => PermanentAccountNumber::from($value), $input, $message);
    }
}
