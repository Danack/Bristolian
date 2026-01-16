<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Bristolian\Exception\InvalidPermissionsException;
use Bristolian\Session\AppSessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class PermissionsCheckHtmlMiddleware implements MiddlewareInterface
{
    /**
     * @var string[]
     */
    private $allowed_paths = [
        '/login',
        '/api/login-status',
        '/api/services/email/mailgun' // mailgun has token based access
    ];

    // Allow token based access for paths
    private $tinned_fish_paths = [
        "/api/tfd/v1"
    ];


    public function __construct(
        private AppSessionManagerInterface $appSessionManager
    ) {
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): ResponseInterface
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

        // TODO - add api keys
        foreach ($this->tinned_fish_paths as $allowed_path) {
            if (str_starts_with($request->getUri()->getPath(), $allowed_path) === true) {
                $check_logged_in = false;
                break;
            }
        }

        if ($check_logged_in === true) {
            $appSession = $this->appSessionManager->getCurrentAppSession();
            if ($appSession === null) {
                throw new InvalidPermissionsException();
            }
        }

        $response = $handler->handle($request);

        return $response;
    }
}
