<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\WhatDoTheyKnowFeedCliController;
use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\FakeWhatDoTheyKnowRequestEventRepo;
use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\RoomMessageService\FakeRoomMessageService;
use Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
final class WhatDoTheyKnowFeedCliControllerTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce
     */
    public function test_syncRequestedFromBristolOnce_inserts_and_messages_for_each_new_event(): void
    {
        $fixturePath = dirname(__DIR__, 2) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($fixturePath);
        self::assertNotFalse($json);

        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($json);
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('owner_user_id', 'FOI advice', 'FOI discussion');
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );

        self::assertCount(25, $repo->getInsertedRows());
        self::assertCount(25, $messages->getChatMessages());
        $firstMessage = $messages->getChatMessages()[0]->text;
        self::assertStringContainsString('https://www.whatdotheyknow.com/request/foi_request_supported_exempt_acc_65', $firstMessage);
        self::assertStringContainsString('FOI Request: Supported Exempt Accommodation', $firstMessage);
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce
     */
    public function test_syncRequestedFromBristolOnce_second_run_inserts_nothing_and_sends_no_messages(): void
    {
        $fixturePath = dirname(__DIR__, 2) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($fixturePath);
        self::assertNotFalse($json);

        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($json);
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('owner_user_id', 'FOI advice', 'FOI discussion');
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );
        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );

        self::assertCount(25, $repo->getInsertedRows());
        self::assertCount(25, $messages->getChatMessages());
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce
     */
    public function test_syncRequestedFromBristolOnce_without_room_still_stores_events_and_skips_messages(): void
    {
        $fixturePath = dirname(__DIR__, 2) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($fixturePath);
        self::assertNotFalse($json);

        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($json);
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );

        self::assertCount(25, $repo->getInsertedRows());
        self::assertCount(0, $messages->getChatMessages());
        self::assertStringContainsString("Failed to find room named 'FOI advice'", $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce
     */
    public function test_syncRequestedFromBristolOnce_throws_when_top_level_json_is_not_a_list(): void
    {
        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson('{"not":"a list"}');
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('WhatDoTheyKnow feed must be a JSON array.');

        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce
     */
    public function test_syncRequestedFromBristolOnce_throws_when_feed_item_is_not_object(): void
    {
        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson('[1]');
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feed item at index 0 must be an object.');

        $controller->syncRequestedFromBristolOnce(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput
        );
    }

}
