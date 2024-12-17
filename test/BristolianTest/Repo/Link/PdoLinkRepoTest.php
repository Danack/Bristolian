<?php

namespace BristolianTest\Repo\Link;

use Bristolian\Repo\LinkRepo\PdoLinkRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class PdoLinkRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo
     */
    public function test_createEntry()
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
