<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Brazilian passport number, "composto por duas letras - chamadas de "série", e por seis dígitos subsequentes. Por
 * exemplo: Passaporte CS265436" (Polícia Federal). Format only: no check digit or letter set is published (the MRZ
 * check digit is separate). `masked()` keeps the last three characters, the number having eight (no official masking
 * rule): `*****436`.
 *
 * @see https://www.gov.br/pf/pt-br/assuntos/passaporte/ajuda/duvidas_/caderneta/caderneta-numero-onde-fica-e Polícia
 *      Federal, "Número do passaporte: onde fica e outras informações a respeito"
 * @see docs/sources/BR.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Brazilian passport number';

    /**
     * @return non-empty-string the two letters the Polícia Federal calls the "série", e.g. "CS"
     */
    public function series(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z]{2}[0-9]{6}')->parse($value, self::DOCUMENT);
    }
}
