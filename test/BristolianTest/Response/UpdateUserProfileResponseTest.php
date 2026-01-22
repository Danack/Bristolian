<?php

namespace BristolianTest\Response;

use Bristolian\Response\UpdateUserProfileResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\UpdateUserProfileResponse
 */
class UpdateUserProfileResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $profile = ['username' => 'testuser'];
        $response = new UpdateUserProfileResponse($profile);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $profile = ['username' => 'testuser'];
        $response = new UpdateUserProfileResponse($profile);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsProfileWithSuccess()
    {
        $profile = [
            'username' => 'testuser',
            'email' => 'test@example.com'
        ];
        $response = new UpdateUserProfileResponse($profile);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertArrayHasKey('profile', $decoded);
        $this->assertSame('testuser', $decoded['profile']['username']);
        $this->assertSame('test@example.com', $decoded['profile']['email']);
    }
}
