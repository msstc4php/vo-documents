<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\MedicareBeneficiaryIdentifier;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "Medicare Beneficiary Identifier"; `1EG4-TE5-MK73` is the CMS example.
 */
#[CoversClass(MedicareBeneficiaryIdentifier::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class MedicareBeneficiaryIdentifierTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validIdentifiers(): iterable
    {
        yield 'CMS example, display form' => ['1EG4-TE5-MK73', '1EG4TE5MK73'];
        yield 'CMS example, compact lower case' => ['1eg4te5mk73', '1EG4TE5MK73'];
        yield 'digits at positions 3 and 6' => ['9A04C07DE00', '9A04C07DE00'];
        yield 'last allowed letters' => ['1YY9YY9YY99', '1YY9YY9YY99'];
        yield 'spaces' => ['1EG4 TE5 MK73', '1EG4TE5MK73'];
    }

    #[DataProvider('validIdentifiers')]
    public function testAccepts(string $input, string $expected): void
    {
        self::assertSame($expected, MedicareBeneficiaryIdentifier::from($input)->value);
    }

    public function testFormatsAndMasksInTheCmsDisplayForm(): void
    {
        $mbi = MedicareBeneficiaryIdentifier::from('1EG4TE5MK73');

        self::assertSame('1EG4-TE5-MK73', $mbi->formatted());
        self::assertSame('****-***-MK73', $mbi->masked());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function wrongPositions(): iterable
    {
        yield 'position 1 is 0' => ['0EG4TE5MK73'];
        yield 'position 1 is a letter' => ['AEG4TE5MK73'];
        yield 'position 2 is a digit' => ['11G4TE5MK73'];
        yield 'position 4 is a letter' => ['1EGATE5MK73'];
        yield 'position 5 is a digit' => ['1EG41E5MK73'];
        yield 'position 7 is a letter' => ['1EG4TEAMK73'];
        yield 'position 8 is a digit' => ['1EG4TE51K73'];
        yield 'position 9 is a digit' => ['1EG4TE5M173'];
        yield 'position 10 is a letter' => ['1EG4TE5MKA3'];
        yield 'position 11 is a letter' => ['1EG4TE5MK7A'];
    }

    #[DataProvider('wrongPositions')]
    public function testRefusesACharacterOfTheWrongTypeForItsPosition(string $input): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): MedicareBeneficiaryIdentifier => MedicareBeneficiaryIdentifier::from($value),
            $input,
            'Invalid American Medicare Beneficiary Identifier (invalid character).',
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function excludedLetters(): iterable
    {
        foreach (['S', 'L', 'O', 'I', 'B', 'Z'] as $letter) {
            yield $letter . ' at position 2' => ['1' . $letter . 'G4TE5MK73'];
            yield $letter . ' at position 3' => ['1E' . $letter . '4TE5MK73'];
            yield $letter . ' at position 6' => ['1EG4T' . $letter . '5MK73'];
            yield $letter . ' at position 9' => ['1EG4TE5M' . $letter . '73'];
        }
    }

    #[DataProvider('excludedLetters')]
    public function testRefusesTheExcludedLetters(string $input): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): MedicareBeneficiaryIdentifier => MedicareBeneficiaryIdentifier::from($value),
            $input,
            'Invalid American Medicare Beneficiary Identifier (invalid character).',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function wrongLengthsOrCharacters(): iterable
    {
        $prefix = 'Invalid American Medicare Beneficiary Identifier';

        yield 'ten characters' => ['1EG4TE5MK7', $prefix . ' (wrong length).'];
        yield 'twelve characters' => ['1EG4TE5MK733', $prefix . ' (wrong length).'];
        yield 'HICN-like slash' => ['1EG4/TE5MK7', $prefix . ' (invalid character).'];
    }

    #[DataProvider('wrongLengthsOrCharacters')]
    public function testRefusesAWrongLength(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): MedicareBeneficiaryIdentifier => MedicareBeneficiaryIdentifier::from($value),
            $input,
            $message,
        );
    }
}
