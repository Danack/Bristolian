<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use VarMap\ArrayVarMap;

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

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::getMessagesForRoom
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::storeChatMessageForUser
     */
    public function test_getMessagesForRoom_returns_messages_for_room_sorted_newest_first(): void
    {
        $repo = new FakeChatMessageRepo();
        $user_id = $this->getTestUserId();
        $room_id = $this->getTestRoomId();
        $other_room = $this->getOtherRoomId();

        $param1 = ChatMessageParam::createFromVarMap(new ArrayVarMap(['text' => 'First', 'room_id' => $room_id]));
        $param2 = ChatMessageParam::createFromVarMap(new ArrayVarMap(['text' => 'Second', 'room_id' => $room_id]));
        $param3 = ChatMessageParam::createFromVarMap(new ArrayVarMap(['text' => 'Other room', 'room_id' => $other_room]));

        $repo->storeChatMessageForUser($user_id, $param1);
        $repo->storeChatMessageForUser($user_id, $param2);
        $repo->storeChatMessageForUser($user_id, $param3);

        $messages = $repo->getMessagesForRoom($room_id);
        $this->assertCount(2, $messages);
        $this->assertSame($room_id, $messages[0]->room_id);
        $this->assertSame($room_id, $messages[1]->room_id);
        $this->assertGreaterThanOrEqual($messages[1]->id, $messages[0]->id);
    }
}
