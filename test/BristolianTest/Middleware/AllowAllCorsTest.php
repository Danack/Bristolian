<?php

namespace BristolianTest\Middleware;


use BristolianTest\BaseTestCase;
use Bristolian\Middleware\AllowAllCors;
use Laminas\Diactoros\ServerRequest;


/**
 * @coversNothing
 */
class AllowAllCorsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Middleware\AllowAllCors
     */
    public function testWorks()
    {
        $request = new ServerRequest();
        $requestHandler = new FakeRequestHandler();

        $middleware = new AllowAllCors();

        $response = $middleware($request, $requestHandler);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Headers'));
    }
}