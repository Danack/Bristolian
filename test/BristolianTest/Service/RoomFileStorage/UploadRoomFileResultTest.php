<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomFileStorage;

use Bristolian\Response\RoomFileUploadErrorResponse;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\Service\RoomFileStorage\UploadRoomFileResult;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadRoomFileResultTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomFileStorage\UploadRoomFileResult::__construct
     * @covers \Bristolian\Service\RoomFileStorage\UploadRoomFileResult::success
     */
    public function test_success_returns_ok_with_file_id(): void
    {
        $fileId = 'roomfile_789';
        $result = UploadRoomFileResult::success($fileId);
        $this->assertTrue($result->ok);
        $this->assertSame($fileId, $result->fileId);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\UploadRoomFileResult::failure
     */
    public function test_failure_returns_not_ok_with_error(): void
    {
        $error = UploadError::unsupportedFileType();
        $result = UploadRoomFileResult::failure($error);
        $this->assertFalse($result->ok);
        $this->assertNull($result->fileId);
        $this->assertSame($error, $result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\UploadRoomFileResult::failureResponse
     */
    public function test_failureResponse_returns_not_ok_with_response(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new RoomFileUploadErrorResponse($error);
        $result = UploadRoomFileResult::failureResponse($response);
        $this->assertFalse($result->ok);
        $this->assertNull($result->fileId);
        $this->assertNull($result->error);
        $this->assertSame($response, $result->errorResponse);
    }
}
