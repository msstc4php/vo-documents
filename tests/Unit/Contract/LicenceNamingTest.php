<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Holds for every licence class of the package, including the ones later plans add.
 */
#[CoversNothing]
final class LicenceNamingTest extends TestCase
{
    public function testNoClassIsNamedDrivingLicence(): void
    {
        $names = $this->documentClassNames();
        self::assertNotEmpty($names);

        foreach ($names as $name) {
            self::assertStringNotContainsString('DrivingLicence', $name);
        }
    }

    public function testEveryLicenceClassIsNamedDriverLicence(): void
    {
        $licences = \array_filter(
            $this->documentClassNames(),
            static fn (string $name): bool => \str_contains($name, 'Licence'),
        );
        self::assertNotEmpty($licences);

        foreach ($licences as $name) {
            self::assertStringContainsString('DriverLicence', $name);
        }
    }

    /**
     * @return list<string> the short class names under src/Document
     */
    private function documentClassNames(): array
    {
        $source = \dirname(__DIR__, 3) . '/src/Document';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        $names = [];
        foreach ($files as $file) {
            \assert($file instanceof SplFileInfo);
            if ($file->getExtension() === 'php') {
                $names[] = $file->getBasename('.php');
            }
        }

        return $names;
    }
}
