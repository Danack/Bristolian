<?php

namespace BristolianTest\Response;

use Bristolian\Response\MemeUploadErrorResponse;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class MemeUploadErrorResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\MemeUploadErrorResponse::__construct
     * @covers \Bristolian\Response\MemeUploadErrorResponse::getStatus
     */
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\MemeUploadErrorResponse::getHeaders
     */
    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\MemeUploadErrorResponse::getBody
     */
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

    /**
     * @covers \Bristolian\Response\MemeUploadErrorResponse::__construct
     * @covers \Bristolian\Response\MemeUploadErrorResponse::getBody
     */
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
