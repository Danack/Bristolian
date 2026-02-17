<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class DevEnvironmentMemoryWarningTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning::checkMemoryUsage
     */
    public function test_checkMemoryUsage_returns_percentage_when_under_threshold(): void
    {
        if (ini_get('memory_limit') === '-1') {
            $this->markTestSkipped('No memory limit set, cannot test getPercentMemoryUsed');
        }

        $check = new DevEnvironmentMemoryWarning();
        $request = new ServerRequest();

        $percent = $check->checkMemoryUsage($request);

        $this->assertIsInt($percent);
        $this->assertGreaterThanOrEqual(0, $percent);
        $this->assertLessThanOrEqual(100, $percent);
    }
}
