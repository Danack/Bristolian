<?php

declare(strict_types=1);

namespace BristolianTest\Service\DailyProcessorSchedule;

use Bristolian\Service\DailyProcessorSchedule\LocalDevBccTroExecutionCheck;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LocalDevBccTroExecutionCheck::class)]
class LocalDevBccTroExecutionCheckTest extends BaseTestCase
{
    public function test_shouldRun_returns_true_when_last_run_time_is_null(): void
    {
        $check = new LocalDevBccTroExecutionCheck();

        $this->assertTrue($check->shouldRun(null));
    }

    public function test_shouldRun_returns_true_on_first_call_even_with_recent_last_run(): void
    {
        $check = new LocalDevBccTroExecutionCheck();
        $recent = new DateTimeImmutable();

        $this->assertTrue($check->shouldRun($recent));
    }

    public function test_shouldRun_returns_false_on_second_call_when_last_run_is_recent(): void
    {
        $check = new LocalDevBccTroExecutionCheck();
        $recent = new DateTimeImmutable();

        $this->assertTrue($check->shouldRun($recent));
        $this->assertFalse($check->shouldRun($recent));
    }

    public function test_shouldRun_returns_true_on_second_call_when_last_run_is_over_an_hour_ago(): void
    {
        $check = new LocalDevBccTroExecutionCheck();
        $old = (new DateTimeImmutable())->sub(new \DateInterval('PT2H'));

        $this->assertTrue($check->shouldRun($old));
        $this->assertTrue($check->shouldRun($old));
    }
}
