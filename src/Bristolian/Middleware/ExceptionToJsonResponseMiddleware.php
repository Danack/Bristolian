<?php

declare(strict_types=1);

namespace Bristolian\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\SiteHtml\PageResponseGenerator;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;

class ExceptionToJsonResponseMiddleware implements MiddlewareInterface
{
    /**
     *
     * @var array[{0:class-string, 1:callable}]
     * Convert particular exceptions to responses
     *
     * Callable should have the signature:
     *
     * function (
     *   SomeException $mappedException,
     *   Request $request,
     * )
     *
     * Where SomeException and the class-string should be the same.
     *
     */
//    private array $exceptionToResponseHandlerList;

    /**
     *
     * @param $exceptionToResponseHandlerList
     * @param $stubResponseToPSR7ResponseHandlerList
     */
    public function __construct(
        private ResponseFactory $responseFactory,
        private $exceptionToResponseHandlerList,
    ) {
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(
        Request $request,
        RequestHandler $handler
    ): Response {
        try {
            $response = $handler->handle($request);

            return $response;
        } catch (\Throwable $e) {
            $response = $this->convertExceptionToResponse($e, $request);

            if ($response !== null) {
                return $response;
            }

            // No exception handler for this exception type, so rethrow the
            // exception to allow it to propagate up the stack.
            throw $e;
        }
    }

    private function convertExceptionToResponse(\Throwable $e, Request $request)
    {
        // Find if there is an exception handler for this type of exception
        foreach ($this->exceptionToResponseHandlerList as $type => $exceptionCallable) {
            if ($e instanceof $type) {
                [$exceptionArray, $statusCode] = $exceptionCallable($e, $request);

                return $this->createJsonWithStatusCode(
                    $exceptionArray,
                    $statusCode
                );
            }
        }

        return null;
    }

    private function createJsonWithStatusCode(
        array $exceptionArray,
        int $statusCode
    ): Response {
        $response = $this->responseFactory->createResponse();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode_safe($exceptionArray));
        $response = $response->withStatus($statusCode);

        return $response;
    }
}
