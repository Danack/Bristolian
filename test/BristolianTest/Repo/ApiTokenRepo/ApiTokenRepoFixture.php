<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\Model\Types\ApiToken;
use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for ApiTokenRepo implementations.
 *
 * @internal
 * @coversNothing
 */
abstract class ApiTokenRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the ApiTokenRepo implementation.
     *
     * @return ApiTokenRepo
     */
    abstract public function getTestInstance(): ApiTokenRepo;


    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::__construct
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::createToken
     */
    public function test_createToken(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' .uniqid();
        $token = 'token-value-' . random_int(1000, 9999);

        $apiToken = $repo->createToken($name, $token);

        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertSame($name, $apiToken->name);
        $this->assertSame($token, $apiToken->token);
        $this->assertFalse($apiToken->is_revoked);
    }


    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::getByToken
     */
    public function test_getByToken_returns_null_for_nonexistent_token(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByToken('nonexistent-token');
        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::createToken
     */
    public function test_getByToken_returns_token_after_creation(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' .uniqid();
        $token = 'token-value-' . random_int(1000, 9999);

        $createdToken = $repo->createToken($name, $token);

        $foundToken = $repo->getByToken($token);
        $this->assertNotNull($foundToken);
        $this->assertInstanceOf(ApiToken::class, $foundToken);
        $this->assertSame($createdToken->id, $foundToken->id);
        $this->assertSame($name, $foundToken->name);
        $this->assertSame($token, $foundToken->token);
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::revokeToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::revokeToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::revokeToken
     */
    public function test_getByToken_returns_null_for_revoked_token(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' .uniqid();
        $token = 'token-value-' . random_int(1000, 9999);

        $createdToken = $repo->createToken($name, $token);
        $repo->revokeToken($createdToken->id);

        $foundToken = $repo->getByToken($token);
        $this->assertNull($foundToken);
    }


    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::revokeToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::revokeToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::getByToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::revokeToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::getByToken
     */
    public function test_revokeToken(): void
    {
        $repo = $this->getTestInstance();

        $name = 'test-token-' . time() . '_' .uniqid();
        $token = 'token-value-' . random_int(1000, 9999);

        $createdToken = $repo->createToken($name, $token);

        // Verify token exists before revocation
        $foundBefore = $repo->getByToken($token);
        $this->assertNotNull($foundBefore);

        // Revoke the token
        $repo->revokeToken($createdToken->id);

        // Verify token is not found after revocation
        $foundAfter = $repo->getByToken($token);
        $this->assertNull($foundAfter);
    }
}
