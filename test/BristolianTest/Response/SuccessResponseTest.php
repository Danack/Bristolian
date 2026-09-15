<?php

namespace BristolianTest\Response;

use Bristolian\Response\SuccessResponse;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Response\SuccessResponse::class)]
class SuccessResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $response = new SuccessResponse();
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $response = new SuccessResponse();
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsSuccessJson()
    {
        $response = new SuccessResponse();
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('result', $decoded);
        $this->assertSame('success', $decoded['result']);
    }
}
