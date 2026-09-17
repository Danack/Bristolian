<?php

declare(strict_types=1);

namespace BristolianTest\Service\DailyProcessorSchedule;

use Bristolian\Service\DailyProcessorSchedule\FakeDailyProcessorSchedule;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\FakeDailyProcessorSchedule::class, 'isOverXHoursAgo')]
#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\FakeDailyProcessorSchedule::class, 'isTimeToRunDailySystemInfo')]

class FakeDailyProcessorScheduleTest extends BaseTestCase
{
    public function test_isTimeToRunDailySystemInfo_returns_configured_value(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $this->assertTrue($schedule->isTimeToRunDailySystemInfo());

        $schedule->isWithinDailyWindow = false;
        $this->assertFalse($schedule->isTimeToRunDailySystemInfo());
    }

    public function test_isOverXHoursAgo_returns_configured_value(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $this->assertTrue($schedule->isOverXHoursAgo(24, new DateTimeImmutable()));

        $schedule->lastRunIsOverCooldownHoursAgo = false;
        $this->assertFalse($schedule->isOverXHoursAgo(24, new DateTimeImmutable()));
    }
}
