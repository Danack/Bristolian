<?php

declare(strict_types=1);

namespace BristolianTest;

use Bristolian\CLIFunction;
use ErrorException;

/**
 * @coversNothing
 */
class CLIFunctionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_errorHandler_returns_true_when_error_reporting_is_zero(): void
    {
        $saved = error_reporting(0);
        try {
            $result = CLIFunction::errorHandler(
                E_WARNING,
                'should be suppressed',
                '/some/file.php',
                10
            );
            $this->assertTrue($result);
        } finally {
            error_reporting($saved);
        }
    }

    /**
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_errorHandler_returns_true_for_E_DEPRECATED(): void
    {
        $result = CLIFunction::errorHandler(
            E_DEPRECATED,
            'deprecated thing',
            '/some/file.php',
            20
        );
        $this->assertTrue($result);
    }

    /**
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_errorHandler_throws_ErrorException_for_E_WARNING(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('E_WARNING');
        $this->expectExceptionMessage('[2]');
        $this->expectExceptionMessage('should throw');
        $this->expectExceptionMessage('/myfile.php');
        $this->expectExceptionMessage('42');

        CLIFunction::errorHandler(E_WARNING, 'should throw', '/myfile.php', 42);
    }

    /**
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_errorHandler_throws_ErrorException_for_E_NOTICE(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('E_NOTICE');

        CLIFunction::errorHandler(E_NOTICE, 'undefined index', '/app/foo.php', 7);
    }

    /**
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_errorHandler_uses_generic_label_for_unknown_errno(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Error type 99999');

        CLIFunction::errorHandler(99999, 'weird error', '/x.php', 1);
    }

    /**
     * @covers \Bristolian\CLIFunction::fatalErrorShutdownHandler
     */
    public function test_fatalErrorShutdownHandler_does_nothing_when_no_fatal_occurred(): void
    {
        CLIFunction::fatalErrorShutdownHandler();
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Bristolian\CLIFunction::fatalErrorShutdownHandler
     */
    public function test_fatalErrorShutdownHandler_logs_and_exits_when_fatal_occurred(): void
    {
        $runner = __DIR__ . '/cli_fatal_runner.php';
        $this->assertFileExists($runner);

        $output = [];
        $exitCode = -999;
        exec('php ' . escapeshellarg($runner) . ' 2>&1', $output, $exitCode);

        $outputString = implode("\n", $output);
        $this->assertStringContainsString('fatal test message', $outputString);
        $this->assertStringContainsString('Oops! Something went terribly wrong', $outputString);
        $this->assertStringContainsString('Fatal error:', $outputString);
        $this->assertSame(255, $exitCode, 'exit(-1) becomes 255 on Unix');
    }

    /**
     * @covers \Bristolian\CLIFunction::setupErrorHandlers
     * @covers \Bristolian\CLIFunction::errorHandler
     */
    public function test_setupErrorHandlers_sets_handler_that_throws_on_user_warning(): void
    {
        $previous = set_error_handler(static function () {
            return false;
        });
        try {
            CLIFunction::setupErrorHandlers();

            try {
                trigger_error('test message', E_USER_WARNING);
                $this->fail('Expected ErrorException');
            } catch (ErrorException $e) {
                $this->assertStringContainsString('E_USER_WARNING', $e->getMessage());
                $this->assertStringContainsString('test message', $e->getMessage());
            }
        } finally {
            restore_error_handler();
            if ($previous !== null) {
                set_error_handler($previous);
            }
        }
    }
}
