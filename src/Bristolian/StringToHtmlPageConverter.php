<?php

declare(strict_types = 1);

namespace Bristolian;

use Bristolian\AssetLinkEmitter;

// TODO - this needs an interface
class StringToHtmlPageConverter
{
    public function __construct(private AssetLinkEmitter $assetLinkEmitter)
    {
    }

    // Define a function that writes a string into the response object.
    public function convertStringToHtmlResponse(
        string $result,
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ): \Psr\Http\Message\ResponseInterface {

        $page = createPageHtml($this->assetLinkEmitter, $result);

        $response = $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($page);
        return $response;
    }
}
