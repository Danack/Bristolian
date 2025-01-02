<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\Basic\ErrorLogger;

class HtmlErrorHandlerForProd implements AppErrorHandler
{
    public function __construct(
        private AssetLinkEmitter $assetLinkEmitter,
        private ErrorLogger $errorLogger
    ) {
    }

    /**
     * @param mixed $container
     * @return \Closure|mixed
     */
    public function __invoke($container)
    {
        return function ($request, $response, \Throwable $exception) {
            $this->errorLogger->log("The heck: " . $exception->getMessage());
            $this->errorLogger->log(getTextForException($exception));
            $text = "Sorry, an error occurred.";

            $page = nl2br($text);
            $html = createPageHtml($this->assetLinkEmitter, $page);

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write($html);
        };
    }
}
