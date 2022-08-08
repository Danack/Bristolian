<?php

declare(strict_types=1);

namespace Bristolian\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\SiteHtml\PageResponseGenerator;

class ExceptionToErrorPageResponseMiddleware implements MiddlewareInterface
{
    private PageResponseGenerator $pageResponseGenerator;

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
    private array $exceptionToResponseHandlerList;

//    /**
//     * @var array[{0:class-string, 1:callable}]
//     * Map custom results/responses to PSR7Responses
//     *
//     * These are called after the exception is converted to a stub response.
//     *
//     *
//     * Handler must have the signature
//     *
//     * $mapCallable(
//     *     $result,
//     *     Psr\Http\Message\ResponseInterface $request
//     * ): \Psr\Http\Message\ResponseInterface
//     *
//     */
//    private array $stubResponseToPSR7ResponseHandlerList;

    /**
     *
     * @param $exceptionToResponseHandlerList
     * @param $stubResponseToPSR7ResponseHandlerList
     */
    public function __construct(
        PageResponseGenerator $pageResponseGenerator,
        array $exceptionToResponseHandlerList,
        //        array $stubResponseToPSR7ResponseHandlerList
    ) {
        $this->pageResponseGenerator = $pageResponseGenerator;
        $this->exceptionToResponseHandlerList = $exceptionToResponseHandlerList;
//        $this->stubResponseToPSR7ResponseHandlerList = $stubResponseToPSR7ResponseHandlerList;
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
