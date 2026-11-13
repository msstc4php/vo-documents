<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\PackageString;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\TestSubdivision;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubdivisionResolver::class)]
#[UsesClass(AmbiguousJurisdictionException::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class SubdivisionResolverTest extends TestCase
{
    /** @var list<array{TestSubdivision, string}> */
    private array $calls = [];

    public function testReturnsTheNumberOfTheOnlyMatchingSubdivision(): void
    {
        $number = $this->resolver()->resolve('N-1');

        self::assertInstanceOf(PackageString::class, $number);
        self::assertSame('Nsw:N-1', $number->value);
    }

    public function testParsesOncePerSubdivision(): void
    {
        $this->resolver()->resolve('N-1');

        self::assertSame([[TestSubdivision::Nsw, 'N-1'], [TestSubdivision::Vic, 'N-1']], $this->calls);
    }

    public function testListsTheMatchesInTheGivenOrder(): void
    {
        self::assertSame([TestSubdivision::Nsw, TestSubdivision::Vic], $this->resolver()->matching('NV-1'));
        self::assertSame([TestSubdivision::Vic], $this->resolver()->matching('V-1'));
        self::assertSame([], $this->resolver()->matching('X-1'));
    }

    public function testRefusesSeveralMatchesWithTheSubdivisionsInTheGivenOrder(): void
    {
        $resolver = new SubdivisionResolver(
            'Test licence',
            [TestSubdivision::Vic, TestSubdivision::Nsw],
            static fn (TestSubdivision $subdivision, string $number): PackageString => PackageString::from($number),
        );

        try {
            $resolver->resolve('123456');
        } catch (AmbiguousJurisdictionException $exception) {
            self::assertSame([TestSubdivision::Vic, TestSubdivision::Nsw], $exception->jurisdictions());
            self::assertSame('Test licence number fits several jurisdictions: AU-VIC, AU-NSW.', $exception->getMessage());

            return;
        }

        self::fail('Expected an AmbiguousJurisdictionException.');
    }

    public function testRefusesANumberValidNowhereWithItsLength(): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage('Invalid Test licence number (3 characters).');

        $this->resolver()->resolve(' X-12 ');
    }

    /**
     * @return SubdivisionResolver<TestSubdivision, PackageString>
     */
    private function resolver(): SubdivisionResolver
    {
        return new SubdivisionResolver(
            'Test licence',
            TestSubdivision::cases(),
            function (TestSubdivision $subdivision, string $number): ?PackageString {
                $this->calls[] = [$subdivision, $number];
                $letter = $subdivision === TestSubdivision::Nsw ? 'N' : 'V';

                return \str_contains($number, $letter) ? PackageString::from($subdivision->name . ':' . $number) : null;
            },
        );
    }
}
