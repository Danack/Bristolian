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
     * @param ResponseFactory $responseFactory
     * @param array{0:class-string, 1:callable} $exceptionToResponseHandlerList
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
    public function __construct(
        private ResponseFactory $responseFactory,
        private array $exceptionToResponseHandlerList,
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

    private function convertExceptionToResponse(\Throwable $e, Request $request): Response|null
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

    /**
     * @param mixed[] $exceptionArray
     * @param int $statusCode
     * @return Response
     * @throws \Exception
     */
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
