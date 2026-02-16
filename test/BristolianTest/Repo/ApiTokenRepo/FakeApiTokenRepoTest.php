<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo;

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
        return new FakeApiTokenRepo([]);
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
        $repo = new FakeApiTokenRepo([$existingToken]);

        $found = $repo->getByToken('existing-token');
        $this->assertNotNull($found);
        $this->assertSame('existing-id', $found->id);
    }
}
