<?php

namespace Bristolian\PdoSimple;

use PDOException;

class PdoSimpleWithPreviousException extends PdoSimpleException
{
    private PDOException $previous_pdo_exception;

    const INVALID_SQL = "Error preparing statement.";
    const ERROR_EXECUTING_STATEMENT = "Error executing statement: %s" ;

    public function __construct(string $message, PDOException $previous)
    {
        parent::__construct($message, 0, $previous);
        $this->previous_pdo_exception = $previous;
    }

    public static function invalidSql(PDOException $pdoe): self
    {
        return new self(self::INVALID_SQL, $pdoe);
    }

    public static function errorExecutingSql(PDOException $pdoe): self
    {
        $message = sprintf(
            self::ERROR_EXECUTING_STATEMENT,
            $pdoe->getMessage()
        );

        return new self($message, $pdoe);
    }

    public function getPreviousPdoException(): \PDOException
    {
        return $this->previous_pdo_exception;
    }
}
