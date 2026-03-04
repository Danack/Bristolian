<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\Repo\ApiTokenRepo\ApiTokenCreateFailedException;
use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo;
use Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeApiTokenRepoTest extends ApiTokenRepoFixture
{
    /**
     * @return ApiTokenRepo
     */
    public function getTestInstance(): ApiTokenRepo
    {
        return new FakeApiTokenRepo([], new FixedSecureTokenGenerator());
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::__construct
     */
    public function test_constructor_with_initial_tokens(): void
    {
        $existingToken = new \Bristolian\Model\Types\ApiToken(
            id: 'existing-id',
            token: 'existing-token',
            name: 'Existing',
            created_at: new \DateTimeImmutable(),
            is_revoked: false,
            revoked_at: null
        );
        $repo = new FakeApiTokenRepo([$existingToken], new FixedSecureTokenGenerator());

        $found = $repo->getByToken('existing-token');
        $this->assertNotNull($found);
        $this->assertSame('existing-id', $found->id);
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     */
    public function test_createToken_returns_token_from_generator(): void
    {
        $expectedToken = 'deterministic-token-from-fake-generator';
        $generator = new FixedSecureTokenGenerator($expectedToken);
        $repo = new FakeApiTokenRepo([], $generator);

        $apiToken = $repo->createToken('my-token-name');

        $this->assertSame($expectedToken, $apiToken->token);
        $this->assertSame('my-token-name', $apiToken->name);
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenCreateFailedException::afterMaxRetries
     */
    public function test_createToken_throws_after_max_retries_when_all_collide(): void
    {
        $collidingToken = 'same-token-every-time';
        $generator = new FixedSecureTokenGenerator($collidingToken);
        $existingToken = new \Bristolian\Model\Types\ApiToken(
            id: 'id-1',
            token: $collidingToken,
            name: 'Existing',
            created_at: new \DateTimeImmutable(),
            is_revoked: false,
            revoked_at: null
        );
        $repo = new FakeApiTokenRepo([$existingToken], $generator);

        $this->expectException(ApiTokenCreateFailedException::class);
        $this->expectExceptionMessage('Failed to create unique API token after 5 attempts');

        $repo->createToken('another-name');
    }
}
