<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

use Bristolian\AssetLinkEmitter;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;

class PageResponseGenerator
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private AssetLinkEmitter $assetLinkEmitter
    ) {
    }

    public function createPageWithStatusCode(
        string $contentHtml,
        int $statusCode
    ): Response {

        $page = createPageHtml($this->assetLinkEmitter, $contentHtml);

        $response = $this->responseFactory->createResponse();
        $response = $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($page);

        $response = $response->withStatus($statusCode);

        return $response;
    }
}
