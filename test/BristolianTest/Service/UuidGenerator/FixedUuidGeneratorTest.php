<?php

declare(strict_types = 1);

namespace BristolianTest\Service\UuidGenerator;

use Bristolian\Service\UuidGenerator\FixedUuidGenerator;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\UuidGenerator\FixedUuidGenerator::class, '__construct')]
#[CoversMethod(\Bristolian\Service\UuidGenerator\FixedUuidGenerator::class, 'generate')]

class FixedUuidGeneratorTest extends BaseTestCase
{
    public function test_generate_returns_configured_uuid(): void
    {
        $uuid = 'aaaaaaaa-bbbb-7ccc-8ddd-eeeeeeeeeeee';
        $generator = new FixedUuidGenerator($uuid);

        $this->assertSame($uuid, $generator->generate());
        $this->assertSame($uuid, $generator->generate());
    }

    public function test_generate_returns_default_uuid_when_none_configured(): void
    {
        $generator = new FixedUuidGenerator();

        $this->assertSame('00000000-0000-0000-0000-000000000001', $generator->generate());
    }
}
