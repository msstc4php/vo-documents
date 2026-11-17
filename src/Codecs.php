<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\BR\PersonNameCodec as BrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CA\PersonNameCodec as CaPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec as ChPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonNameCodec as CnPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec as GbPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonNameCodec as JpPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec as MxPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\US\PersonNameCodec as UsPersonNameCodec;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;

/**
 * Every payload codec of the package, for `new ValueObjectMapper(Codecs::all())`.
 */
final class Codecs
{
    /**
     * @return list<PayloadCodec<ValueObject>>
     */
    public static function all(): array
    {
        return [
            new RuPersonNameCodec(),
            new AuPersonNameCodec(),
            new DePersonNameCodec(),
            new FrPersonNameCodec(),
            new ItPersonNameCodec(),
            new EsPersonNameCodec(),
            new NlPersonNameCodec(),
            new ChPersonNameCodec(),
            new GbPersonNameCodec(),
            new TrPersonNameCodec(),
            new UsPersonNameCodec(),
            new CaPersonNameCodec(),
            new MxPersonNameCodec(),
            new BrPersonNameCodec(),
            new CnPersonNameCodec(),
            new JpPersonNameCodec(),
        ];
    }
}
