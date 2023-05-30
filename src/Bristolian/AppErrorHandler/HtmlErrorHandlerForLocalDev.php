<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\App;
use SlimAuryn\ResponseMapper\ResponseMapper;

use SlimDispatcher\Response\HtmlResponse;
use Bristolian\Page;
use Bristolian\AssetLinkEmitter;

use function SlimDispatcher\mapStubResponseToPsr7;

class HtmlErrorHandlerForLocalDev implements AppErrorHandler
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
        /**
         * @param mixed $request
         * @param mixed $response
         * @param mixed $exception
         * @return mixed
         */
        return function ($request, $response, $exception) {
            /** @var \Throwable $exception */
            $text = getTextForException($exception);
            /** This is to allow testing */
            $text .= App::ERROR_CAUGHT_BY_ERROR_HANDLER_MESSAGE;
            $page = nl2br($text);

            $html = createPageHtml($this->assetLinkEmitter, $page);
            $stubResponse = new HtmlResponse($html, [], 500);
            \error_log($text);
            $response = mapStubResponseToPsr7($stubResponse, $request, $response);

            return $response;
        };
    }
}
