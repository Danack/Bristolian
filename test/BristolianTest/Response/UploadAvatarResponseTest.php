<?php

namespace BristolianTest\Response;

use Bristolian\Response\UploadAvatarResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\UploadAvatarResponse
 */
class UploadAvatarResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $avatarImageId = 'avatar-123';
        $response = new UploadAvatarResponse($avatarImageId);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $avatarImageId = 'avatar-123';
        $response = new UploadAvatarResponse($avatarImageId);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsAvatarImageId()
    {
        $avatarImageId = 'avatar-456';
        $response = new UploadAvatarResponse($avatarImageId);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame($avatarImageId, $decoded['avatar_image_id']);
    }
}
