<?php

declare(strict_types = 1);

namespace BristolianTest\PdoSimple;

use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\PdoSimple\PdoSimpleException;

/**
 * @covers \Bristolian\PdoSimple\PdoSimpleException
 * @covers \Bristolian\PdoSimple\PdoSimpleWithPreviousException
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

    public function testConstructor(): void
    {
        $custom_message = "Custom error message";
        $pdo_exception = new \PDOException("Original PDO error");
        
        $exception = new PdoSimpleWithPreviousException($custom_message, $pdo_exception);
        
        $this->assertEquals($custom_message, $exception->getMessage());
        $this->assertSame($pdo_exception, $exception->getPreviousPdoException());
        $this->assertSame($pdo_exception, $exception->getPrevious());
    }

    public function testGetPreviousPdoException(): void
    {
        $pdo_exception = new \PDOException("Test PDO error");
        $exception = new PdoSimpleWithPreviousException("Test message", $pdo_exception);
        
        $this->assertSame($pdo_exception, $exception->getPreviousPdoException());
    }

    public function testInvalidSql_storesPreviousException(): void
    {
        $pdo_message = "SQL syntax error";
        $pdo_exception = new \PDOException($pdo_message);
        $exception = PdoSimpleWithPreviousException::invalidSql($pdo_exception);

        $this->assertEquals(PdoSimpleWithPreviousException::INVALID_SQL, $exception->getMessage());
        $this->assertSame($pdo_exception, $exception->getPreviousPdoException());
        $this->assertSame($pdo_exception, $exception->getPrevious());
    }

    public function testErrorExecutingSql_storesPreviousException(): void
    {
        $pdo_message = "Column 'nonexistent' doesn't exist";
        $pdo_exception = new \PDOException($pdo_message);
        $exception = PdoSimpleWithPreviousException::errorExecutingSql($pdo_exception);

        $expected_message = sprintf(
            PdoSimpleWithPreviousException::ERROR_EXECUTING_STATEMENT,
            $pdo_message
        );
        
        $this->assertEquals($expected_message, $exception->getMessage());
        $this->assertSame($pdo_exception, $exception->getPreviousPdoException());
        $this->assertSame($pdo_exception, $exception->getPrevious());
    }

    public function testInheritance(): void
    {
        $pdo_exception = new \PDOException("Test error");
        $exception = new PdoSimpleWithPreviousException("Test message", $pdo_exception);
        
        $this->assertInstanceOf(PdoSimpleException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstants(): void
    {
        $this->assertEquals("Error preparing statement.", PdoSimpleWithPreviousException::INVALID_SQL);
        $this->assertEquals("Error executing statement: %s", PdoSimpleWithPreviousException::ERROR_EXECUTING_STATEMENT);
    }
}
