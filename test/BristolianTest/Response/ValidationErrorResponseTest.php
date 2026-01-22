<?php

namespace BristolianTest\Response;

use Bristolian\Response\ValidationErrorResponse;
use BristolianTest\BaseTestCase;
use DataType\ValidationProblem;

/**
 * @covers \Bristolian\Response\ValidationErrorResponse
 */
class ValidationErrorResponseTest extends BaseTestCase
{
    public function testGetStatusReturns400()
    {
        $problems = [];
        $response = ValidationErrorResponse::fromProblems($problems);
        
        $this->assertSame(400, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $problems = [];
        $response = ValidationErrorResponse::fromProblems($problems);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsErrors()
    {
        $dataStorage1 = \DataType\DataStorage\TestArrayDataStorage::fromArray([])->moveKey('name');
        $dataStorage2 = \DataType\DataStorage\TestArrayDataStorage::fromArray([])->moveKey('email');
        $problem1 = new ValidationProblem($dataStorage1, 'Field "name" is required');
        $problem2 = new ValidationProblem($dataStorage2, 'Field "email" must be a valid email');
        $problems = [$problem1, $problem2];
        
        $response = ValidationErrorResponse::fromProblems($problems);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('errors', $decoded);
        $this->assertCount(2, $decoded['errors']);
    }

    public function testGetBodyWithEmptyProblems()
    {
        $problems = [];
        $response = ValidationErrorResponse::fromProblems($problems);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertCount(0, $decoded['errors']);
    }
}
