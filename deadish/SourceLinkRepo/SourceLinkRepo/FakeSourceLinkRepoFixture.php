<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\SourceLinkRepo;

use Bristolian\Repo\SourceLinkRepo\FakeSourceLinkRepo;
use Bristolian\Repo\SourceLinkRepo\SourceLinkRepo;

/**
 * @group standard_repo
 */
class FakeSourceLinkRepoFixture extends SourceLinkRepoFixture
{
    public function getTestInstance(): SourceLinkRepo
    {
        return new FakeSourceLinkRepo();
    }
}
