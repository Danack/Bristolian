<?php

declare(strict_types=1);

namespace BristolianTest;

use Bristolian\Exception\BristolianException;
use Bristolian\ToString;

/**
 * Test class that uses the ToString trait with scalar and datetime properties.
 *
 * @coversNothing
 */
class TestToStringClass
{
    use ToString;

    public function __construct(
        public readonly string $foo,
        public readonly int $bar,
        public readonly ?\DateTimeInterface $created_at = null
    ) {
    }
}

/**
 * Test class with __-prefixed property (should be omitted from toArray/toString).
 *
 * @coversNothing
 */
class TestToStringClassWithSkippedProperty
{
    use ToString;

    public function __construct(
        public readonly string $visible,
        public readonly string $__internal = 'skipped'
    ) {
    }
}

/**
 * Test class with unsupported property type (should throw when converting).
 *
 * @coversNothing
 */
class TestToStringClassWithUnsupportedProperty
{
    use ToString;

    public function __construct(
        public readonly string $foo,
        public readonly \stdClass $unsupported
    ) {
    }
}

/**
 * @covers \Bristolian\ToString
 */
class ToStringTest extends BaseTestCase
{
    public function test_toArray_returns_properties_as_array(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-15T10:30:00+00:00');
        $object = new TestToStringClass('hello', 42, $createdAt);

        $result = $object->toArray();

        $this->assertSame('hello', $result['foo']);
        $this->assertSame(42, $result['bar']);
        $this->assertSame('2024-01-15T10:30:00+00:00', $result['created_at']);
    }

    public function test_toArray_skips_properties_starting_with_double_underscore(): void
    {
        $object = new TestToStringClassWithSkippedProperty('visible', 'ignored');

        $result = $object->toArray();

        $this->assertSame(['visible' => 'visible'], $result);
        $this->assertArrayNotHasKey('__internal', $result);
    }

    public function test_toArray_throws_BristolianException_when_property_cannot_be_converted(): void
    {
        $object = new TestToStringClassWithUnsupportedProperty('foo', new \stdClass());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Failed to convert object to array on item [unsupported]');

        $object->toArray();
    }

    public function test_toString_returns_valid_json_of_toArray(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-15T10:30:00+00:00');
        $object = new TestToStringClass('hello', 42, $createdAt);

        $string = $object->toString();

        $this->assertJson($string);
        $decoded = json_decode($string, true);
        $this->assertIsArray($decoded);
        $this->assertSame('hello', $decoded['foo']);
        $this->assertSame(42, $decoded['bar']);
        $this->assertSame('2024-01-15T10:30:00+00:00', $decoded['created_at']);
    }

    public function test_toString_matches_json_encode_safe_of_toArray(): void
    {
        $object = new TestToStringClass('a', 1, null);

        $this->assertSame(json_encode_safe($object->toArray()), $object->toString());
    }

    public function test_toString_with_null_datetime(): void
    {
        $object = new TestToStringClass('x', 0, null);

        $string = $object->toString();
        $decoded = json_decode($string, true);

        $this->assertNull($decoded['created_at']);
    }
}
