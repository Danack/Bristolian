<?php

declare(strict_types=1);

namespace BristolianTest;

/**
 * @coversNothing
 */
class ErrorFunctionsTest extends BaseTestCase
{
    /**
     * @covers ::getExceptionText
     */
    public function test_getExceptionText_returns_class_message_and_trace_for_single_exception(): void
    {
        $exception = new \RuntimeException('Something broke');
        $text = getExceptionText($exception);
        $this->assertStringContainsString('RuntimeException', $text);
        $this->assertStringContainsString('Something broke', $text);
        $this->assertStringContainsString('#0', $text);
    }

    /**
     * @covers ::getExceptionText
     */
    public function test_getExceptionText_includes_previous_exception(): void
    {
        $previous = new \InvalidArgumentException('Root cause');
        $exception = new \RuntimeException('Wrapper', 0, $previous);
        $text = getExceptionText($exception);
        $this->assertStringContainsString('RuntimeException', $text);
        $this->assertStringContainsString('Wrapper', $text);
        $this->assertStringContainsString('InvalidArgumentException', $text);
        $this->assertStringContainsString('Root cause', $text);
    }

    /**
     * @covers ::purgeExceptionMessage
     */
    public function test_purgeExceptionMessage_returns_message_unchanged_when_no_phrase(): void
    {
        $exception = new \Exception('Plain message');
        $this->assertSame('Plain message', purgeExceptionMessage($exception));
    }

    /**
     * @covers ::purgeExceptionMessage
     */
    public function test_purgeExceptionMessage_truncates_at_with_params_and_appends_purged(): void
    {
        $exception = new \Exception('Something with params and more secret data');
        $result = purgeExceptionMessage($exception);
        $this->assertStringContainsString('with params', $result);
        $this->assertStringContainsString('**PURGED**', $result);
        $this->assertStringNotContainsString('more secret data', $result);
    }

    /**
     * @covers ::getTextForException
     */
    public function test_getTextForException_includes_type_message_and_file(): void
    {
        $exception = new \DomainException('Test message');
        $text = getTextForException($exception);
        $this->assertStringContainsString('Exception type:', $text);
        $this->assertStringContainsString('DomainException', $text);
        $this->assertStringContainsString('Message:', $text);
        $this->assertStringContainsString('Test message', $text);
        $this->assertStringContainsString('File:', $text);
        $this->assertStringContainsString('Stack trace', $text);
    }

    /**
     * @covers ::getTextForException
     */
    public function test_getTextForException_uses_purgeExceptionMessage(): void
    {
        $exception = new \Exception('Sensitive with params hidden');
        $text = getTextForException($exception);
        $this->assertStringContainsString('**PURGED**', $text);
    }

    /**
     * @covers ::getStacktraceForException
     */
    public function test_getStacktraceForException_returns_formatted_lines(): void
    {
        $exception = new \Exception('Trace test');
        $result = getStacktraceForException($exception);
        $this->assertStringContainsString('#0', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_file_and_line(): void
    {
        $trace = ['file' => '/var/app/src/foo.php', 'line' => 42, 'function' => 'bar'];
        $result = formatTraceLine($trace, 1);
        $this->assertStringContainsString('#1', $result);
        $this->assertStringContainsString('foo.php', $result);
        $this->assertStringContainsString('42', $result);
        $this->assertStringContainsString('bar', $result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_file_only(): void
    {
        $trace = ['file' => '/var/app/src/foo.php', 'function' => 'baz'];
        $result = formatTraceLine($trace, 0);
        $this->assertStringContainsString('#0', $result);
        $this->assertStringContainsString('foo.php', $result);
        $this->assertStringContainsString('??', $result);
        $this->assertStringContainsString('baz', $result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_class_type_and_function(): void
    {
        $trace = [
            'file' => __DIR__ . '/bar.php',
            'line' => 10,
            'class' => 'MyClass',
            'type' => '::',
            'function' => 'staticMethod',
        ];
        $result = formatTraceLine($trace, 2);
        $this->assertStringContainsString('#2', $result);
        $this->assertStringContainsString('MyClass::staticMethod', $result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_class_and_function_no_type(): void
    {
        $trace = [
            'file' => __DIR__ . '/baz.php',
            'line' => 1,
            'class' => 'Foo',
            'function' => 'method',
        ];
        $result = formatTraceLine($trace, 0);
        $this->assertStringContainsString('Foo_method', $result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_no_file_uses_question_marks(): void
    {
        $trace = ['function' => 'anonymous'];
        $result = formatTraceLine($trace, 0);
        $this->assertStringContainsString('??', $result);
        $this->assertStringContainsString('anonymous', $result);
    }

    /**
     * @covers ::formatTraceLine
     */
    public function test_formatTraceLine_with_no_function_key_uses_weird_message(): void
    {
        $trace = ['file' => '/some/file.php', 'line' => 1];
        $result = formatTraceLine($trace, 0);
        $this->assertStringContainsString('Function is weird:', $result);
    }

    /**
     * @covers ::getFormattedException
     */
    public function test_getFormattedException_returns_trace_lines(): void
    {
        $exception = new \Exception('Formatted');
        $output = getFormattedException($exception);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('#0', $output);
    }

    /**
     * @covers ::getFormattedException
     */
    public function test_getFormattedException_includes_previous_exception_traces(): void
    {
        $previous = new \LogicException('Inner');
        $exception = new \RuntimeException('Outer', 0, $previous);
        $output = getFormattedException($exception);
        $this->assertNotEmpty($output);
    }

    /**
     * @covers ::getExceptionStackAsArray
     */
    public function test_getExceptionStackAsArray_returns_array_of_trace_lines(): void
    {
        $exception = new \Exception('Stack array');
        $lines = getExceptionStackAsArray($exception);
        $this->assertIsArray($lines);
        $this->assertNotEmpty($lines);
        foreach ($lines as $line) {
            $this->assertIsString($line);
        }
    }

    /**
     * @covers ::saneErrorHandler
     */
    public function test_saneErrorHandler_returns_false_for_E_DEPRECATED(): void
    {
        $result = saneErrorHandler(E_DEPRECATED, 'deprecated thing', '/path/file.php', 1);
        $this->assertFalse($result);
    }

    /**
     * @covers ::saneErrorHandler
     */
    public function test_saneErrorHandler_returns_false_for_E_ERROR(): void
    {
        $result = saneErrorHandler(E_ERROR, 'Fatal', '/path/file.php', 1);
        $this->assertFalse($result);
    }

    /**
     * @covers ::saneErrorHandler
     */
    public function test_saneErrorHandler_returns_false_for_E_CORE_ERROR(): void
    {
        $result = saneErrorHandler(E_CORE_ERROR, 'Core fatal', '/path/file.php', 1);
        $this->assertFalse($result);
    }

    /**
     * @covers ::saneErrorHandler
     */
    public function test_saneErrorHandler_throws_for_other_errors(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error:');
        $this->expectExceptionMessage('/path/file.php:10');
        saneErrorHandler(E_WARNING, 'Something went wrong', '/path/file.php', 10);
    }

    /**
     * @covers ::saneErrorHandler
     */
    public function test_saneErrorHandler_returns_true_when_reporting_suppressed_and_not_user_deprecated(): void
    {
        $previous = error_reporting(0);
        try {
            $result = saneErrorHandler(E_NOTICE, 'Suppressed notice', '/path/file.php', 1);
            $this->assertTrue($result);
        } finally {
            error_reporting($previous);
        }
    }
}
