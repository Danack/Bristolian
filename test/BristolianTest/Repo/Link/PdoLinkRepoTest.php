<?php

namespace BristolianTest\Repo\Link;

use Bristolian\Repo\LinkRepo\PdoLinkRepo;
use Bristolian\Repo\LinkRepo\LinkRepo;
use BristolianTest\Repo\TestPlaceholders;

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
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo
     */
    public function test_createEntry(): void
    {
        $pdoLinkRepo = $this->make(PdoLinkRepo::class);

        $url = $this->getTestLink();
        $testUser = $this->createTestAdminUser();

        $link_id = $pdoLinkRepo->store_link(
            $testUser->getUserId(),
            $url
        );
    }
}
