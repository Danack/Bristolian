<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck;
use Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use function Safe\ini_get;
use function Safe\ini_set;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::class, '__construct')]
#[CoversMethod(\Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::class, 'checkMemoryUsage')]

class ProdMemoryWarningCheckTest extends BaseTestCase
{
    public function test_checkMemoryUsage_returns_percentage(): void
    {
        $previous_memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', "1000M");

//        if (ini_get('memory_limit') === '-1') {
//            $this->markTestSkipped('No memory limit set, cannot test getPercentMemoryUsed');
//        }

        try {
            $notifier = new NullTooMuchMemoryNotifier();
            $check = new ProdMemoryWarningCheck($notifier);
            $request = new ServerRequest();

            $percent = $check->checkMemoryUsage($request);

            $this->assertGreaterThanOrEqual(0, $percent);
            $this->assertLessThanOrEqual(100, $percent);
        } finally {
            ini_set('memory_limit', $previous_memory_limit);
        }
    }
}
