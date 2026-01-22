<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\SourceLinkRepo;

use Bristolian\Repo\SourceLinkRepo\SourceLinkRepo;
use Bristolian\Repo\SourceLinkRepo\PdoSourceLinkRepo;

/**
 * @group db
 */
class PdoSourceLinkRepoFixture extends SourceLinkRepoFixture
{
    public function getTestInstance(): SourceLinkRepo
    {
        return $this->injector->make(PdoSourceLinkRepo::class);
    }

    protected function getTestUserId(): string
    {
        $adminUser = $this->createTestAdminUser();
        return $adminUser->getUserId();
    }
}
