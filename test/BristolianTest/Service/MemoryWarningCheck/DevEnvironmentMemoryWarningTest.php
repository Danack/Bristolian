<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 * @group wip
 */
class DevEnvironmentMemoryWarningTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning::checkMemoryUsage
     */
    public function test_checkMemoryUsage_returns_percentage_when_under_threshold(): void
    {
        $previous_memory_limit = ini_get('memory_limit');

        ini_set('memory_limit', "1000M");

//        if (ini_get('memory_limit') === '-1') {
//            $this->markTestSkipped('No memory limit set, cannot test getPercentMemoryUsed');
//        }
        try {
            $check = new DevEnvironmentMemoryWarning();
            $request = new ServerRequest();

            $percent = $check->checkMemoryUsage($request);

            $this->assertGreaterThanOrEqual(0, $percent);
            $this->assertLessThanOrEqual(100, $percent);
        } finally {
            ini_set('memory_limit', $previous_memory_limit);
        }
    }
}
