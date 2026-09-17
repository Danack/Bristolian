<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemoryWarningCheck;

use Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck::class, '__construct')]
#[CoversMethod(\Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck::class, 'checkMemoryUsage')]

class FakeMemoryWarningCheckTest extends BaseTestCase
{
    public function test_checkMemoryUsage_returns_configured_percentage(): void
    {
        $check = new FakeMemoryWarningCheck(85);
        $request = new ServerRequest();
        $this->assertSame(85, $check->checkMemoryUsage($request));
    }

    public function test_checkMemoryUsage_returns_zero_when_constructed_with_zero(): void
    {
        $check = new FakeMemoryWarningCheck(0);
        $request = new ServerRequest();
        $this->assertSame(0, $check->checkMemoryUsage($request));
    }
}
