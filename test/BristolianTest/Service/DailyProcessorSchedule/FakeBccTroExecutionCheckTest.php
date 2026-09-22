<?php

declare(strict_types=1);

namespace BristolianTest\Service\DailyProcessorSchedule;

use Bristolian\Service\DailyProcessorSchedule\FakeBccTroExecutionCheck;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\FakeBccTroExecutionCheck::class, '__construct')]
#[CoversMethod(\Bristolian\Service\DailyProcessorSchedule\FakeBccTroExecutionCheck::class, 'shouldRun')]

class FakeBccTroExecutionCheckTest extends BaseTestCase
{
    public function test_shouldRun_returns_configured_value(): void
    {
        $shouldRun = new FakeBccTroExecutionCheck(true);
        $this->assertTrue($shouldRun->shouldRun(null));
        $this->assertTrue($shouldRun->shouldRun(new DateTimeImmutable()));

        $shouldSkip = new FakeBccTroExecutionCheck(false);
        $this->assertFalse($shouldSkip->shouldRun(null));
        $this->assertFalse($shouldSkip->shouldRun(new DateTimeImmutable()));
    }
}
