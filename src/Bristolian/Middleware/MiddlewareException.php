<?php

namespace Bristolian\Middleware;

use Bristolian\BristolianException;

class MiddlewareException extends BristolianException
{
    const ERROR_HANDLER_FAILED_TO_RETURN_RESPONSE = <<< TEXT
Exception handler for exception type %s failed to return a ResponseInterface object instead got %s
TEXT;

    public static function errorHandlerFailedToReturnResponse(
        \Throwable $e,
        mixed $response
    ) {
        $message = sprintf(
            self::ERROR_HANDLER_FAILED_TO_RETURN_RESPONSE,
            get_class($e),
            get_readable_variable_type($response)
        );

        return new self($message);
    }
}