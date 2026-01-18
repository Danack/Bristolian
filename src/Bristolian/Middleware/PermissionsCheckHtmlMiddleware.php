<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Bristolian\Exception\InvalidPermissionsException;
use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
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


    public function __construct(
        private AppSessionManagerInterface $appSessionManager,
        private ApiTokenRepo $apiTokenRepo
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

        if ($check_logged_in === true) {
            // Check for session authentication
            $appSession = $this->appSessionManager->getCurrentAppSession();
            
            // If no session, check for Bearer token authentication
            if ($appSession === null) {
                $bearerToken = $this->extractBearerToken($request);
                if ($bearerToken === null) {
                    throw new InvalidPermissionsException();
                }
                
                // Validate the token
                $apiToken = $this->apiTokenRepo->getByToken($bearerToken);
                if ($apiToken === null) {
                    throw new InvalidPermissionsException();
                }
            }
        }

        $response = $handler->handle($request);

        return $response;
    }

    /**
     * Extract Bearer token from Authorization header.
     *
     * @param Request $request
     * @return string|null The token if found, null otherwise
     */
    private function extractBearerToken(Request $request): ?string
    {
        $authorizationHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authorizationHeader)) {
            return null;
        }
        
        // Check if it starts with "Bearer "
        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }
        
        // Extract the token (everything after "Bearer ")
        $token = substr($authorizationHeader, 7);
        
        // Trim whitespace
        $token = trim($token);
        
        if (empty($token)) {
            return null;
        }
        
        return $token;
    }
}
