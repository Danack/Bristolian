<?php

namespace BristolianTest\Middleware;

use BristolianTest\BaseTestCase;
use Bristolian\Middleware\MemoryCheckMiddleware;
use Bristolian\Service\MemoryWarningCheck\FakeMemoryWarningCheck;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Middleware\MemoryCheckMiddleware::class)]

class MemoryCheckMiddlewareTest extends BaseTestCase
{
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
