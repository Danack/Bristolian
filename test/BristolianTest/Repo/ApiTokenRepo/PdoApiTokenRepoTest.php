<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ApiTokenRepo\ApiTokenCreateFailedException;
use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo;
use Bristolian\Service\SecureTokenGenerator\FixedSecureTokenGenerator;

/**
 * @group db
 * @coversNothing
 */
class PdoApiTokenRepoTest extends ApiTokenRepoFixture
{
    /**
     * @return ApiTokenRepo
     */
    public function getTestInstance(): ApiTokenRepo
    {
        return $this->injector->make(PdoApiTokenRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::createToken
     * @covers \Bristolian\Repo\ApiTokenRepo\ApiTokenCreateFailedException::afterMaxRetries
     */
    public function test_createToken_throws_after_max_retries_when_token_collides(): void
    {
        $repo = $this->getTestInstance();
        $name = 'first-' . create_test_uniqid();
        $created = $repo->createToken($name);

        $pdoSimple = $this->injector->make(PdoSimple::class);
        $generator = new FixedSecureTokenGenerator($created->token);
        $repoWithCollidingGenerator = new PdoApiTokenRepo($pdoSimple, $generator);

        $this->expectException(ApiTokenCreateFailedException::class);
        $this->expectExceptionMessage('Failed to create unique API token after 5 attempts');

        $repoWithCollidingGenerator->createToken('second-' . create_test_uniqid());
    }

    /**
     * @covers \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::getByToken
     */
    public function test_getByToken_returns_token_with_all_fields_mapped(): void
    {
        $repo = $this->getTestInstance();
        $name = 'mapped-' . create_test_uniqid();
        $created = $repo->createToken($name);

        $found = $repo->getByToken($created->token);

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
        $this->assertSame($created->token, $found->token);
        $this->assertSame($name, $found->name);
        $this->assertInstanceOf(\DateTimeInterface::class, $found->created_at);
        $this->assertSame($created->created_at->format('Y-m-d H:i:s'), $found->created_at->format('Y-m-d H:i:s'));
        $this->assertFalse($found->is_revoked);
        $this->assertNull($found->revoked_at);
    }
}
