<?php

namespace Bristolian\AppController;

use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use Bristolian\Response\GetUserInfoResponse;
use Bristolian\Response\UpdateUserProfileResponse;
use Bristolian\Response\UploadAvatarErrorResponse;
use Bristolian\Response\UploadAvatarResponse;
use Bristolian\Service\AvatarImageStorage\HandleAvatarUpload;
use Bristolian\Session\UserSession;
use SlimDispatcher\Response\JsonResponse;
use user_repo\UserRepo\UserRepo;

class Users
{
    public const AVATAR_FILE_UPLOAD_FORM_NAME = 'avatar_file';

    public function index(/*UserRepo $userRepo*/): string
    {
        $contents = "<h1>User list</h1>";

        // TODO - we need need to have user ids for all users.
        // I need to go back through and 'normalise' a whole load of code.
        $template = "<a href='/users/:attr_user_id/:attr_username'>:html_username</a>";

//        foreach ($userRepo->getUsers() as $user) {
//            $params = [
////                ':attr_user_id' => $user->user_id,
//                ':attr_username' => $user->username,
//                ':html_username' => $user->username,
//            ];
//
//            $contents .= esprintf($template, $params);
//        }

        $contents .= "This is a little broken";

        return $contents;
    }

    public function whoami(
        \Bristolian\Session\AppSessionManager $appSessionManager,
        UserProfileRepo $userProfileRepo
    ): JsonResponse|\SlimDispatcher\Response\HtmlResponse {
        $appSession = $appSessionManager->getCurrentAppSession();
        
        if (!$appSession) {
            return new \SlimDispatcher\Response\HtmlResponse(
                'Not logged in',
                [],
                404
            );
        }

        $user_id = $appSession->getUserId();
        $user_profile = $userProfileRepo->getUserProfile($user_id);

        $data = [
            'user_id' => $user_id,
            'avatar_image_id' => $user_profile->getAvatarImageId(),
        ];

        [$error, $values] = convertToValue($data);

        return new JsonResponse($values);
    }

    public function getUserInfo(
        UserProfileRepo $userProfileRepo,
        string $user_id
    ): GetUserInfoResponse {
        $user_profile = $userProfileRepo->getUserProfile($user_id);

        $data = [
            'user_id' => $user_id,
            'display_name' => $user_profile->getDisplayName(),
            'avatar_image_id' => $user_profile->getAvatarImageId(),
        ];

        [$error, $values] = convertToValue($data);

        return new GetUserInfoResponse($values);
    }

    public function getUserAvatar(
        \Bristolian\Filesystem\AvatarImageFilesystem $avatarFilesystem,
        \Bristolian\Filesystem\LocalCacheFilesystem $localCacheFilesystem,
        \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo $avatarImageStorageInfoRepo,
        UserProfileRepo $userProfileRepo,
        string $user_id
    ): \Bristolian\Response\StreamingResponse|\Bristolian\Response\StoredFileErrorResponse {
        // Get the user's profile to find their avatar_image_id
        $user_profile = $userProfileRepo->getUserProfile($user_id);
        
        if (!$user_profile->getAvatarImageId()) {
            return new \Bristolian\Response\StoredFileErrorResponse('No avatar for user: ' . $user_id);
        }

        $avatar_image_id = $user_profile->getAvatarImageId();
        $fileDetails = $avatarImageStorageInfoRepo->getById($avatar_image_id);

        // Hard to test: defensive path when profile has avatar_image_id but storage has no record (data inconsistency).
        if ($fileDetails === null) {
            return new \Bristolian\Response\StoredFileErrorResponse($avatar_image_id);
        }

        $normalized_name = $fileDetails->normalized_name;
        try {
            ensureFileCachedFromStream($localCacheFilesystem, $avatarFilesystem, $normalized_name);
        }
        // Hard to test: requires forcing Flysystem to throw UnableToReadFile (e.g. missing file in object store or I/O failure).
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            return new \Bristolian\Response\StoredFileErrorResponse($normalized_name);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;
        $filenameToServe = realpath($localCacheFilename);

        // Hard to test: realpath() only returns false if the file is missing or path invalid after cache write; would require simulating filesystem/permission failure.
        if ($filenameToServe === false) {
            throw new \Bristolian\Exception\BristolianException(
                "Failed to retrieve avatar from object store [" . $normalized_name . "]."
            );
        }

        return new \Bristolian\Response\StreamingResponse(
            $filenameToServe
        );
    }

