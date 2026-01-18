<?php

namespace Bristolian\Exception;

class DataEncodingException extends BristolianException
{
    public function __construct(string $message, string $error)
    {
        $full_message = $message . " : " . $error;

        parent::__construct($full_message);
    }
}
