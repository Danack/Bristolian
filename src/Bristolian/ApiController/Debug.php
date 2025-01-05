<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use Bristolian\Exception\DebuggingCaughtException;
use Bristolian\Exception\DebuggingUncaughtException;
use SlimDispatcher\Response\JsonResponse;

class Debug
{
    public function testUncaughtException(): never
    {
        throw new DebuggingUncaughtException(
            "Hello, I am a test exception that won't be caught by the application."
        );
    }

    public function testCaughtException(): never
    {
        throw new DebuggingCaughtException(
            "Hello, I am a test exception that will be caught by the application."
        );
    }

    /**
     * This function is to make it easy for a developer to check if xdebug is
     * working correctly, and their debugger is also working correctly listening
     * for breakpoints.
     * @return JsonResponse
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function testXdebugWorking(): JsonResponse
    {
        // @codeCoverageIgnoreStart
        if (function_exists('xdebug_break') === false) {
            return new JsonResponse(
                ['status' => "xdebug_break isn't a function. Are you on the xdebug port?"]
            );
        }

        \xdebug_break();
        return new JsonResponse(['status' => 'ok']);
        // @codeCoverageIgnoreEnd
    }
}
