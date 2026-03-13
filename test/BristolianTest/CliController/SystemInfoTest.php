<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\SystemInfo;
use Bristolian\Repo\EmailQueue\FakeEmailQueue;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\DailyProcessorSchedule\FakeDailyProcessorSchedule;
use Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class SystemInfoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\SystemInfo::__construct
     */
    public function test_construct(): void
    {
        $systemInfo = new SystemInfo(
            new FakeProcessorRunRecordRepo(),
            new FakeEmailQueue(),
            new FakeDailyProcessorSchedule(),
            new CapturingCliOutput()
        );
        $this->assertInstanceOf(SystemInfo::class, $systemInfo);
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo::runInternal
     */
    public function test_runInternal_writes_skip_when_not_in_daily_window(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = false;
        $cliOutput = new CapturingCliOutput();
        $systemInfo = new SystemInfo(
            new FakeProcessorRunRecordRepo(),
            new FakeEmailQueue(),
            $schedule,
            $cliOutput
        );
        $systemInfo->runInternal();
        $text = $cliOutput->getCapturedOutput();
        $this->assertStringContainsString('daily system info', $text);
        $this->assertStringContainsString('Skipping, not currently time', $text);
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo::runInternal
     */
    public function test_runInternal_writes_skip_when_last_run_within_cooldown(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = true;
        $schedule->lastRunIsOverCooldownHoursAgo = false;
        $runRecordRepo = new FakeProcessorRunRecordRepo();
        $runRecordRepo->startRun(ProcessType::daily_system_info);
        $cliOutput = new CapturingCliOutput();
        $systemInfo = new SystemInfo(
            $runRecordRepo,
            new FakeEmailQueue(),
            $schedule,
            $cliOutput
        );
        $systemInfo->runInternal();
        $this->assertStringContainsString('within the last 21 hours', $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo::runInternal
     */
    public function test_runInternal_queues_email_and_finishes_when_no_prior_run(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = true;
        $runRecordRepo = new FakeProcessorRunRecordRepo();
        $emailQueue = new FakeEmailQueue();
        $cliOutput = new CapturingCliOutput();
        $systemInfo = new SystemInfo(
            $runRecordRepo,
            $emailQueue,
            $schedule,
            $cliOutput
        );
        $systemInfo->runInternal();
        $text = $cliOutput->getCapturedOutput();
        $this->assertStringContainsString('Email generated, queueing', $text);
        $this->assertStringContainsString('Fin.', $text);
        $this->assertNotEmpty($emailQueue->getAllEmails());
        $records = $runRecordRepo->getRunRecords(ProcessType::daily_system_info);
        $this->assertNotEmpty($records);
        $this->assertSame(FakeProcessorRunRecordRepo::STATE_FINISHED, $records[0]->status);
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo::runInternal
     */
    public function test_runInternal_runs_again_when_schedule_says_cooldown_passed(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = true;
        $schedule->lastRunIsOverCooldownHoursAgo = true;
        $runRecordRepo = new FakeProcessorRunRecordRepo();
        $runRecordRepo->startRun(ProcessType::daily_system_info);
        $runRecordRepo->setRunFinished('1', '');
        $emailQueue = new FakeEmailQueue();
        $cliOutput = new CapturingCliOutput();
        $systemInfo = new SystemInfo(
            $runRecordRepo,
            $emailQueue,
            $schedule,
            $cliOutput
        );
        $systemInfo->runInternal();
        $this->assertStringContainsString('Fin.', $cliOutput->getCapturedOutput());
        $this->assertNotEmpty($emailQueue->getAllEmails());
    }

    /**
     * @covers \Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule::isOverXHoursAgo
     */
    public function test_schedule_isOverXHoursAgo_returns_true_when_datetime_is_older_than_x_hours(): void
    {
        $schedule = new StandardDailyProcessorSchedule();
        $twentyTwoHoursAgo = new \DateTimeImmutable('-22 hours');
        $this->assertTrue($schedule->isOverXHoursAgo(21, $twentyTwoHoursAgo));
    }

    /**
     * @covers \Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule::isOverXHoursAgo
     */
    public function test_schedule_isOverXHoursAgo_returns_false_when_datetime_is_less_than_x_hours_ago(): void
    {
        $schedule = new StandardDailyProcessorSchedule();
        $twentyHoursAgo = new \DateTimeImmutable('-20 hours');
        $this->assertFalse($schedule->isOverXHoursAgo(21, $twentyHoursAgo));
    }
}
