<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use Closure;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use PHPUnit\Framework\Assert;
use Throwable;

trait RefusesNameWithoutEchoingInput
{
    /**
     * Asserts that `$create()` throws `$exception` with exactly `$expectedMessage`, and that the message holds no word
     * of three or more letters of `$input` (as typed or upper-cased) and no non-ASCII letter of it at all, so no name
     * part is echoed, however short (`张`, `민준`), and no number equal to its length.
     *
     * @param Closure(): mixed $create
     * @param class-string<Throwable> $exception
     */
    private static function assertNameRefusedWithoutEcho(
        Closure $create,
        string $input,
        string $expectedMessage,
        string $exception = InvalidPersonNameException::class,
    ): void {
        try {
            $create();
        } catch (Throwable $thrown) {
            Assert::assertInstanceOf($exception, $thrown);
            Assert::assertSame($expectedMessage, $thrown->getMessage());
            \preg_match_all('/\p{L}{3,}/u', $input, $words);
            foreach ($words[0] as $word) {
                Assert::assertStringNotContainsString($word, $thrown->getMessage());
                Assert::assertStringNotContainsString(\mb_strtoupper($word, 'UTF-8'), $thrown->getMessage());
            }

            Assert::assertDoesNotMatchRegularExpression(
                '/(?<![0-9])' . \mb_strlen($input, 'UTF-8') . '(?![0-9])/',
                $thrown->getMessage(),
            );

            \preg_match_all('/(?![\x00-\x7F])\p{L}/u', $input, $letters);
            foreach ($letters[0] as $letter) {
                Assert::assertStringNotContainsString($letter, $thrown->getMessage());
            }

            return;
        }

        Assert::fail(\sprintf('Expected %s.', $exception));
    }

    /**
     * The string fields of a payload, as the probe of {@see assertNameRefusedWithoutEcho()}.
     *
     * @param array<array-key, mixed> $payload
     */
    private static function payloadProbe(array $payload): string
    {
        return \implode(' ', \array_filter($payload, \is_string(...)));
    }
}
