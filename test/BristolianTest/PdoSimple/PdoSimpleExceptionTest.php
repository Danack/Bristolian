<?php

declare(strict_types = 1);

namespace BristolianTest\PdoSimple;

use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\PdoSimple\PdoSimpleException;

/**
 * @covers \Bristolian\PdoSimple\PdoSimpleException
 * @group db
 */
class PdoSimpleExceptionTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks(): void
    {
        $column_count = 5;
        $exception = PdoSimpleException::tooManyColumns($column_count);
        $this->assertStringMatchesTemplateString(
            PdoSimpleException::TOO_MANY_COLUMNS_MESSAGE,
            $exception->getMessage()
        );

        $this->assertStringContainsString("$column_count", $exception->getMessage());
    }

    public function testWorks_invalidSql(): void
    {
        $message = "Some PDOException";
        $pdo_exception = new \PDOException($message);
        $exception = PdoSimpleWithPreviousException::invalidSql($pdo_exception);

        $this->assertStringMatchesTemplateString(
            PdoSimpleWithPreviousException::INVALID_SQL,
            $exception->getMessage()
        );
    }

    public function testWorks_errorExecutingSql(): void
    {
        $message = "Some PDOException";
        $pdo_exception = new \PDOException($message);
        $exception = PdoSimpleWithPreviousException::errorExecutingSql($pdo_exception);

        $this->assertStringMatchesTemplateString(
            PdoSimpleWithPreviousException::ERROR_EXECUTING_STATEMENT,
            $exception->getMessage()
        );
    }
}
