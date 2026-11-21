<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * Document number of a TD1 or TD2 MRZ, with the long form of Doc 9303 Part 5 §4.2.2.1 note j and Part 6 §4.2.2.2
 * note j: a `<` in the check digit position, the remaining characters at the start of the optional data followed by
 * their check digit and a `<`.
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

        $fallback = null;
        for ($overflow = 1; $overflow + 1 < \strlen($optional); $overflow++) {
            $number = $principal . \substr($optional, 0, $overflow);
            if ($optional[$overflow + 1] !== '<' || !MrzCheckDigit::matches($number, $optional[$overflow])) {
                continue;
            }

            $rest = \substr($optional, $overflow + 2);
            $candidate = ['number' => \rtrim($number, '<'), 'optional' => $rest];
            if (\strspn($rest, '<') === \strlen($rest)) {
                return $candidate;
            }

            $fallback ??= $candidate;
        }

        return $fallback;
    }
}
