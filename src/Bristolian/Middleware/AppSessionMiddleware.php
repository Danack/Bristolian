<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Bristolian\Session\AppSessionManager;
use Bristolian\Session\AppSessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppSessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AppSessionManagerInterface $appSessionManager
    ) {
    }

    /**
     * @param ServerRequest $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(Request $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->appSessionManager->initialize($request);

        // We don't open the session by default. Instead anything that needs
        // access to the session will init it.
        $response = $handler->handle($request);

        // Session could have been opened inside request
        $headersArrays = $this->appSessionManager->saveIfOpenedAndGetHeaders();

        if (count($headersArrays) === 0) {
            if ($request->hasHeader('x-session-renew') === true) {
                $headersArrays = $this->appSessionManager->renewSession();
            }
        }

        foreach ($headersArrays as $nameAndValue) {
            $name = $nameAndValue[0];
            $value = $nameAndValue[1];

            /** @var ResponseInterface $response */
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }
}
