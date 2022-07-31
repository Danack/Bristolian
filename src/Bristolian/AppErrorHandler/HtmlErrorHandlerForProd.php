<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\Breadcrumbs;
use Bristolian\Page;

class HtmlErrorHandlerForProd implements AppErrorHandler
{
    /**
     * @param mixed $container
     * @return \Closure|mixed
     */
    public function __invoke($container)
    {
        return function ($request, $response, \Throwable $exception) {
            \error_log("The heck: " . $exception->getMessage());
            \error_log(getTextForException($exception));
            $text = "Sorry, an error occurred. ";

            $page = createErrorPage(nl2br($text));
//            $page = Page::errorPage(nl2br($text));
            $html = createPageHtml(null, $page);

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write($html);
        };
    }
}
