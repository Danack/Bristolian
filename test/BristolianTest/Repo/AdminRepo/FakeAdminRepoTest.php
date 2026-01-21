<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AdminRepo;

use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;

/**
 * @group standard_repo
 */
class FakeAdminRepoTest extends AdminRepoTest
{
    /**
     * @return AdminRepo
     */
    public function getTestInstance(): AdminRepo
    {
        return new FakeAdminRepo([]);
    }
}
