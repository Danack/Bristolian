<?php

namespace BristolianTest\Middleware;

use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\BristolianException;
use Bristolian\Config\HardCodedAssetLinkConfig;
use Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware;
use Bristolian\Middleware\MiddlewareException;
use Bristolian\SiteHtml\PageResponseGenerator;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * @covers \Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware
 */
class ExceptionToErrorPageResponseMiddlewareTest extends BaseTestCase
{
    public function testWorks_no_exception()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, $sha = 'abcdefg');
        $linkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $pageResponseGenerator = new PageResponseGenerator($responseFactory, $linkEmitter);

        $middleware = new ExceptionToErrorPageResponseMiddleware(
            $pageResponseGenerator,
            []
        );
        $request = new ServerRequest();

        $foo = new class() implements RequestHandler {
            public function __construct()
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $responseFactory = new ResponseFactory();
                return $responseFactory->createResponse(505);
            }
        };

        $response = $middleware->process($request, $foo);
        $this->assertSame(505, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testWorks_exception_handled_correctly()
    {
        $html = "<div>This is an error page. %s</div>";

        $exception_handler = function (
            BristolianException $be,
            ServerRequestInterface $request
        ) use ($html) {

            $html_rendered = sprintf($html, $be->getMessage());

            return [$html_rendered, 505];
        };

        $handlers = [
            BristolianException::class => $exception_handler
        ];

        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, $sha = 'abcdefg');
        $linkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $pageResponseGenerator = new PageResponseGenerator($responseFactory, $linkEmitter);
        $middleware = new ExceptionToErrorPageResponseMiddleware($pageResponseGenerator, $handlers);

        $message = "something went wrong";

        $request = new ServerRequest();

        $foo = new class($message) implements RequestHandler {
            public function __construct(private string $message)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new BristolianException($this->message);
            }
        };

        // Call the code
        $result = $middleware->process($request, $foo);

        // Assertions
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $result->getBody()->rewind();
        $contents = $result->getBody()->getContents();

        $this->assertSame(505, $result->getStatusCode());
        $this->assertStringContainsString(
            $message,
            $contents
        );
        $this->assertStringContainsString(
            "<script src='/js/app.bundle.js?version=abcdefg'></script>",
            $contents
        );
    }

    public function testWorks_exception_no_handlers()
    {
        $message = "This exception was not handled at all.";

        $responseFactory = new ResponseFactory();
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, $sha = 'abcdefg');
        $linkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $pageResponseGenerator = new PageResponseGenerator($responseFactory, $linkEmitter);

        $middleware = new ExceptionToErrorPageResponseMiddleware($pageResponseGenerator, []);
        $request = new ServerRequest();

        $foo = new class($message) implements RequestHandler {
            public function __construct(private string $message)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new BristolianException($this->message);
            }
        };

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage($message);
        $middleware->process($request, $foo);
    }
}
