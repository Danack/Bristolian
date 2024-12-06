<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\Basic\ErrorLogger;

class JsonErrorHandlerForProd implements AppErrorHandler
{
    public function __construct(private ErrorLogger $errorLogger)
    {
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
            $text = json_encode("Sorry, there was an error.\n");
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write($text);
        };
    }
}
