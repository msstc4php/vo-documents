<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Exception;

use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\TestSubdivision;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AmbiguousJurisdictionException::class)]
final class AmbiguousJurisdictionExceptionTest extends TestCase
{
    public function testMessageListsIsoCodesOnly(): void
    {
        $exception = new AmbiguousJurisdictionException('AU driver licence', [TestSubdivision::Nsw, TestSubdivision::Vic]);

        self::assertSame('AU driver licence number fits several jurisdictions: AU-NSW, AU-VIC.', $exception->getMessage());
    }

    public function testJurisdictionsKeepGivenOrder(): void
    {
        $exception = new AmbiguousJurisdictionException('AU driver licence', [TestSubdivision::Vic, TestSubdivision::Nsw]);

        self::assertSame([TestSubdivision::Vic, TestSubdivision::Nsw], $exception->jurisdictions());
    }
}
