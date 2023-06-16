<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Asm\RequestSessionStorage;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Di\Injector;

class AppSessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        Injector $injector,
        private RequestSessionStorage $sessionStorage
    ) {
    }

    /**
     * @param ServerRequest $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Auryn\ConfigException
     */

    public function process(Request $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // We don't open the session by default. Instead anything that needs
        // access to the session will init it.
        $response = $handler->handle($request);

        // Session could have been opened inside request
        $session = $this->sessionStorage->get();
        
        if ($session) {
            $session->save();
            $headersArrays = $session->getHeaders(
                \Asm\SessionManager::CACHE_PRIVATE,
                '/'
            //        $domain = false,
            //        $secure = false,
            //        $httpOnly = true
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
