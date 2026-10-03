<?php

declare(strict_types=1);

namespace BristolianTest\Functions;

use Bristolian\Config\Config;
use Bristolian\Service\DailyProcessorSchedule\LocalDevBccTroExecutionCheck;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('createBccTroExecutionCheck')]
class CreateBccTroExecutionCheckTest extends BaseTestCase
{
    public function test_returns_local_dev_check_in_non_production(): void
    {
        $config = $this->injector->make(Config::class);
        $check = createBccTroExecutionCheck($config);

        $this->assertInstanceOf(LocalDevBccTroExecutionCheck::class, $check);
    }

    public function test_throws_in_production(): void
    {
        $config = new class extends Config {
            public function isProductionEnv(): bool
            {
                return true;
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("haven't written production version yet.");
        createBccTroExecutionCheck($config);
    }
}
