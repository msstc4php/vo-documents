<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
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
        return [new RuPersonNameCodec(), new AuPersonNameCodec(), new DePersonNameCodec(), new FrPersonNameCodec()];
    }
}
