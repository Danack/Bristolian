<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use PHPUnit\Framework\Attributes\CoversMethod;
use Bristolian\Model\Types\ApiToken;
use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for ApiTokenRepo implementations.
 *
 * @internal
 */

#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::class, 'createToken')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::class, 'getByToken')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::class, 'revokeToken')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class, 'createToken')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class, 'getByToken')]
#[CoversMethod(\Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class, 'revokeToken')]

abstract class ApiTokenRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the ApiTokenRepo implementation.
     *
     * @return ApiTokenRepo
     */
    abstract public function getTestInstance(): ApiTokenRepo;

    public function test_createToken(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' . uniqid();

        $apiToken = $repo->createToken($name);

        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertSame($name, $apiToken->name);
        $this->assertNotEmpty($apiToken->token);
        $this->assertFalse($apiToken->is_revoked);
    }

    public function test_getByToken_returns_null_for_nonexistent_token(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByToken('nonexistent-token');
        $this->assertNull($result);
    }

    public function test_getByToken_returns_token_after_creation(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' . uniqid();

        $createdToken = $repo->createToken($name);

        $foundToken = $repo->getByToken($createdToken->token);
        $this->assertNotNull($foundToken);
        $this->assertInstanceOf(ApiToken::class, $foundToken);
        $this->assertSame($createdToken->id, $foundToken->id);
        $this->assertSame($name, $foundToken->name);
        $this->assertSame($createdToken->token, $foundToken->token);
    }

    public function test_getByToken_returns_null_for_revoked_token(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' . uniqid();

        $createdToken = $repo->createToken($name);
        $repo->revokeToken($createdToken->id);

        $foundToken = $repo->getByToken($createdToken->token);
        $this->assertNull($foundToken);
    }

    public function test_revokeToken(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' . uniqid();

        $createdToken = $repo->createToken($name);

        // Verify token exists before revocation
        $foundBefore = $repo->getByToken($createdToken->token);
        $this->assertNotNull($foundBefore);

        // Revoke the token
        $repo->revokeToken($createdToken->id);

        // Verify token is not found after revocation
        $foundAfter = $repo->getByToken($createdToken->token);
        $this->assertNull($foundAfter);
    }
}
