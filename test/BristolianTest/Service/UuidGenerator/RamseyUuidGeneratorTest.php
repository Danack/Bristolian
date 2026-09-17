<?php

declare(strict_types = 1);

namespace BristolianTest\Service\UuidGenerator;

use Bristolian\Service\UuidGenerator\RamseyUuidGenerator;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\UuidGenerator\RamseyUuidGenerator::class, 'generate')]

class RamseyUuidGeneratorTest extends BaseTestCase
{
    public function test_generate_returns_valid_uuid7_format(): void
    {
        $generator = new RamseyUuidGenerator();

        $uuid = $generator->generate();

        $this->assertNotEmpty($uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'Should be a valid UUID v7 format'
        );
    }

    public function test_generate_returns_different_values_each_call(): void
    {
        $generator = new RamseyUuidGenerator();

        $uuid1 = $generator->generate();
        $uuid2 = $generator->generate();

        $this->assertNotSame($uuid1, $uuid2);
    }
}
