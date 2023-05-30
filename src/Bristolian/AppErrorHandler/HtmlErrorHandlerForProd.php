<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\AssetLinkEmitter;
use Bristolian\Page;

class HtmlErrorHandlerForProd implements AppErrorHandler
{
    public function __construct(private AssetLinkEmitter $assetLinkEmitter)
    {
    }

    /**
     * @param mixed $container
     * @return \Closure|mixed
     */
    public function __invoke($container)
    {
        return function ($request, $response, \Throwable $exception) {
            \error_log("The heck: " . $exception->getMessage());
            \error_log(getTextForException($exception));
            $text = "Sorry, an error occurred.";

            $page = nl2br($text);
            $html = createPageHtml($this->assetLinkEmitter, $page);

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write($html);
        };
    }
}
