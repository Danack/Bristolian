<?php

declare(strict_types=1);

namespace Bristolian\Service\AvatarImageStorage;

use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\StubResponse;

final class HandleAvatarUpload
{
    public function __construct(
        private AvatarImageStorage $avatarImageStorage,
        private UserSessionFileUploadHandler $uploadHandler,
        private UserProfileRepo $userProfileRepo
    ) {
    }

    public function handle(string $userId, string $formFieldName): UploadAvatarResult
    {
        $fileOrResponse = $this->uploadHandler->fetchUploadedFile($formFieldName);

        if ($fileOrResponse instanceof StubResponse) {
            return UploadAvatarResult::failureResponse($fileOrResponse);
        }

        $avatarImageIdOrError = $this->avatarImageStorage->storeAvatarForUser(
            $userId,
            $fileOrResponse,
            get_supported_avatar_image_extensions()
        );

        if ($avatarImageIdOrError instanceof UploadError) {
            return UploadAvatarResult::failure($avatarImageIdOrError);
        }

        $this->userProfileRepo->updateAvatarImage($userId, $avatarImageIdOrError);

        return UploadAvatarResult::success($avatarImageIdOrError);
    }
}
