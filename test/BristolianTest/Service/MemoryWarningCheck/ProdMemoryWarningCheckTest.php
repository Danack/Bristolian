<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck;
use Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class ProdMemoryWarningCheckTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::__construct
     * @covers \Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::checkMemoryUsage
     */
    public function test_checkMemoryUsage_returns_percentage(): void
    {
        if (ini_get('memory_limit') === '-1') {
            $this->markTestSkipped('No memory limit set, cannot test getPercentMemoryUsed');
        }

        $notifier = new NullTooMuchMemoryNotifier();
        $check = new ProdMemoryWarningCheck($notifier);
        $request = new ServerRequest();

        $percent = $check->checkMemoryUsage($request);

        $this->assertGreaterThanOrEqual(0, $percent);
        $this->assertLessThanOrEqual(100, $percent);
    }
}
