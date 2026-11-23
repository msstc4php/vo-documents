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
 * {family: string, given: string|null, latinOverride: string|null}; latinOverride holds only an override, the derived
 * form is never stored. A missing nullable key reads as null. The 0.x key `latin` is read as `latinOverride`, so the
 * `jsonSerialize()` view (whose `latin` is the full Latin form) reads back as the same name; other keys are refused.
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
    private const array KEYS = ['family', 'given', 'latinOverride'];

    /**
     * @return array{family: string, given: TGivenValue, latinOverride: string|null}
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
            'latinOverride' => $value->latinOverride()?->value,
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
        $payload = PayloadFields::upgraded($payload, PayloadFields::LEGACY_LATIN_OVERRIDE, $this->shape());
        PayloadFields::assertOnlyKeys($payload, self::KEYS, $this->shape());
        $latin = PayloadFields::nullableString($payload, 'latinOverride', $this->shape());

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
