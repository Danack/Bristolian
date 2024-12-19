<?php

namespace BristolianTest\Middleware;


use BristolianTest\BaseTestCase;
use Bristolian\Middleware\MiddlewareException;


/**
 * @coversNothing
 */
class MiddlewareExceptionTest extends BaseTestCase
{
    public function provides_works()
    {
        yield ['some string', "a string"];
        yield [new \StdClass, "an object of type [stdClass]"];
    }

    /**
     * @covers \Bristolian\Middleware\MiddlewareException
     * @dataProvider provides_works
     */
    public function testWorks($value, $expected_contents)
    {
        $e = new \Exception("not used");
        $result = MiddlewareException::errorHandlerFailedToReturnResponse(
            $e,
            $value
        );

        $this->assertStringMatchesTemplateString(
            \Bristolian\Middleware\MiddlewareException::ERROR_HANDLER_FAILED_TO_RETURN_RESPONSE,
            $result->getMessage()
        );

        $this->assertStringContainsStringIgnoringCase($expected_contents, $result->getMessage());
    }
}