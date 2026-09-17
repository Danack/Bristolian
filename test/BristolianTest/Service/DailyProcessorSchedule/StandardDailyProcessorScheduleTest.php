<?php

declare(strict_types=1);

namespace BristolianTest\Service\DailyProcessorSchedule;

use Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule;
use BristolianTest\BaseTestCase;
use Safe\DateTime;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule::class, 'isOverXHoursAgo')]
#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule::class, 'isTimeToRunDailySystemInfo')]

class StandardDailyProcessorScheduleTest extends BaseTestCase
{
    public function test_isTimeToRunDailySystemInfo_returns_true_between_noon_and_3pm(): void
    {
        $schedule = new StandardDailyProcessorSchedule();
        $result = $schedule->isTimeToRunDailySystemInfo();
        $hour = (int) (new DateTime())->format('G');
        $expected = $hour >= 12 && $hour < 15;
        $this->assertSame($expected, $result);
    }

    public function test_isOverXHoursAgo_returns_true_when_datetime_is_older_than_threshold(): void
    {
        $schedule = new StandardDailyProcessorSchedule();
        $fiveHoursAgo = new DateTimeImmutable('-5 hours');
        $this->assertTrue($schedule->isOverXHoursAgo(4, $fiveHoursAgo));
    }

    public function test_isOverXHoursAgo_returns_false_when_datetime_is_within_threshold(): void
    {
        $schedule = new StandardDailyProcessorSchedule();
        $oneHourAgo = new DateTimeImmutable('-1 hour');
        $this->assertFalse($schedule->isOverXHoursAgo(4, $oneHourAgo));
    }
}
