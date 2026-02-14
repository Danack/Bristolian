<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeChatMessageRepoTest extends ChatMessageRepoFixture
{
    public function getTestInstance(): ChatMessageRepo
    {
        return new FakeChatMessageRepo();
    }
}
