<?php

declare(strict_types=1);

namespace Bristolian\Service\DailyProcessorSchedule;

/**
 * Production schedule: noon–3pm window; cooldown uses wall clock.
 */
final class StandardDailyProcessorSchedule implements DailyProcessorSchedule
{
    public function isTimeToRunDailySystemInfo(): bool
    {
        $now = new \DateTime();

        $start = (clone $now)->setTime(12, 0);
        $end = (clone $now)->setTime(15, 0);

        return $now >= $start && $now < $end;
    }

    public function isOverXHoursAgo(int $hours, \DateTimeInterface $datetime): bool
    {
        $now = new \DateTimeImmutable();
        $threshold = $now->sub(new \DateInterval(sprintf('PT%dH', $hours)));

        return $datetime < $threshold;
    }
}
