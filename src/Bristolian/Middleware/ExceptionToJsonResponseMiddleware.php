<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Bristolian\Exception\BristolianException;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ExceptionToJsonResponseMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactory $responseFactory
     * @param array<class-string, callable> $exceptionToResponseHandlerList
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

    /**
     * @throws BristolianException
     */
    private function convertExceptionToResponse(\Throwable $e, Request $request): Response|null
    {
        // Find if there is an exception handler for this type of exception
        foreach ($this->exceptionToResponseHandlerList as $type => $exceptionCallable) {
            if ($e instanceof $type) {
                $response = $this->responseFactory->createResponse();
                $response = $response->withHeader('Content-Type', 'application/json');
                $response = $exceptionCallable($e, $request, $response);
                if (!($response instanceof ResponseInterface)) {
                    throw MiddlewareException::errorHandlerFailedToReturnResponse(
                        $e,
                        $response
                    );
                }

                return $response;
            }
        }

        return null;
    }
}
