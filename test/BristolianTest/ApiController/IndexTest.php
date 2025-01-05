<?php

namespace BristolianTest\ApiController;

use BristolianTest\BaseTestCase;
use Bristolian\ApiController\Index;
use SlimDispatcher\Response\JsonResponse;

/**
 * @covers \Bristolian\ApiController\Index
 */
class IndexTest extends BaseTestCase
{
    public function testWorks()
    {
        $health_check = new Index();
        $response = $health_check->getRouteList();
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
