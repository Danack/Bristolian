<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Model\Chat\UserChatMessage;
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
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::storeChatMessageForUser
     */
    public function test_fake_storeChatMessageForUser_stores_and_returns_message(): void
    {
        $repo = new FakeChatMessageRepo();
        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Stored',
            'room_id' => 'room-1',
        ]));

        $message = $repo->storeChatMessageForUser('user-1', $param);
        $this->assertInstanceOf(UserChatMessage::class, $message);
        $this->assertSame('user-1', $message->user_id);
        $this->assertSame('room-1', $message->room_id);
        $this->assertSame('Stored', $message->text);
        $this->assertGreaterThan(0, $message->id);
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::getMessagesForRoom
     */
    public function test_fake_getMessagesForRoom_returns_only_room_messages_sorted_newest_first(): void
    {
        $repo = new FakeChatMessageRepo();
        $room_id = 'room-a';
        $other_room = 'room-b';

        $repo->storeChatMessageForUser('user-1', ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'First', 'room_id' => $room_id,
        ])));
        $repo->storeChatMessageForUser('user-1', ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Second', 'room_id' => $room_id,
        ])));
        $repo->storeChatMessageForUser('user-1', ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Other', 'room_id' => $other_room,
        ])));

        $messages = $repo->getMessagesForRoom($room_id);
        $this->assertCount(2, $messages);
        $this->assertSame($room_id, $messages[0]->room_id);
        $this->assertSame($room_id, $messages[1]->room_id);
        $this->assertGreaterThanOrEqual($messages[1]->id, $messages[0]->id);
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
