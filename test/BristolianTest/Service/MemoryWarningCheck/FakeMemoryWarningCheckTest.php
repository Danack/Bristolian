<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class FakeMemoryWarningCheckTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck::__construct
     * @covers \Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck::checkMemoryUsage
     */
    public function test_checkMemoryUsage_returns_configured_percentage(): void
    {
        $check = new FakeMemoryWarningCheck(85);
        $request = new ServerRequest();
        $this->assertSame(85, $check->checkMemoryUsage($request));
    }

    /**
     * @covers \Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck::checkMemoryUsage
     */
    public function test_checkMemoryUsage_returns_zero_when_constructed_with_zero(): void
    {
        $check = new FakeMemoryWarningCheck(0);
        $request = new ServerRequest();
        $this->assertSame(0, $check->checkMemoryUsage($request));
    }
}
