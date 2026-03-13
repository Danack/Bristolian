<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Response\MemeUploadErrorResponse;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\MemeStorageProcessor\UploadMemeResult;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadMemeResultTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadMemeResult::__construct
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadMemeResult::success
     */
    public function test_success_returns_ok_with_meme(): void
    {
        $meme = new ObjectStoredMeme('normalized.png', 'meme_456');
        $result = UploadMemeResult::success($meme);
        $this->assertTrue($result->ok);
        $this->assertSame($meme, $result->meme);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadMemeResult::failure
     */
    public function test_failure_returns_not_ok_with_error(): void
    {
        $error = UploadError::unsupportedFileType();
        $result = UploadMemeResult::failure($error);
        $this->assertFalse($result->ok);
        $this->assertNull($result->meme);
        $this->assertSame($error, $result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadMemeResult::failureResponse
     */
    public function test_failureResponse_returns_not_ok_with_response(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new MemeUploadErrorResponse($error);
        $result = UploadMemeResult::failureResponse($response);
        $this->assertFalse($result->ok);
        $this->assertNull($result->meme);
        $this->assertNull($result->error);
        $this->assertSame($response, $result->errorResponse);
    }
}
