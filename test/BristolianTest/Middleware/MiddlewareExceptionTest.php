<?php

namespace BristolianTest\Middleware;

use BristolianTest\BaseTestCase;
use Bristolian\Middleware\MiddlewareException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @coversNothing
 */
class MiddlewareExceptionTest extends BaseTestCase
{
    public static function provides_works()
    {
        yield ['some string', "a string"];
        yield [new \StdClass, "an object of type [stdClass]"];
    }

    /**
     * @covers \Bristolian\Middleware\MiddlewareException
     */
    #[DataProvider('provides_works')]
    public function testWorks(mixed $value, string $expected_contents)
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
