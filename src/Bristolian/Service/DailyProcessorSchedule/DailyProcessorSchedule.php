<?php

declare(strict_types=1);

namespace Bristolian\Service\DailyProcessorSchedule;

/**
 * Time-window and cooldown checks for daily CLI processors (system info, BCC TRO, etc.).
 */
interface DailyProcessorSchedule
{
    /**
     * True when local time is between noon and 3pm (same semantics as former isTimeToRunDailySystemInfo).
     */
    public function isTimeToRunDailySystemInfo(): bool;

    /**
     * True when $datetime is strictly older than $hours hours before now.
     */
    public function isOverXHoursAgo(int $hours, \DateTimeInterface $datetime): bool;
}
