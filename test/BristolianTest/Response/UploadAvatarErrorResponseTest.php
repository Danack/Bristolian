<?php

namespace BristolianTest\Response;

use Bristolian\Response\UploadAvatarErrorResponse;
use Bristolian\Service\AvatarImageStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadAvatarErrorResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\UploadAvatarErrorResponse::__construct
     * @covers \Bristolian\Response\UploadAvatarErrorResponse::getStatus
     */
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new UploadAvatarErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\UploadAvatarErrorResponse::getHeaders
     */
    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new UploadAvatarErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\UploadAvatarErrorResponse::getBody
     */
    public function testGetBodyReturnsErrorJson(): void
    {
        $error = UploadError::extensionNotAllowed('exe');
        $response = new UploadAvatarErrorResponse($error);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertStringContainsString('exe', $decoded['error']);
    }
}
