<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string|null, latin: string}, all three keys required (given may be null); latin is the
 * `Icao\PersonName` string. Other keys are refused.
 *
 * @internal
 *
 * @template TName of AbstractFamilyGivenPersonName the country's composite name
 *
 * @implements PayloadCodec<TName>
 */
abstract readonly class AbstractFamilyGivenPersonNameCodec implements PayloadCodec
{
    private const array KEYS = ['family', 'given', 'latin'];

    /**
     * @return array{family: string, given: string|null, latin: string}
     */
    #[Override]
    final public function encode(ValueObject $value): array
    {
        $class = $this->valueObjectClass();
        if (!$value instanceof $class) {
            $message = \sprintf('%s encodes %s, %s given.', static::class, $class, $value::class);

            throw new LogicException($message);
        }

        return $value->jsonSerialize();
    }

    /**
     * @return TName
     *
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    final public function decode(array $payload): AbstractFamilyGivenPersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, $this->shape());

        if (!\array_key_exists('given', $payload)) {
            throw new InvalidPayloadException($this->shape());
        }

        return $this->create(
            PayloadFields::string($payload, 'family', $this->shape()),
            PayloadFields::nullableString($payload, 'given', $this->shape()),
            IcaoPersonName::from(PayloadFields::string($payload, 'latin', $this->shape())),
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
    ): AbstractFamilyGivenPersonName;
}
