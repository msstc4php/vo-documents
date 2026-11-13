<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string|null, latin: string|null}; latin holds only an override, the derived form is never
 * stored. A missing nullable key reads as null. Other keys are refused, so the name's `jsonSerialize()` view (which
 * has `effectiveLatin`) is never read as a payload that silently drops an override.
 *
 * @internal
 *
 * @template TName of AbstractFamilyGivenPersonName the country's composite name
 * @template TGivenValue of string|null the stored "given": string where the country requires a given name
 *
 * @implements PayloadCodec<TName>
 */
abstract readonly class AbstractFamilyGivenPersonNameCodec implements PayloadCodec
{
    private const array KEYS = ['family', 'given', 'latin'];

    /**
     * @return array{family: string, given: TGivenValue, latin: string|null}
     */
    #[Override]
    final public function encode(ValueObject $value): array
    {
        $class = $this->valueObjectClass();
        if (!$value instanceof $class) {
            $message = \sprintf('%s encodes %s, %s given.', static::class, $class, $value::class);

            throw new LogicException($message);
        }

        return [
            'family' => $value->family->value,
            'given' => $this->givenValue($value),
            'latin' => $value->latinOverride()?->value,
        ];
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
        $latin = PayloadFields::nullableString($payload, 'latin', $this->shape());

        return $this->create(
            PayloadFields::string($payload, 'family', $this->shape()),
            PayloadFields::nullableString($payload, 'given', $this->shape()),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }

    /**
     * @return non-empty-string the message of every shape failure
     */
    abstract protected function shape(): string;

    /**
     * @return TName
     *
     * @throws InvalidPayloadException when $given is null and the country requires a given name
     * @throws InvalidPersonNameException
     */
    abstract protected function create(
        string $family,
        ?string $given,
        ?IcaoPersonName $latin,
    ): AbstractFamilyGivenPersonName;

    /**
     * @param TName $name
     *
     * @return TGivenValue
     */
    abstract protected function givenValue(AbstractFamilyGivenPersonName $name): ?string;

    final protected function malformed(): InvalidPayloadException
    {
        return new InvalidPayloadException($this->shape());
    }
}
