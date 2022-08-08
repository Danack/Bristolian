<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\Service\RequestNonce;
use Bristolian\Data\ApiDomain;
use Bristolian\App;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{
    /** @var RequestNonce */
    private $requestNonce;

    /** @var ApiDomain */
    private $apiDomain;

    public function __construct(
        RequestNonce $requestNonce,
        ApiDomain $apiDomain
    ) {
        $this->requestNonce = $requestNonce;
        $this->apiDomain = $apiDomain;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $connectSrcDomains = [
//            'https://checkout.stripe.com',
//            'https://api.stripe.com',
            $this->apiDomain->getDomain()
        ];

        $scriptSrcDomains = [
//            'https://js.stripe.com/'
        ];

        $frameSrcDomains = [
            'https://youtube.com',
            'https://www.youtube.com',
//            'https://hooks.stripe.com',
        ];

        $cspLines = [];
        $cspLines[] = "default-src 'self'";
        $cspLines[] = sprintf(
            "connect-src 'self' %s",
            implode(' ', $connectSrcDomains)
        );

        $cspLines[] = sprintf(
            "frame-src 'self' %s",
            implode(' ', $frameSrcDomains)
        );

        $cspLines[] = "img-src * data:";
        $cspLines[] = sprintf(
            "script-src 'self' 'nonce-%s' %s",
            $this->requestNonce->getRandom(),
            implode(' ', $scriptSrcDomains)
        );
        $cspLines[] = "object-src *";
        $cspLines[] = "style-src 'self'";
        $cspLines[] = "report-uri " . $this->apiDomain->getDomain() . App::CSP_REPORT_PATH;

        $response = $response->withHeader(
            'Content-Security-Policy',
            implode("; ", $cspLines)
        );

        return $response;
    }
}
