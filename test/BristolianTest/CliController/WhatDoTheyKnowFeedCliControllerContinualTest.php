<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\WhatDoTheyKnowFeedCliController;
use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\FakeWhatDoTheyKnowRequestEventRepo;
use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\RoomMessageService\FakeRoomMessageService;
use Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson;
use PHPUnit\Framework\TestCase;

/**
 * continualExecuteCallable() writes to stdout; BaseTestCase teardown forbids any output, so these tests use TestCase.
 *
 * @coversNothing
 */
final class WhatDoTheyKnowFeedCliControllerContinualTest extends TestCase
{
    private function expectContinuallyExecuteCallableStdout(): void
    {
        $this->expectOutputString(
            "starting continuallyExecuteCallable \n"
            . "Reach maxRunTime - finished = true\n"
            . "Finishing continuallyExecuteCallable\n"
        );
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolContinual
     */
    public function test_syncRequestedFromBristolContinual_runs_sync_with_tight_timing_parameters(): void
    {
        $this->expectContinuallyExecuteCallableStdout();

        $fixturePath = dirname(__DIR__, 2) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($fixturePath);
        self::assertNotFalse($json);

        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($json);
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $controller->syncRequestedFromBristolContinual(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput,
            secondsBetweenPollRuns: 0,
            continualSleepSeconds: 0,
            continualMaxRunSeconds: 0
        );

        self::assertStringContainsString('Processed feed:', $cliOutput->getCapturedOutput());
        self::assertGreaterThan(0, count($repo->getInsertedRows()));
    }

    /**
     * @covers \Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolContinual
     */
    public function test_syncRequestedFromBristolContinual_catches_sync_throwable_and_writes_error(): void
    {
        $this->expectContinuallyExecuteCallableStdout();

        $fetcher = new WhatDoTheyKnowFeedFetcherThatThrows(new \RuntimeException('network failed'));
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $roomRepo = new FakeRoomRepo();
        $messages = new FakeRoomMessageService();
        $cliOutput = new CapturingCliOutput();
        $controller = new WhatDoTheyKnowFeedCliController();

        $controller->syncRequestedFromBristolContinual(
            $fetcher,
            $repo,
            $roomRepo,
            $messages,
            $cliOutput,
            secondsBetweenPollRuns: 0,
            continualSleepSeconds: 0,
            continualMaxRunSeconds: 0
        );

        self::assertStringContainsString('WhatDoTheyKnow sync error: network failed', $cliOutput->getCapturedOutput());
    }
}