    public function showOwnProfile(
        \Bristolian\Session\AppSessionManager $appSessionManager,
        UserProfileRepo $userProfileRepo,
    ): string {

        $appSession = $appSessionManager->getCurrentAppSession();
        $user_id = $appSession->getUserId();

        // Get full user profile
        $user_profile = $userProfileRepo->getUserProfile($user_id);

        // Check if logged-in user is viewing their own profile
        $is_own_profile = true;

        // Prepare widget data - flatten the nested structure
        $data = [
            'user_id' => $user_id,
            'display_name' => $user_profile->getDisplayName(),
            'about_me' => $user_profile->getAboutMe(),
            'avatar_image_id' => $user_profile->getAvatarImageId(),
            'is_own_profile' => $is_own_profile
        ];

        [$error, $values] = convertToValue($data);
        $widget_json = json_encode_safe($values);
        $widget_data = htmlspecialchars($widget_json);


        $content = "<h1>User Profile</h1>";
        $content .= <<< HTML
<div class="user_profile_panel" data-widgety_json="$widget_data"></div>
HTML;

        return $content;
    }



    public function showUserProfile(
        \Bristolian\Session\AppSessionManager $appSessionManager,
        UserProfileRepo $userProfileRepo,
        string $user_id
    ): string {
        // Get full user profile
        $user_profile = $userProfileRepo->getUserProfile($user_id);
        
        // Check if logged-in user is viewing their own profile
        $is_own_profile = false;
        $appSession = $appSessionManager->getCurrentAppSession();
        if ($appSession && $appSession->getUserId() === $user_id) {
            $is_own_profile = true;
        }

        // Prepare widget data - flatten the nested structure
        $data = [
            'user_id' => $user_id,
            'display_name' => $user_profile->getDisplayName(),
            'about_me' => $user_profile->getAboutMe(),
            'avatar_image_id' => $user_profile->getAvatarImageId(),
            'is_own_profile' => $is_own_profile
        ];
        
        [$error, $values] = convertToValue($data);
        $widget_json = json_encode_safe($values);
        $widget_data = htmlspecialchars($widget_json);


        $content = "<h1>User Profile</h1>";
        $content .= <<< HTML
<div class="user_profile_panel" data-widgety_json="$widget_data"></div>
HTML;

        return $content;
    }

    public function updateProfile(
        UserSession $userSession,
        UserProfileRepo $userProfileRepo,
        UserProfileUpdateParams $params
    ): UpdateUserProfileResponse {
        // User can only update their own profile
        $updated_profile = $userProfileRepo->updateProfile(
            $userSession->getUserId(),
            $params
        );

        [$error, $values] = convertToValue($updated_profile);

        return new UpdateUserProfileResponse($values);
    }

    public function uploadAvatar(
        HandleAvatarUpload $handleAvatarUpload,
        UserSession $userSession,
    ): \SlimDispatcher\Response\StubResponse {
        $result = $handleAvatarUpload->handle(
            $userSession->getUserId(),
            self::AVATAR_FILE_UPLOAD_FORM_NAME
        );

        if ($result->errorResponse !== null) {
            return $result->errorResponse;
        }

        if ($result->ok === false) {
            return new UploadAvatarErrorResponse($result->error);
        }

        return new UploadAvatarResponse($result->avatarImageId);
    }

    public function getAvatarImage(
        \Bristolian\Filesystem\AvatarImageFilesystem $avatarFilesystem,
        \Bristolian\Filesystem\LocalCacheFilesystem $localCacheFilesystem,
        \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo $avatarImageStorageInfoRepo,
        string $avatar_image_id
    ): \Bristolian\Response\StreamingResponse|\Bristolian\Response\StoredFileErrorResponse {
        $fileDetails = $avatarImageStorageInfoRepo->getById($avatar_image_id);

        if ($fileDetails === null) {
            return new \Bristolian\Response\StoredFileErrorResponse($avatar_image_id);
        }

        $normalized_name = $fileDetails->normalized_name;
        try {
            ensureFileCachedFromStream($localCacheFilesystem, $avatarFilesystem, $normalized_name);
        }
        // Hard to test: requires forcing Flysystem to throw UnableToReadFile (e.g. missing file in object store or I/O failure).
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            return new \Bristolian\Response\StoredFileErrorResponse($normalized_name);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;
        $filenameToServe = realpath($localCacheFilename);

        // Hard to test: realpath() only returns false if the file is missing or path invalid after cache write; would require simulating filesystem/permission failure.
        if ($filenameToServe === false) {
            throw new \Bristolian\Exception\BristolianException(
                "Failed to retrieve avatar from object store [" . $normalized_name . "]."
            );
        }

        return new \Bristolian\Response\StreamingResponse(
            $filenameToServe
        );
    }
}
