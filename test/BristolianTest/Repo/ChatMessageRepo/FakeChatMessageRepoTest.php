<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;

/**
 * @group standard_repo
 */
class FakeChatMessageRepoTest extends ChatMessageRepoTest
{
    public function getTestInstance(): ChatMessageRepo
    {
        return new FakeChatMessageRepo();
    }
}
