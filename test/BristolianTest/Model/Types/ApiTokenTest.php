<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\ApiToken;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ApiTokenTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\ApiToken
     */
    public function test_construct(): void
    {
        $id = 'token-id-123';
        $token = 'secret-token-abc';
        $name = 'Test Token';
        $createdAt = new \DateTimeImmutable();
        $revokedAt = new \DateTimeImmutable('2024-02-01');

        $apiToken = new ApiToken($id, $token, $name, $createdAt, true, $revokedAt);

        $this->assertSame($id, $apiToken->id);
        $this->assertSame($token, $apiToken->token);
        $this->assertSame($name, $apiToken->name);
        $this->assertSame($createdAt, $apiToken->created_at);
        $this->assertTrue($apiToken->is_revoked);
        $this->assertSame($revokedAt, $apiToken->revoked_at);
    }
}
