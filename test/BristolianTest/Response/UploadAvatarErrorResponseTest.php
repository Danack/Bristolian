<?php

namespace BristolianTest\Response;

use Bristolian\Response\UploadAvatarErrorResponse;
use Bristolian\Service\AvatarImageStorage\UploadError;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\UploadAvatarErrorResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\UploadAvatarErrorResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\UploadAvatarErrorResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\UploadAvatarErrorResponse::class, 'getStatus')]

class UploadAvatarErrorResponseTest extends BaseTestCase
{
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new UploadAvatarErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new UploadAvatarErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

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
