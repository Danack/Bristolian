<?php

namespace BristolianTest;

use Bristolian\ToString;
use Bristolian\FromString;
use Bristolian\Model\Chat\UserChatMessage;

/**
 * Tests all Model classes that use the ToString trait
 * to ensure they can be serialized and deserialized correctly.
 *
 * @coversNothing
 */
class ToStringTraitTest extends BaseTestCase
{
    /**
     * Get all classes in the Model directory that use the ToString trait
     *
     * @return array<class-string>
     */
    private function getModelClassesWithToStringTrait(): array
    {
        $modelDir = __DIR__ . '/../../src/Bristolian/Model';
        $classes = [];

        if (!is_dir($modelDir)) {
            return [];
        }

        $files = scandir($modelDir);
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = 'Bristolian\\Model\\' . pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $traits = $reflection->getTraitNames();

            if (in_array(ToString::class, $traits, true)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

    /**
     * Factory method to create instances of Model classes for testing
     *
     * @param class-string $className
     * @return object
     */
    private function createModelInstance(string $className): object
    {
        // Add factory methods for each class that uses ToString
        return match ($className) {
            UserChatMessage::class => new UserChatMessage(
                id: 123,
                user_id: 'user-456',
                room_id: 'room-789',
                text: 'Hello, world! This is a test message.',
                message_reply_id: 100,
                created_at: new \DateTimeImmutable('2024-01-15 10:30:00')
            ),
            default => throw new \RuntimeException("No factory method defined for class: $className")
        };
    }

    /**
     * Get expected values for a model instance's properties
     *
     * @param object $instance
     * @return array<string, mixed>
     */
    private function getExpectedPropertyValues(object $instance): array
    {
        $reflection = new \ReflectionClass($instance);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            
            // Skip properties that start with __ (these are marked to be skipped in ToString trait)
            if (str_starts_with($name, '__')) {
                continue;
            }

            $value = $property->getValue($instance);
            $properties[$name] = $value;
        }

        return $properties;
    }

    /**
     * Test that all Model classes with ToString trait can serialize and deserialize correctly
     *
     * @dataProvider modelClassProvider
     * @param class-string $className
     */
    public function testToStringAndFromStringRoundTrip(string $className): void
    {
        // Create an instance
        $original = $this->createModelInstance($className);

        // Convert to string
        $this->assertMethodExists($original, 'toString');
        $serialized = $original->toString();

        // Verify it's valid JSON
        $this->assertJson($serialized, "toString() should produce valid JSON");

        // Convert back from string
        $this->assertMethodExists($className, 'fromString');
        $recreated = $className::fromString($serialized);

        // Verify the recreated object is of the correct type
        $this->assertInstanceOf($className, $recreated);

        // Compare all public properties
        $originalProperties = $this->getExpectedPropertyValues($original);
        $recreatedProperties = $this->getExpectedPropertyValues($recreated);

        foreach ($originalProperties as $propertyName => $originalValue) {
            $this->assertArrayHasKey(
                $propertyName,
                $recreatedProperties,
                "Recreated object should have property: $propertyName"
            );

            $recreatedValue = $recreatedProperties[$propertyName];

            // Special handling for DateTime objects - compare at second precision
            // since serialization may lose microsecond precision
            if ($originalValue instanceof \DateTimeInterface && $recreatedValue instanceof \DateTimeInterface) {
                $this->assertEquals(
                    $originalValue->getTimestamp(),
                    $recreatedValue->getTimestamp(),
                    "DateTime property '$propertyName' should match at second precision"
                );
            } else {
                $this->assertSame(
                    $originalValue,
                    $recreatedValue,
                    "Property '$propertyName' should match after round-trip"
                );
            }
        }
    }

    /**
     * Test that Model classes with null values handle serialization correctly
     *
     * @dataProvider modelClassWithNullValuesProvider
     * @param class-string $className
     */
    public function testToStringAndFromStringWithNullValues(string $className, object $instance): void
    {
        // Convert to string
        $serialized = $instance->toString();
        
        // Verify it's valid JSON
        $this->assertJson($serialized);

        // Convert back from string
        $recreated = $className::fromString($serialized);

        // Verify properties match
        $originalProperties = $this->getExpectedPropertyValues($instance);
        $recreatedProperties = $this->getExpectedPropertyValues($recreated);

        foreach ($originalProperties as $propertyName => $originalValue) {
            $recreatedValue = $recreatedProperties[$propertyName];

            // Compare at second precision for DateTime since serialization may lose microseconds
            if ($originalValue instanceof \DateTimeInterface && $recreatedValue instanceof \DateTimeInterface) {
                $this->assertEquals(
                    $originalValue->getTimestamp(),
                    $recreatedValue->getTimestamp(),
                    "DateTime property '$propertyName' should match at second precision"
                );
            } else {
                $this->assertSame(
                    $originalValue,
                    $recreatedValue,
                    "Property '$propertyName' should match (including null values)"
                );
            }
        }
    }

    /**
     * Provide model classes that use ToString trait
     *
     * @return array<string, array{class-string}>
     */
    public function modelClassProvider(): array
    {
        $classes = $this->getModelClassesWithToStringTrait();
        $data = [];

        foreach ($classes as $className) {
            $shortName = (new \ReflectionClass($className))->getShortName();
            $data[$shortName] = [$className];
        }

        return $data;
    }

    /**
     * Provide model classes with instances that have null values
     *
     * @return array<string, array{class-string, object}>
     */
    public function modelClassWithNullValuesProvider(): array
    {
        return [
            'ChatMessage with null reply_id' => [
                UserChatMessage::class,
                new UserChatMessage(
                    id: 1,
                    user_id: 'user-123',
                    room_id: 'room-456',
                    text: 'Message without reply',
                    message_reply_id: null,
                    created_at: new \DateTimeImmutable('2024-01-15 12:00:00')
                )
            ],
        ];
    }

    /**
     * Helper to assert a method exists on an object or class
     *
     * @param object|class-string $objectOrClass
     * @param string $methodName
     */
    private function assertMethodExists($objectOrClass, string $methodName): void
    {
        $className = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;
        $this->assertTrue(
            method_exists($objectOrClass, $methodName),
            "Class $className should have method $methodName"
        );
    }

    /**
     * Test that toArray method works correctly
     */
    public function testToArrayReturnsCorrectStructure(): void
    {
        $chatMessage = new UserChatMessage(
            id: 999,
            user_id: 'user-abc',
            room_id: 'room-xyz',
            text: 'Test array conversion',
            message_reply_id: null,
            created_at: new \DateTimeImmutable('2024-01-15 14:30:00')
        );

        $array = $chatMessage->toArray();

        // Verify array has expected keys
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('room_id', $array);
        $this->assertArrayHasKey('text', $array);
        $this->assertArrayHasKey('message_reply_id', $array);
        $this->assertArrayHasKey('created_at', $array);

        // Verify values
        $this->assertSame(999, $array['id']);
        $this->assertSame('user-abc', $array['user_id']);
        $this->assertSame('room-xyz', $array['room_id']);
        $this->assertSame('Test array conversion', $array['text']);
        $this->assertNull($array['message_reply_id']);
        $this->assertIsString($array['created_at']);
    }
}

