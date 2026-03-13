<?php

declare(strict_types=1);

namespace Bristolian\Service\DailyProcessorSchedule;

/**
 * Test double: control window and cooldown without wall clock.
 */
final class FakeDailyProcessorSchedule implements DailyProcessorSchedule
{
    public bool $isWithinDailyWindow = true;

    /** When true, isOverXHoursAgo returns true for any datetime (treat last run as old enough). */
    public bool $lastRunIsOverCooldownHoursAgo = true;

    public function isTimeToRunDailySystemInfo(): bool
    {
        return $this->isWithinDailyWindow;
    }

    public function isOverXHoursAgo(int $hours, \DateTimeInterface $datetime): bool
    {
        return $this->lastRunIsOverCooldownHoursAgo;
    }
}
