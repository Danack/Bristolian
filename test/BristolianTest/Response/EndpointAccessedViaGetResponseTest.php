<?php

namespace BristolianTest\Response;

use Bristolian\Response\EndpointAccessedViaGetResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\EndpointAccessedViaGetResponse
 */
class EndpointAccessedViaGetResponseTest extends BaseTestCase
{
    public function testGetStatusReturns405()
    {
        $response = new EndpointAccessedViaGetResponse();
        
        $this->assertSame(405, $response->getStatus());
    }

    public function testGetHeadersReturnsContentTypeAndAllow()
    {
        $response = new EndpointAccessedViaGetResponse();
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('text/plain', $headers['Content-Type']);
        $this->assertArrayHasKey('Allow', $headers);
        $this->assertSame('POST', $headers['Allow']);
    }

    public function testGetBodyReturnsDefaultMessage()
    {
        $response = new EndpointAccessedViaGetResponse();
        $body = $response->getBody();
        
        $this->assertStringContainsString('POST request', $body);
    }

    public function testGetBodyReturnsCustomMessage()
    {
        $customMessage = 'Custom error message';
        $response = new EndpointAccessedViaGetResponse($customMessage);
        $body = $response->getBody();
        
        $this->assertSame($customMessage, $body);
    }

    public function testForDeleteReturnsDeleteMessage()
    {
        $response = EndpointAccessedViaGetResponse::forDelete();
        $body = $response->getBody();
        
        $this->assertStringContainsString('DELETE request', $body);
    }
}
