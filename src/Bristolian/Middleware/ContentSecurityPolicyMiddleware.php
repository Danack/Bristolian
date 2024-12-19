<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Bristolian\App;
use Bristolian\Service\RequestNonce;
use Bristolian\Data\ApiDomain;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RequestNonce $requestNonce,
        private array $connectSrcDomains,
        private array $scriptSrcDomains,
        private array $frameSrcDomains,
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $cspLines = [];
        $cspLines[] = "default-src 'self'";

        if (count($this->connectSrcDomains) !== 0) {
            $cspLines[] = sprintf(
                "connect-src 'self' %s",
                implode(' ', $this->connectSrcDomains)
            );
        }
        else {
            $cspLines[] = "connect-src 'self'";
        }

        $cspLines[] = sprintf(
            "frame-src 'self' %s",
            implode(' ', $this->frameSrcDomains)
        );

        $cspLines[] = "img-src * data:";
        $cspLines[] = sprintf(
            // TODO - remove the unsafe eval
            "script-src 'self' 'nonce-%s' %s 'unsafe-eval'",
            $this->requestNonce->getRandom(),
            implode(' ', $this->scriptSrcDomains)
        );
        $cspLines[] = "object-src *";
        $cspLines[] = "style-src 'self'";
//        $cspLines[] = "report-uri " . $this->apiDomain->getDomain() . App::CSP_REPORT_PATH;
        $cspLines[] = "report-uri " . App::CSP_REPORT_PATH;

        $response = $response->withHeader(
            'Content-Security-Policy',
            implode("; ", $cspLines)
        );

        return $response;
    }
}
