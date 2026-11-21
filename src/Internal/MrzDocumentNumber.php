<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * Document number of a TD1 or TD2 MRZ, with the long form of Doc 9303 Part 5 §4.2.2.1 note j and Part 6 §4.2.2.2
 * note j: a `<` in the check digit position, the remaining characters at the start of the optional data followed by
 * their check digit and a `<` (Part 5 §4.2.4: the check digit follows the last digit of the number and is followed
 * by `<`, so the number ends at the first `<`).
 *
 * @internal
 */
final class MrzDocumentNumber
{
    /**
     * @param string $principal the nine characters of the document number field
     * @param string $check the character in the check digit position
     * @param string $optional the optional data field
     *
     * @return array{number: string, optional: string}|null the number (without trailing fillers) and the optional data
     *                                                      that remain, or null when the check digit does not match
     */
    public static function resolve(string $principal, string $check, string $optional, bool $longAllowed): ?array
    {
        if ($check !== '<') {
            return MrzCheckDigit::matches($principal, $check)
                ? ['number' => \rtrim($principal, '<'), 'optional' => $optional]
                : null;
        }

        if (!$longAllowed) {
            return null;
        }

        $end = \strpos($optional, '<');
        if ($end === false || $end < 2) {
            return null;
        }

        $number = $principal . \substr($optional, 0, $end - 1);
        if (!MrzCheckDigit::matches($number, $optional[$end - 1])) {
            return null;
        }

        return ['number' => \rtrim($number, '<'), 'optional' => \substr($optional, $end + 1)];
    }
}
