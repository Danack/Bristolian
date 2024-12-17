<?php

namespace Functions;

use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Bristolian\DataType\LinkParam;
use VarMap\ArrayVarMap;
use DataType\Exception\ValidationException;

/**
 * @coversNothing
 */
class ApiFunctionsTest extends BaseTestCase
{



    /**
     * @covers ::fillJsonResponseData
     */
    public function test_fillJsonResponseData_works()
    {
        $response = new Response();
        $data = ['foo' => 'bar'];

        $response_result = fillJsonResponseData($response, $data, 400);
        $stream = $response_result->getBody();
        $stream->rewind();
        $contents = $stream->getContents();

        $result = json_decode_safe($contents);

        $expectedResult = $data;
        $expectedResult['status_code'] = 400;

        $this->assertSame($expectedResult, $result);
    }


    /**
     * @covers ::convertValidationExceptionMapperApi
     */
    public function test_convertValidationExceptionMapperApi()
    {

        $request = new ServerRequest();
        $response = new Response();

        $varMap = new ArrayVarMap([
        ]);
        $exception_caught = null;
        try {
            LinkParam::createFromVarMap($varMap);
            $this->fail("Creating LinkParam did not throw exception.");
        }
        catch (ValidationException $ve) {
            $exception_caught = $ve;
        }

        $response_result = convertValidationExceptionMapperApi(
            $exception_caught,
            $request,
            $response
        );

        // TODO - convert 400 to a constant...somewhere.
        $this->assertSame(400, $response_result->getStatusCode());

        $stream = $response_result->getBody();
        $stream->rewind();
        $contents = $stream->getContents();

        $expected_response = <<< JSON
{
    "status": "fail",
    "message": "There were validation errors",
    "data": {
        "/url": "Value not set."
    },
    "status_code": 400
}
JSON;
        $this->assertSame($expected_response, $contents);
    }
}
