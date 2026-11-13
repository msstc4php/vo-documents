<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Contract;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionEnum;
use SplFileInfo;

/**
 * Holds for every subdivision enum of the package, including the ones later plans add.
 */
#[CoversNothing]
final class SubdivisionTest extends TestCase
{
    private const string NAMESPACE = 'MSSTC4PHP\\ValueObject\\Documents\\';

    /**
     * @return iterable<string, array{class-string<Subdivision>}>
     */
    public static function subdivisionEnums(): iterable
    {
        $source = \dirname(__DIR__, 3) . '/src/';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($files as $file) {
            \assert($file instanceof SplFileInfo);
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = \substr($file->getPathname(), \strlen($source), -4);
            $class = self::NAMESPACE . \str_replace('/', '\\', $relative);
            if (\enum_exists($class) && \is_subclass_of($class, Subdivision::class)) {
                yield $class => [$class];
            }
        }
    }

    public function testThePackageHasSubdivisionEnums(): void
    {
        self::assertNotEmpty(\iterator_to_array(self::subdivisionEnums()));
    }

    /**
     * @param class-string<Subdivision> $enum
     */
    #[DataProvider('subdivisionEnums')]
    public function testIsStringBacked(string $enum): void
    {
        self::assertSame('string', (string) new ReflectionEnum($enum)->getBackingType());
    }

    /**
     * @param class-string<Subdivision> $enum
     */
    #[DataProvider('subdivisionEnums')]
    public function testEveryValueIsItsIsoCode(string $enum): void
    {
        foreach ($enum::cases() as $case) {
            self::assertSame($case->value, $case->isoCode());
            self::assertMatchesRegularExpression('/^[A-Z]{2}-[A-Z0-9]{1,3}$/D', $case->isoCode());
        }
    }
}
