<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\LatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\SingleWordNamePart;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
final class AbstractNamePartTest extends TestCase
{
    public function testSeparatorsAreTakenLiterally(): void
    {
        self::assertSame('ab/cd.ef]gh', LatinNamePart::from('ab/cd.ef]gh')->value);
        self::assertFalse(LatinNamePart::isValid('ab-cd'));
        self::assertFalse(LatinNamePart::isValid('ab\\cd'));
    }

    public function testNoSeparatorsMeansOneWord(): void
    {
        self::assertSame('abc', SingleWordNamePart::from('abc')->value);
        self::assertFalse(SingleWordNamePart::isValid('ab c'));
    }
}
