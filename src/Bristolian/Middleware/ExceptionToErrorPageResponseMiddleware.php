<?php

declare(strict_types=1);

namespace Bristolian\Middleware;

use Bristolian\SiteHtml\PageResponseGenerator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ExceptionToErrorPageResponseMiddleware implements MiddlewareInterface
{
    private PageResponseGenerator $pageResponseGenerator;

    /**
     *
     * @var array{0:class-string, 1:callable}
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
    private array $exceptionToResponseHandlerList;


    /**
     * @param PageResponseGenerator $pageResponseGenerator
     * @param mixed[] $exceptionToResponseHandlerList
     */
    public function __construct(
        PageResponseGenerator $pageResponseGenerator,
        array $exceptionToResponseHandlerList,
    ) {
        $this->pageResponseGenerator = $pageResponseGenerator;
        $this->exceptionToResponseHandlerList = $exceptionToResponseHandlerList;
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
                // TODO - this doesn't pass in the response, so the handler can't
                // add headers. Which is probably okay for HTML pages?
                [$exceptionHtml, $statusCode] = $exceptionCallable($e, $request);

                return $this->pageResponseGenerator->createPageWithStatusCode(
                    $exceptionHtml,
                    $statusCode
                );
            }
        }

        return null;
    }
}
