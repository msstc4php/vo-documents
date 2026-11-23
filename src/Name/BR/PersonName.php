<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\BR;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use Override;

/**
 * Surnames and given names, both required: the name comprises "o prenome […] e o sobrenome", and must contain "o
 * sobrenome de, ao menos, um ascendente" (CNJ Código Nacional de Normas, art. 515-B). The derived Latin form is
 * SURNAMES<<GIVEN through the ICAO multinational Latin table (`da Silva, João` → `DA<SILVA<<JOAO`, `Conceição` →
 * `CONCEICAO`). Override policy {@see AnyIcaoSpelling}: the Polícia Federal's passport system abbreviates a surname or
 * given name over 30 characters, first dropping the particles "de", "da", "dos" and then reducing the penultimate
 * word to one letter, and "a abreviação não configura erro no passaporte", so a passport name need not be a
 * transliteration: any valid ICAO name is kept as the override, as typed, also when a letter has no ICAO spelling;
 * one equal to the derived form is not kept. See docs/sources/BR.md "Names".
 *
 * @see https://atos.cnj.jus.br/atos/detalhar/5243 CNJ, Código Nacional de Normas (Provimento n. 149/2023),
 *      art. 515-B
 * @see https://www.gov.br/pf/pt-br/assuntos/passaporte/duvidas Polícia Federal, Passaporte, Dúvidas, "Nome abreviado
 *      no passaporte"
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling and no override is given
     */
    public function __construct(FamilyName $family, GivenName $given, ?IcaoPersonName $latin = null)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): AnyIcaoSpelling
    {
        return new AnyIcaoSpelling();
    }

    #[Override]
    protected static function payloadCodec(): PersonNameCodec
    {
        return new PersonNameCodec();
    }
}
