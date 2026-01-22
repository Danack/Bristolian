<?php

namespace BristolianTest\Response;

use Bristolian\Response\GetUserInfoResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetUserInfoResponse
 */
class GetUserInfoResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $userInfo = ['id' => 'user-123', 'username' => 'testuser'];
        $response = new GetUserInfoResponse($userInfo);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $userInfo = ['id' => 'user-123'];
        $response = new GetUserInfoResponse($userInfo);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsUserInfoAsJson()
    {
        $userInfo = [
            'id' => 'user-123',
            'username' => 'testuser',
            'email' => 'test@example.com'
        ];
        $response = new GetUserInfoResponse($userInfo);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('user-123', $decoded['id']);
        $this->assertSame('testuser', $decoded['username']);
        $this->assertSame('test@example.com', $decoded['email']);
    }
}
