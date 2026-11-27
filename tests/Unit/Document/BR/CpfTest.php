<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Cpf;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "CPF": the Receita Federal's "DV módulo 11" (ADE COFIS nº 41/2017, Anexo II,
 * REGRA_VALIDA_CPF), whose worked example is `280012389-38`, and its fiscal-region leaflet.
 */
#[CoversClass(Cpf::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class CpfTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'Receita Federal example, display form' => ['280.012.389-38', '28001238938'];
        yield 'compact' => ['28001238938', '28001238938'];
        yield 'spaces and NBSP' => ["280 012\u{00A0}389 38", '28001238938'];
        yield 'first remainder 10 is 0' => ['100.000.001-08', '10000000108'];
        yield 'second remainder 10 is 0' => ['100.000.028-10', '10000002810'];
        yield 'both remainders 10' => ['100.000.046-00', '10000004600'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = Cpf::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * Review Focus 4. The Receita Federal's rule (REGRA_VALIDA_CPF) is the check digits only, and no Receita text
     * refuses a CPF of one repeated digit. DATASUS ("Não deve ser aceita a inserção de números repetidos em todas as
     * posições do campo, ainda que esses números sejam válidos no algoritmo de verificação da Receita Federal") and
     * the Previdência's SIPREV ("Recusar todos os dígitos iguais") refuse it for their own intake only, so it is
     * accepted here: a still-valid number is never refused on another agency's rule.
     *
     * @return iterable<string, array{string}>
     */
    public static function repeatedDigits(): iterable
    {
        foreach (\range(0, 9) as $digit) {
            yield 'all ' . $digit => [\str_repeat((string) $digit, 11)];
        }
    }

    #[DataProvider('repeatedDigits')]
    public function testAcceptsOneRepeatedDigitAsTheReceitaFederalRefusesNone(string $input): void
    {
        self::assertSame($input, Cpf::from($input)->value);
    }

    public function testFormatsMasksAndReadsTheFiscalRegion(): void
    {
        $number = Cpf::from('28001238938');

        self::assertSame('280.012.389-38', $number->formatted());
        self::assertSame('***.***.*89-38', $number->masked());
        self::assertSame('***.012.389-**', $number->cguMasked());
        self::assertSame('9', $number->fiscalRegion());
        self::assertSame('0', Cpf::from('123.456.780-62')->fiscalRegion());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $checksum = 'Invalid Brazilian CPF checksum.';

        yield 'second check digit wrong' => ['280.012.389-39', $checksum];
        yield 'first check digit wrong' => ['280.012.389-48', $checksum];
        yield 'remainder 10 written as 1' => ['100.000.001-18', $checksum];
        yield 'base digit changed' => ['280.012.398-38', $checksum];
        yield 'ten digits' => ['280.012.389-3', 'Invalid Brazilian CPF (wrong length).'];
        yield 'twelve digits' => ['280.012.389-380', 'Invalid Brazilian CPF (wrong length).'];
        yield 'letter' => ['280.012.389-3A', 'Invalid Brazilian CPF (invalid character).'];
        yield 'slash' => ['280.012.389/38', 'Invalid Brazilian CPF (wrong length).'];
        yield 'empty' => ['', 'Invalid Brazilian CPF (wrong length).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Brazilian CPF (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Cpf => Cpf::from($value), $input, $message);
    }
}
