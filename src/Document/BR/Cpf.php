<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Cadastro de Pessoas Físicas, `280.012.389-38`: nine digits and two check digits of the Receita Federal's "DV módulo
 * 11", the remainder by 11 of the digits weighted 1 to 9 from the left (then 0 to 9 with the first check digit), "o
 * resto 10 é considerado 0". The ninth digit is the fiscal region of the first registration address
 * ({@see fiscalRegion()}). A number of one repeated digit (`111.111.111-11`) passes the arithmetic and is accepted: no
 * Receita Federal text refuses it (other agencies' intake rules do; docs/sources/BR.md "CPF"). Dots are accepted as
 * separators. `masked()` keeps the last four digits (no official masking rule): `***.***.*89-38`.
 *
 * @see http://sped.rfb.gov.br/estatico/27/69D86AF38819D6F10B6EE8C145B0005D5F41D2/ADE%2041_2017%20-%20Anexo%20II%20-%20Manual%20RERCT.pdf
 *      Receita Federal, Anexo II do Ato Declaratório Executivo COFIS nº 41 (2017), REGRA_VALIDA_CPF
 * @see https://www.gov.br/receitafederal/pt-br/assuntos/educacao-fiscal/educacao-fiscal/publicacoes/folhetos/cpf.pdf
 *      Receita Federal, "Por que a Receita Federal é responsável pelo meu CPF?"
 * @see https://www.gov.br/previdencia/pt-br/outros/imagens/2015/07/rgrva_RegrasValidacao.pdf Ministério da
 *      Previdência (Dataprev), SIPREV Gestão - Cadastros, Regras de Validação, versão 1.14, RV_01 (display form)
 * @see docs/sources/BR.md "CPF"
 */
final class Cpf extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian CPF';

    private const array FIRST_WEIGHTS = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    private const array SECOND_WEIGHTS = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    /**
     * @return non-empty-string the Receita Federal's region digit, "0" to "9" (e.g. "8" for São Paulo, "9" for Paraná
     *                          and Santa Catarina); one region spans several states, so it is not mapped to a state
     */
    public function fiscalRegion(): string
    {
        return $this->value[8];
    }

    /**
     * @return non-empty-string e.g. "280.012.389-38"
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s.%s.%s-%s',
            \substr($this->value, 0, 3),
            \substr($this->value, 3, 3),
            \substr($this->value, 6, 3),
            \substr($this->value, 9),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly(\str_replace('.', '', $value), 11, self::DOCUMENT);
        $first = $this->checkDigit(\substr($number, 0, 9), self::FIRST_WEIGHTS);
        $second = $this->checkDigit(\substr($number, 0, 9) . $first, self::SECOND_WEIGHTS);
        if (\substr($number, 9) !== $first . $second) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }

    /**
     * @param list<int> $weights
     */
    private function checkDigit(string $digits, array $weights): string
    {
        return (string) (Checksum::weightedMod($digits, $weights, 11) % 10);
    }
}
