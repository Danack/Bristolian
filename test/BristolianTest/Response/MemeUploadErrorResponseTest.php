<?php

namespace BristolianTest\Response;

use Bristolian\Response\MemeUploadErrorResponse;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\MemeUploadErrorResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\MemeUploadErrorResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\MemeUploadErrorResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\MemeUploadErrorResponse::class, 'getStatus')]

class MemeUploadErrorResponseTest extends BaseTestCase
{
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsErrorJsonWithMessageOnly(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['result']);
        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $decoded['error']);
        $this->assertArrayNotHasKey('error_code', $decoded);
        $this->assertArrayNotHasKey('error_data', $decoded);
    }

    public function testGetBodyIncludesErrorCodeAndErrorDataWhenPresent(): void
    {
        $error = UploadError::duplicateOriginalFilename('test.png');
        $response = new MemeUploadErrorResponse($error);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['result']);
        $this->assertStringContainsString('test.png', $decoded['error']);
        $this->assertSame(UploadError::DUPLICATE_FILENAME, $decoded['error_code']);
        $this->assertSame(['filename' => 'test.png'], $decoded['error_data']);
    }
}
