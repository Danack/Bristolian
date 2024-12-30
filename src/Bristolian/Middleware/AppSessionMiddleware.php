<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Asm\RequestSessionStorage;
use Bristolian\AppSessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//use DI\Injector;

class AppSessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AppSessionManager $appSessionManager
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
        $session = $this->appSessionManager->getRawSession();

        if ($session) {
            $session->save();
            $headersArrays = $session->getHeaders(
                \Asm\SessionManager::CACHE_PRIVATE,
                '/'
            );

            foreach ($headersArrays as $nameAndValue) {
                $name = $nameAndValue[0];
                $value = $nameAndValue[1];

                /** @var ResponseInterface $response */
                $response = $response->withAddedHeader($name, $value);
            }
        }

        return $response;
    }
}
