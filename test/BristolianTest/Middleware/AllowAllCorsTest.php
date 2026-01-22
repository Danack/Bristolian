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

        $response = $middleware->process($request, $requestHandler);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Headers'));
    }

    /**
     * @covers \Bristolian\Middleware\AllowAllCors
     */
    public function testWorks_with_OPTIONS_request()
    {
        $request = new ServerRequest(method: 'OPTIONS');
        $requestHandler = new FakeRequestHandler();

        $middleware = new AllowAllCors();

        $response = $middleware->process($request, $requestHandler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertSame('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
        $this->assertSame('GET,POST,DELETE,PUT,OPTIONS,HEAD,PATCH', $response->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Headers'));
        $this->assertSame('*', $response->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertSame('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertSame('86400', $response->getHeaderLine('Access-Control-Max-Age'));
    }
}
