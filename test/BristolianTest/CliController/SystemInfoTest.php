<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\SystemInfo;
use BristolianTest\BaseTestCase;
use function Bristolian\CliController\isOverXHoursAgo;

/**
 * @coversNothing
 */
class SystemInfoTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        class_exists(SystemInfo::class); // load file containing isOverXHoursAgo
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo
     */
    public function test_isOverXHoursAgo_returns_true_when_datetime_is_older_than_x_hours(): void
    {
        $twentyTwoHoursAgo = new \DateTimeImmutable('-22 hours');
        $this->assertTrue(isOverXHoursAgo(21, $twentyTwoHoursAgo));
    }

    /**
     * @covers \Bristolian\CliController\SystemInfo
     */
    public function test_isOverXHoursAgo_returns_false_when_datetime_is_less_than_x_hours_ago(): void
    {
        $twentyHoursAgo = new \DateTimeImmutable('-20 hours');
        $this->assertFalse(isOverXHoursAgo(21, $twentyHoursAgo));
    }
}
