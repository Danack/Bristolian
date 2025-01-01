<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Asm\RequestSessionStorage;
use Bristolian\Session\AppSessionManager;
use Bristolian\Exception\InvalidPermissionsException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class PermissionsCheckHtmlMiddleware
{
    /**
     * @var string[]
     */
    private $allowed_paths = [
        '/login',
        '/api/login-status'
    ];

    public function __construct(
        //        private \DI\Injector $injector,
        //        private RequestSessionStorage $sessionStorage,
        private AppSessionManager $appSessionManager
    ) {
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $check_logged_in = false;

        if (strcasecmp($request->getMethod(), 'POST') === 0) {
            $check_logged_in = true;
            foreach ($this->allowed_paths as $allowed_path) {
                if (str_starts_with($request->getUri()->getPath(), $allowed_path) === true) {
                    $check_logged_in = false;
                    break;
                }
            }
        }

        if ($check_logged_in === true) {
            $appSession = $this->appSessionManager->getCurrentAppSession();
//            $session = $this->sessionStorage->get();
            if ($appSession === null) {
                throw new InvalidPermissionsException();
            }
        }

        $response = $handler->handle($request);

        return $response;
    }
}
