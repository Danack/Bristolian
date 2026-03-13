<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Response\UploadAvatarErrorResponse;
use Bristolian\Service\AvatarImageStorage\UploadAvatarResult;
use Bristolian\Service\AvatarImageStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadAvatarResultTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadAvatarResult::__construct
     * @covers \Bristolian\Service\AvatarImageStorage\UploadAvatarResult::success
     */
    public function test_success_returns_ok_with_avatar_id(): void
    {
        $avatarImageId = 'av_123';
        $result = UploadAvatarResult::success($avatarImageId);
        $this->assertTrue($result->ok);
        $this->assertSame($avatarImageId, $result->avatarImageId);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadAvatarResult::failure
     */
    public function test_failure_returns_not_ok_with_error(): void
    {
        $error = UploadError::extensionNotAllowed('exe');
        $result = UploadAvatarResult::failure($error);
        $this->assertFalse($result->ok);
        $this->assertNull($result->avatarImageId);
        $this->assertSame($error, $result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadAvatarResult::failureResponse
     */
    public function test_failureResponse_returns_not_ok_with_response(): void
    {
        $error = UploadError::imageTooSmall(100, 100, 512);
        $response = new UploadAvatarErrorResponse($error);
        $result = UploadAvatarResult::failureResponse($response);
        $this->assertFalse($result->ok);
        $this->assertNull($result->avatarImageId);
        $this->assertNull($result->error);
        $this->assertSame($response, $result->errorResponse);
    }
}
