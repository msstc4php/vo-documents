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
     * of three or more letters of `$input` (as typed or upper-cased), so no name part is echoed.
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

            return;
        }

        Assert::fail(\sprintf('Expected %s.', $exception));
    }
}
