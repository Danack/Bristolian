<?php

namespace BristolianTest\Basic;

use BristolianTest\BaseTestCase;
use Bristolian\Basic\Dispatcher;
use DI\Injector;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;

/**
 * @coversNothing
 */
class DispatcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Basic\Dispatcher::dispatch_route
     * @covers \Bristolian\Basic\Dispatcher::__construct
     */
    public function testWorks_dispatch_route()
    {
        $injector = new Injector;
        $dispatcher = new Dispatcher($injector);
        $value = "123456";

        $request = new ServerRequest();
        $routeArguments = ['foo' => $value];
        $passed_value = null;
        $callable_result = "Hello.";

        $resolvedCallable = function ($foo) use (&$passed_value, $callable_result) {
            $passed_value = $foo;
            return $callable_result;
        };

        $result = $dispatcher->dispatch_route(
            $request,
            $routeArguments,
            $resolvedCallable
        );

        $this->assertSame($callable_result, $result);
        $this->assertSame($value, $passed_value);
    }

    /**
     * @covers \Bristolian\Basic\Dispatcher::convert_response_to_html
     */
    public function testWorks_convert_response_to_html()
    {
        $injector = new Injector;
        $dispatcher = new Dispatcher($injector);
        $expected_result = "expected result";

        $passed_result = null;
        $passed_request = null;
        $passed_response = null;

        $fn = function (
            $result,
            $request,
            $response
        ) use (
            $expected_result,
            &$passed_result,
            &$passed_request,
            &$passed_response
) {
            $passed_result = $result;
            $passed_request = $request;
            $passed_response = $response;
            return $expected_result;
        };

        $result = "Some result";
        $request = new ServerRequest();
        $response = new Response();

        $actual_result = $dispatcher->convert_response_to_html(
            $fn,
            $result,
            $request,
            $response
        );

        $this->assertSame($result, $passed_result);
        $this->assertSame($request, $passed_request);
        $this->assertSame($response, $passed_response);
        $this->assertSame($actual_result, $expected_result);
    }
}
