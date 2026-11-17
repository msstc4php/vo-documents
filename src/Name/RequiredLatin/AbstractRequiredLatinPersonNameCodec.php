<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string|null, latin: string}; `family` and `latin` are required, `given` may be missing or
 * null; latin is the `Icao\PersonName` string. Other keys are refused.
 *
 * @internal
 *
 * @phpstan-import-type RequiredLatinPayloadShape from AbstractRequiredLatinPersonName
 *
 * @template TName of AbstractRequiredLatinPersonName the country's composite name
 *
 * @implements PayloadCodec<TName>
 */
abstract readonly class AbstractRequiredLatinPersonNameCodec implements PayloadCodec
{
    private const array KEYS = ['family', 'given', 'latin'];

    /**
     * @return RequiredLatinPayloadShape
     */
    #[Override]
    final public function encode(ValueObject $value): array
    {
        return PayloadFields::encodable($value, $this->valueObjectClass(), static::class)->jsonSerialize();
    }

    /**
     * @return TName
     *
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    final public function decode(array $payload): AbstractRequiredLatinPersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, $this->shape());

        return $this->create(
            PayloadFields::string($payload, 'family', $this->shape()),
            PayloadFields::nullableString($payload, 'given', $this->shape()),
            PayloadFields::latin($payload, $this->shape()),
        );
    }

    /**
     * @return non-empty-string the message of every shape failure
     */
    abstract protected function shape(): string;

    /**
     * @return TName
     *
     * @throws InvalidPersonNameException
     */
    abstract protected function create(
        string $family,
        ?string $given,
        IcaoPersonName $latin,
    ): AbstractRequiredLatinPersonName;
}
