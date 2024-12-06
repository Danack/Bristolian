<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

use Bristolian\App;
use Bristolian\Basic\ErrorLogger;

class JsonErrorHandlerForLocalDev implements AppErrorHandler
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
        return function ($request, $response, $exception) {
            $data = getExceptionInfoAsArray($exception);

            $data['info'] = App::ERROR_CAUGHT_BY_ERROR_HANDLER_API_MESSAGE;

            $this->errorLogger->log(json_encode_safe($data));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode_safe($data, JSON_PRETTY_PRINT));
        };
    }
}
