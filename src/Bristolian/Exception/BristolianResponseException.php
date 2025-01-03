<?php

namespace Bristolian\Exception;

use SlimDispatcher\Response\ResponseException;

class BristolianResponseException extends ResponseException
{
    const FAILED_TO_OPEN_FILE = "Failed to open file [%s] for serving.";

    public static function failedToOpenFile($filenameToServe)
    {
        $message = sprintf(self::FAILED_TO_OPEN_FILE, $filenameToServe);
        return new self($message);
    }

}