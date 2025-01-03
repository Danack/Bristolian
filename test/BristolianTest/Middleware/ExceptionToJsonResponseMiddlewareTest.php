<?php

namespace BristolianTest\Middleware;

use Bristolian\Exception\BristolianException;
use Bristolian\Middleware\ExceptionToJsonResponseMiddleware;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Bristolian\Middleware\MiddlewareException;

/**
 * @covers \Bristolian\Middleware\ExceptionToJsonResponseMiddleware
 */
class ExceptionToJsonResponseMiddlewareTest extends BaseTestCase
{
    public function testWorks_no_exception()
    {
        $responseFactory = new ResponseFactory();
        $middleware = new ExceptionToJsonResponseMiddleware($responseFactory, []);
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
        $message = "This exception was handled and converted into a response.";

        $exception_handler = function (
            BristolianException $be,
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use ($message) {

            $data = [
                'status' => 'fail',
                'message' => $message
            ];
            return fillJsonResponseData($response, $data, 400);
        };

        $handlers = [
            BristolianException::class => $exception_handler
        ];

        $responseFactory = new ResponseFactory();
        $middleware = new ExceptionToJsonResponseMiddleware($responseFactory, $handlers);
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

        $end_result = json_decode($contents, true);

        $data = [
            'status' => 'fail',
            'message' => $message,
            'status_code' => 400
        ];

        $this->assertSame($data, $end_result);
    }

    public function testWorks_exception_no_handlers()
    {
        $message = "This exception was not handled at all.";

        $responseFactory = new ResponseFactory();
        $middleware = new ExceptionToJsonResponseMiddleware($responseFactory, []);
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




    /**
     * @return void
     */
    public function testWorks_exception_handler_fails_to_return_a_response_object()
    {
        $message = "This isn't going to be handled properly.";

        $exception_handler = function (
            BristolianException $be,
            ServerRequestInterface $request,
            ResponseInterface $response
        ) {
            return new \StdClass;
        };

        $handlers = [
            BristolianException::class => $exception_handler
        ];

        $responseFactory = new ResponseFactory();
        $middleware = new ExceptionToJsonResponseMiddleware($responseFactory, $handlers);
        $request = new ServerRequest();

        $request_handler = new class($message) implements RequestHandler {
            public function __construct(private string $message)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new BristolianException($this->message);
            }
        };

        $this->expectExceptionMessageMatchesTemplateString(
            MiddlewareException::ERROR_HANDLER_FAILED_TO_RETURN_RESPONSE
        );

        // Call the code
        $middleware->process($request, $request_handler);
    }
}
