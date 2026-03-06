<?php

namespace BristolianTest\Repo\Link;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Bristolian\Repo\LinkRepo\PdoLinkRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\FixedUuidGenerator;
use BristolianTest\Repo\TestPlaceholders;
use Ramsey\Uuid\Uuid;

/**
 * @group db
 * @coversNothing
 */
class PdoLinkRepoTest extends LinkRepoFixture
{
    use TestPlaceholders;

    public function getTestInstance(): LinkRepo
    {
        return $this->injector->make(PdoLinkRepo::class);
    }

    /**
     * Duplicate id triggers constraint violation (23000); repo throws UserConstraintFailedException.
     *
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo::store_link
     */
    public function test_store_link_throws_UserConstraintFailedException_on_duplicate_id(): void
    {
        $fixedUuid = Uuid::uuid7()->toString();
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $uuidGenerator = new FixedUuidGenerator($fixedUuid);
        $repo = new PdoLinkRepo($pdoSimple, $uuidGenerator);

        $testUser = $this->createTestAdminUser();

        $repo->store_link($testUser->getUserId(), 'https://example.com/first');

        $this->expectException(UserConstraintFailedException::class);
        $repo->store_link($testUser->getUserId(), 'https://example.com/second');
    }

    /**
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo
     */
    public function test_createEntry(): void
    {
        $pdoLinkRepo = $this->injector->make(PdoLinkRepo::class);

        $url = $this->getTestLink();
        $testUser = $this->createTestAdminUser();

        $link_id = $pdoLinkRepo->store_link(
            $testUser->getUserId(),
            $url
        );
        $this->assertNotEmpty($link_id);
    }
}
