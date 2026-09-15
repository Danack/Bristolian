<?php

namespace BristolianTest\ApiController;

use BristolianTest\BaseTestCase;
use Bristolian\ApiController\HealthCheck;
use SlimDispatcher\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\ApiController\HealthCheck::class)]
class HealthCheckTest extends BaseTestCase
{
    public function testWorks()
    {
        $health_check = new HealthCheck();

        $response = $health_check->get();
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
