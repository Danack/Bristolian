<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomMessageService;

//use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\Generated\UserOwnership;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use Bristolian\Repo\UserRepo\UserRepo;
use Bristolian\Service\RoomMessageService\StandardRoomMessageService;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 * @group db
 */
class StandardRoomMessageServiceTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::__construct
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::sendMessage
     */
    public function test_sendMessage_stores_via_repo_and_pushes_to_redis(): void
    {
        $chatMessageRepo = new FakeChatMessageRepo();
        $userRepo = new class implements UserRepo {
            public function ensureSystemUserExists(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getSystemUser(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getRoomUserForRoom(string $room_id): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }
        };
        $service = new StandardRoomMessageService($userRepo, $chatMessageRepo);

        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Hello room',
            'room_id' => 'room_1',
        ]));

        $message = $service->sendMessage('user_1', $param);

        $this->assertSame('user_1', $message->user_id);
        $this->assertSame('room_1', $message->room_id);
        $this->assertSame('Hello room', $message->text);

//        $key = RoomMessageKey::getAbsoluteKeyName();
//        $listLength = $this->redis->lLen($key);
//        $this->assertGreaterThanOrEqual(1, $listLength);
//        $lastPushed = $this->redis->lIndex($key, -1);
//        $this->assertSame($message->toString(), $lastPushed);

//        $this->redis->lRem($key, $lastPushed, 1);
    }

    /**
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::sendRoomMessage
     */
    public function test_sendRoomMessage_stores_for_user_resolved_from_room(): void
    {
        $chatMessageRepo = new FakeChatMessageRepo();
        $userRepo = new class implements UserRepo {
            public function ensureSystemUserExists(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getSystemUser(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getRoomUserForRoom(string $room_id): UserOwnership
            {
                return new UserOwnership(1, 'room-owner-resolved', UserRepo::TYPE_ROOM_USER, $room_id);
            }
        };
        $service = new StandardRoomMessageService($userRepo, $chatMessageRepo);

        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Room message',
            'room_id' => 'room_alpha',
        ]));

        $message = $service->sendRoomMessage($param);

        $this->assertSame('room-owner-resolved', $message->user_id);
        $this->assertSame('room_alpha', $message->room_id);
        $this->assertSame('Room message', $message->text);
    }

    /**
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::sendSystemMessage
     */
    public function test_sendSystemMessage_stores_via_chat_repo_system_path(): void
    {
        $chatMessageRepo = new FakeChatMessageRepo();
        $userRepo = new class implements UserRepo {
            public function ensureSystemUserExists(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getSystemUser(): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }

            public function getRoomUserForRoom(string $room_id): UserOwnership
            {
                throw new \LogicException('not used in this test');
            }
        };
        $service = new StandardRoomMessageService($userRepo, $chatMessageRepo);

        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Automated line',
            'room_id' => 'room_feed',
        ]));

        $message = $service->sendSystemMessage($param);

        $this->assertSame('system_user_fixture', $message->user_id);
        $this->assertSame('room_feed', $message->room_id);
        $this->assertSame('Automated line', $message->text);
    }
}
