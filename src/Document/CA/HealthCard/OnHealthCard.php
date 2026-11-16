<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

/**
 * The two fields of a printed Ontario health card, as {@see OnHealthCardNumber::fromCard()} splits them. Not a value
 * object of its own: the ministry's validation service takes `healthNumber` and `versionCode` as two fields, and the
 * number alone identifies the person across card versions, so each part is stored on its own.
 */
final readonly class OnHealthCard
{
    public function __construct(private OnHealthCardNumber $number, private ?OnHealthCardVersionCode $versionCode)
    {
    }

    public function number(): OnHealthCardNumber
    {
        return $this->number;
    }

    /**
     * @return OnHealthCardVersionCode|null null for a red-and-white card printed without one
     */
    public function versionCode(): ?OnHealthCardVersionCode
    {
        return $this->versionCode;
    }
}
