<?php

declare(strict_types=1);

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\StubResponse;

final class HandleRoomFileUpload
{
    public function __construct(
        private RoomFileStorage $roomFileStorage,
        private UserSessionFileUploadHandler $uploadHandler
    ) {
    }

    public function handle(string $userId, string $roomId, string $formFieldName): UploadRoomFileResult
    {
        $fileOrResponse = $this->uploadHandler->fetchUploadedFile($formFieldName);

        if ($fileOrResponse instanceof StubResponse) {
            return UploadRoomFileResult::failureResponse($fileOrResponse);
        }

        $fileIdOrError = $this->roomFileStorage->storeFileForRoomAndUser(
            $userId,
            $roomId,
            $fileOrResponse
        );

        if ($fileIdOrError instanceof UploadError) {
            return UploadRoomFileResult::failure($fileIdOrError);
        }

        return UploadRoomFileResult::success($fileIdOrError);
    }
}
