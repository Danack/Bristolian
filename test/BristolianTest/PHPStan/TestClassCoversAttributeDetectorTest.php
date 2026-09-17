<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan;

use Bristolian\PHPStan\TestClassCoversAttributeDetector;
use BristolianTest\BaseTestCase;
use BristolianTest\PHPStan\Fixtures\Covers\DocblockCoversNothingOnlyFixture;
use BristolianTest\PHPStan\Fixtures\Covers\HasCoversClassAttributeFixture;
use BristolianTest\PHPStan\Fixtures\Covers\HasCoversNothingAttributeFixture;
use BristolianTest\PHPStan\Fixtures\Covers\NoCoversAttributeFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

#[CoversClass(\Bristolian\PHPStan\TestClassCoversAttributeDetector::class)]
class TestClassCoversAttributeDetectorTest extends BaseTestCase
{
    private TestClassCoversAttributeDetector $detector;

    public function setUp(): void
    {
        parent::setUp();
        $this->detector = new TestClassCoversAttributeDetector();
    }

    /**
     * @return \Generator<string, array{class-string, bool}>
     */
    public static function provides_reflection_class_has_covers_attribute_cases(): \Generator
    {
        yield 'CoversNothing attribute' => [HasCoversNothingAttributeFixture::class, true];
        yield 'CoversClass attribute' => [HasCoversClassAttributeFixture::class, true];
        yield 'docblock only does not count' => [DocblockCoversNothingOnlyFixture::class, false];
        yield 'no covers at all' => [NoCoversAttributeFixture::class, false];
    }

    /**
     * @dataProvider provides_reflection_class_has_covers_attribute_cases
     */
    #[DataProvider('provides_reflection_class_has_covers_attribute_cases')]
    public function test_reflection_class_has_covers_attribute(
        string $className,
        bool $expectedHasCovers
    ): void {
        $reflectionClass = new ReflectionClass($className);
        $this->assertSame(
            $expectedHasCovers,
            $this->detector->reflectionClassHasCoversAttribute($reflectionClass)
        );
    }

    /**
     * @return \Generator<string, array{string, bool}>
     */
    public static function provides_is_test_class_short_name_cases(): \Generator
    {
        yield 'ends with Test' => ['FooTest', true];
        yield 'exact Test' => ['Test', true];
        yield 'Fixture' => ['FooFixture', false];
        yield 'empty' => ['', false];
    }

    /**
     * @dataProvider provides_is_test_class_short_name_cases
     */
    #[DataProvider('provides_is_test_class_short_name_cases')]
    public function test_is_test_class_short_name(string $shortName, bool $expected): void
    {
        $this->assertSame($expected, $this->detector->isTestClassShortName($shortName));
    }

    /**
     * @return \Generator<string, array{string, bool}>
     */
    public static function provides_is_path_under_test_directory_cases(): \Generator
    {
        yield 'container absolute' => ['/var/app/test/BristolianTest/FooTest.php', true];
        yield 'relative' => ['test/BristolianTest/FooTest.php', true];
        yield 'windows separators' => ['C:\\project\\test\\FooTest.php', true];
        yield 'src path' => ['/var/app/src/Bristolian/Foo.php', false];
        yield 'testing in name only' => ['/var/app/src/Bristolian/Testing/Foo.php', false];
    }

    /**
     * @dataProvider provides_is_path_under_test_directory_cases
     */
    #[DataProvider('provides_is_path_under_test_directory_cases')]
    public function test_is_path_under_test_directory(string $path, bool $expected): void
    {
        $this->assertSame($expected, $this->detector->isPathUnderTestDirectory($path));
    }
}
