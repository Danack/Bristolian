<?php

namespace BristolianTest\Middleware;

use BristolianTest\BaseTestCase;
use Bristolian\Middleware\MemoryCheckMiddleware;
use Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class MemoryCheckMiddlewareTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Middleware\MemoryCheckMiddleware
     */
    public function testWorks()
    {
        $memoryWarningCheck = new FakeMemoryWarningCheck(50);

        $middleware = new MemoryCheckMiddleware($memoryWarningCheck);

        $request = new ServerRequest();
        $requestHandler = new FakeRequestHandler();

        $response = $middleware($request, $requestHandler);
        $this->assertTrue($response->hasHeader('X-Debug-Memory'));

        $headers = $response->getHeader('X-Debug-Memory');
        $this->assertCount(1, $headers);

        $this->assertSame("50%", $headers[0]);
    }
}
