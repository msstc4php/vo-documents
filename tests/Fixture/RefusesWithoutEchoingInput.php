<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use Closure;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use PHPUnit\Framework\Assert;

trait RefusesWithoutEchoingInput
{
    /**
     * Asserts that `$create($input)` throws the expected message, that the message holds no three consecutive digits
     * of the input (as typed or with the separators removed), no run of three or more digits at all, so a character
     * count can never pass for part of the input, and no run of three or more letters of the input (as typed or
     * upper-cased: `vo-documents` upper-cases letters), and no number equal to the input's length (as typed or with the
     * separators removed).
     *
     * @param Closure(string): mixed $create
     */
    private static function assertRefusedWithoutEcho(Closure $create, string $input, string $expectedMessage): void
    {
        try {
            $create($input);
        } catch (InvalidDocumentNumberException $exception) {
            Assert::assertSame($expectedMessage, $exception->getMessage());
            self::assertMessageHidesInput($input, $exception->getMessage());
            Assert::assertDoesNotMatchRegularExpression('/[0-9]{3}/', $exception->getMessage());
            self::assertMessageHidesLetters($input, $exception->getMessage());
            self::assertMessageHidesLength($input, $exception->getMessage());

            return;
        }

        Assert::fail('Expected an InvalidDocumentNumberException.');
    }

    private static function assertMessageHidesLength(string $input, string $message): void
    {
        $lengths = [\mb_strlen($input, 'UTF-8'), \mb_strlen((string) \preg_replace('/[^\p{L}\p{N}]+/u', '', $input), 'UTF-8')];
        foreach (\array_unique($lengths) as $length) {
            Assert::assertDoesNotMatchRegularExpression('/(?<![0-9])' . $length . '(?![0-9])/', $message);
        }
    }

    private static function assertMessageHidesLetters(string $input, string $message): void
    {
        \preg_match_all('/\p{L}{3,}/u', $input, $runs);
        foreach ($runs[0] as $run) {
            Assert::assertStringNotContainsString($run, $message);
            Assert::assertStringNotContainsString(\mb_strtoupper($run, 'UTF-8'), $message);
        }
    }

    private static function assertMessageHidesInput(string $input, string $message): void
    {
        \preg_match_all('/[0-9]{3,}/', $input, $runs);
        $runs[0][] = (string) \preg_replace('/[^0-9]+/', '', $input);

        foreach ($runs[0] as $run) {
            for ($i = 0; $i + 3 <= \strlen($run); $i++) {
                Assert::assertStringNotContainsString(\substr($run, $i, 3), $message);
            }
        }
    }
}
