<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomMessageService;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use Bristolian\Service\RoomMessageService\StandardRoomMessageService;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 * @group db
 */
class StandardRoomMessageServiceTest extends BaseTestCase
{
    private \Redis $redis;

    public function setup(): void
    {
        parent::setup();
        $this->redis = $this->injector->make(\Redis::class);
    }

    /**
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::__construct
     * @covers \Bristolian\Service\RoomMessageService\StandardRoomMessageService::sendMessage
     */
    public function test_sendMessage_stores_via_repo_and_pushes_to_redis(): void
    {
        $chatMessageRepo = new FakeChatMessageRepo();
        $service = new StandardRoomMessageService($this->redis, $chatMessageRepo);

        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Hello room',
            'room_id' => 'room_1',
        ]));

        $message = $service->sendMessage('user_1', $param);

        $this->assertSame('user_1', $message->user_id);
        $this->assertSame('room_1', $message->room_id);
        $this->assertSame('Hello room', $message->text);

        $key = RoomMessageKey::getAbsoluteKeyName();
        $listLength = $this->redis->lLen($key);
        $this->assertGreaterThanOrEqual(1, $listLength);
        $lastPushed = $this->redis->lIndex($key, -1);
        $this->assertSame($message->toString(), $lastPushed);

        $this->redis->lRem($key, $lastPushed, 1);
    }
}
