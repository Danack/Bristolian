<?php

namespace Bristolian\AppController;

use Bristolian\Repo\UserDocumentRepo\UserDocumentRepo;
use Bristolian\Repo\UserRepo\UserRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Session\UserSession;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Response\GetUserInfoResponse;
use Bristolian\Response\UpdateUserProfileResponse;
use Bristolian\Response\UploadAvatarResponse;

class Users
{
    public function index(UserRepo $userRepo): string
    {
        $contents = "<h1>User list</h1>";

        // TODO - we need need to have user ids for all users.
        // I need to go back through and 'normalise' a whole load of code.
        $template = "<a href='/users/:attr_user_id/:attr_username'>:html_username</a>";

        foreach ($userRepo->getUsers() as $user) {
            $params = [
//                ':attr_user_id' => $user->user_id,
                ':attr_username' => $user->username,
                ':html_username' => $user->username,
            ];

            $contents .= esprintf($template, $params);
        }

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
            'avatar_image_id' => $user_profile ? $user_profile->getAvatarImageId() : null,
        ];

        [$error, $values] = convertToValue($data);

        return new JsonResponse($values);
    }

    public function getUserInfo(
        UserProfileRepo $userProfileRepo,
        string $user_id
    ): GetUserInfoResponse|\SlimDispatcher\Response\HtmlResponse {
        $user_profile = $userProfileRepo->getUserProfile($user_id);
        
        if (!$user_profile) {
            return new \SlimDispatcher\Response\HtmlResponse(
                'User not found',
                [],
                404
            );
        }

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
        
        if (!$user_profile || !$user_profile->getAvatarImageId()) {
            return new \Bristolian\Response\StoredFileErrorResponse('No avatar for user: ' . $user_id);
        }

        $avatar_image_id = $user_profile->getAvatarImageId();
        $fileDetails = $avatarImageStorageInfoRepo->getById($avatar_image_id);

        if ($fileDetails === null) {
            return new \Bristolian\Response\StoredFileErrorResponse($avatar_image_id);
        }

        $normalized_name = $fileDetails->normalized_name;
        if ($localCacheFilesystem->fileExists($normalized_name) !== true) {
            try {
                $stream = $avatarFilesystem->readStream($normalized_name);
            }
            catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
                return new \Bristolian\Response\StoredFileErrorResponse($normalized_name);
            }
            $localCacheFilesystem->writeStream($normalized_name, $stream);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;
        $filenameToServe = realpath($localCacheFilename);

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
    ) {

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


    public function showUser(
        UserRepo $userRepo,
        UserDocumentRepo $userDocumentRepo,
        string $username
    ): string {
        $user = $userRepo->findUser($username);

        if ($user === null) {
            return "User not found.";
        }

        $documents = $userDocumentRepo->getUserDocuments($user);
        $contents = "<h1>User has these documents</h1>";
        $template = "<a href='/users/:uri_username/docs/:uri_link'>:html_title</a>";

        foreach ($documents as $document) {
            $params = [
                ':uri_username' => $user->username,
                ':uri_link' => slugify($document->title),
                ':html_title' => $document->title
            ];

            $contents .= esprintf($template, $params);
            $contents .= "<br/>";
        }

        $contents .= "<br/><br/><br/><br/><br/>";

        return $contents;
    }

    public function showUserDocument(
        UserRepo $userRepo,
        UserDocumentRepo $userDocumentRepo,
        string $username,
        string $title
    ): string {
        $user = $userRepo->findUser($username);

        if ($user === null) {
            return "User not found.";
        }

        return $userDocumentRepo->renderUserDocument($user, $title);
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
        UserSession $userSession,
        UserProfileRepo $userProfileRepo,
        \Bristolian\Service\AvatarImageStorage\AvatarImageStorage $avatarImageStorage,
        \Bristolian\UserUploadedFile\UserSessionFileUploadHandler $uploadHandler
    ): \SlimDispatcher\Response\StubResponse {
        
        // Get the uploaded file
        $fileOrResponse = $uploadHandler->fetchUploadedFile('avatar_file');
        if ($fileOrResponse instanceof \SlimDispatcher\Response\StubResponse) {
            return $fileOrResponse;
        }

        // Store the avatar image
        $avatarImageIdOrError = $avatarImageStorage->storeAvatarForUser(
            $userSession->getUserId(),
            $fileOrResponse,
            get_supported_avatar_image_extensions()
        );

        if ($avatarImageIdOrError instanceof \Bristolian\Service\AvatarImageStorage\UploadError) {
            return new \SlimDispatcher\Response\JsonNoCacheResponse(
                ['error' => $avatarImageIdOrError->error_message],
                [],
                400
            );
        }

        // Update the user profile with the new avatar ID
        $userProfileRepo->updateAvatarImage(
            $userSession->getUserId(),
            $avatarImageIdOrError
        );

        return new UploadAvatarResponse($avatarImageIdOrError);
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
        if ($localCacheFilesystem->fileExists($normalized_name) !== true) {
            try {
                $stream = $avatarFilesystem->readStream($normalized_name);
            }
            catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
                return new \Bristolian\Response\StoredFileErrorResponse($normalized_name);
            }
            $localCacheFilesystem->writeStream($normalized_name, $stream);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;
        $filenameToServe = realpath($localCacheFilename);

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
