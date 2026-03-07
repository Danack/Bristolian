<?php

declare(strict_types=1);

namespace BristolianTest\Middleware;

use Bristolian\Cache\RequestTableAccessRecorder;
use Bristolian\Middleware\CacheTagMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Bristolian\Middleware\CacheTagMiddleware
 */
class CacheTagMiddlewareTest extends TestCase
{
    private RequestTableAccessRecorder $recorder;

    public function setup(): void
    {
        $this->recorder = new RequestTableAccessRecorder();
    }

    private function createHandler(int $statusCode = 200): RequestHandlerInterface
    {
        return new class ($statusCode) implements RequestHandlerInterface {
            public function __construct(private int $statusCode)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new Response())->withStatus($this->statusCode);
            }
        };
    }

    public function testGetRequestWithReadTablesSetsCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users', 'rooms']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertTrue($response->hasHeader('X-Cache-Tags'));
        $tags = $response->getHeader('X-Cache-Tags')[0];
        $this->assertStringContainsString('table:users', $tags);
        $this->assertStringContainsString('table:rooms', $tags);
    }

    public function testHeadRequestWithReadTablesSetsHeader(): void
    {
        $this->recorder->recordTablesRead(['rooms']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'HEAD');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertTrue($response->hasHeader('X-Cache-Tags'));
        $this->assertSame('table:rooms', $response->getHeader('X-Cache-Tags')[0]);
    }

    public function testGetRequestWithNoReadTablesDoesNotSetHeader(): void
    {
        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testPostRequestDoesNotSetCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'POST');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testNon200ResponseDoesNotSetCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(404);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testRedirectResponseDoesNotSetCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(302);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testPutRequestDoesNotSetCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'PUT');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testDeleteRequestDoesNotSetCacheTagsHeader(): void
    {
        $this->recorder->recordTablesRead(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'DELETE');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testMiddlewareReturnsResponseFromHandler(): void
    {
        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(201);

        $response = $middleware($request, $handler);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testWriteTablesDoNotAffectCacheTagsHeader(): void
    {
        $this->recorder->recordTablesWritten(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertFalse($response->hasHeader('X-Cache-Tags'));
    }

    public function testBothReadAndWriteTablesOnlyReadsInHeader(): void
    {
        $this->recorder->recordTablesRead(['rooms']);
        $this->recorder->recordTablesWritten(['users']);

        $middleware = new CacheTagMiddleware($this->recorder);
        $request = new ServerRequest([], [], null, 'GET');
        $handler = $this->createHandler(200);

        $response = $middleware($request, $handler);

        $this->assertTrue($response->hasHeader('X-Cache-Tags'));
        $tags = $response->getHeader('X-Cache-Tags')[0];
        $this->assertSame('table:rooms', $tags);
        $this->assertStringNotContainsString('users', $tags);
    }
}
