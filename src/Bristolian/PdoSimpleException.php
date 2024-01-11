<?php

namespace Bristolian;

class PdoSimpleException extends \Exception
{
    public static function tooManyColumns(int $number_of_rows)
    {
        $message = "When using PdoSimple::fetchAllAsScalar one column only may be selected. You have $number_of_rows selected in your query.";

        return new self($message);
    }
}
