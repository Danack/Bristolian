<?php

namespace BristolianTest\ApiController;

use BristolianTest\BaseTestCase;
use Bristolian\ApiController\HealthCheck;
use SlimDispatcher\Response\JsonResponse;

/**
 * @covers \Bristolian\ApiController\HealthCheck
 */
class HealthCheckTest extends BaseTestCase
{
    public function testWorks()
    {
        $health_check = new HealthCheck();

        $response = $health_check->get();
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
