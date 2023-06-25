<?php

declare(strict_types = 1);

namespace BristolianTest;

/**
 * @coversNothing
 */
class FunctionsTest extends BaseTestCase
{
    /**
     * @covers ::getRandomId
     */
    public function testGetRandomId()
    {
        $id = getRandomId();
        $this->assertSame(64, strlen($id));
    }


    public function test_hackVarMap()
    {
    }

    public function test_formatLinesWithCount()
    {
        $result = formatLinesWithCount(['foo', 'bar']);
        $expected = <<<TEXT
#0 foo
#1 bar

TEXT;
        $this->assertSame($expected, $result);
    }

    public function test_checkSignalsForExit()
    {
    }

    public function test_continuallyExecuteCallable()
    {
    }

    public function test_json_decode_safe()
    {
    }

    public function test_json_encode_safe()
    {
    }

    public function test_getExceptionInfoAsArray()
    {
    }

    /**
     * @covers ::peak_memory
     */
    public function test_peak_memory()
    {
        $memory_string = peak_memory();
        $this->assertGreaterThanOrEqual(
            9,
            strlen($memory_string),
            "memory used is only " . $memory_string
        );
    }


    public function test_convertToValue()
    {
    }


    /**
     * @group slow
     */
    public function test_fetchUri()
    {
    }


    public function test_convertStringToHtmlResponse()
    {
    }


    public function test_getMask()
    {
    }


    public function test_twiddleWithShit()
    {
    }


    public function test_showTotalErrorPage()
    {
    }


    public function test_getMemoryLimit()
    {
    }


    public function test_getPercentMemoryUsed()
    {
    }


    public function test_normaliseFilePath()
    {
    }


    public function test_getEnvString()
    {
    }


    /**
     * @covers ::array_contains
     */
    public function test_array_contains()
    {
        $this->assertFalse(array_contains(true, [1]));
        $this->assertTrue(array_contains(1, [1]));
    }

    public function test_generate_password_hash()
    {
        $result = generate_password_hash("Hello");
        $this->assertEquals(60, strlen($result));
    }

    /**
     * @covers ::get_password_options
     */
    public function test_get_password_options()
    {
        $options = get_password_options();
        $this->assertArrayHasKey("cost", $options);
        $this->assertGreaterThanOrEqual(12, $options["cost"]);
    }
}
