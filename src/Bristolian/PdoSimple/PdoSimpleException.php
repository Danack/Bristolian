<?php

namespace Bristolian\PdoSimple;

use PDOException;

class PdoSimpleException extends \Exception
{
    const TOO_MANY_COLUMNS_MESSAGE = "When using PdoSimple::fetchAllAsScalar one column only may be selected. You have %s selected in your query.";

    public static function tooManyColumns(int $number_of_rows): self
    {
        $message = sprintf(
            self::TOO_MANY_COLUMNS_MESSAGE,
            $number_of_rows
        );

        return new self($message);
    }
}
