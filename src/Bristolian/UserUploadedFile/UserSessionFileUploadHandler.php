<?php

namespace Bristolian\UserUploadedFile;

use Bristolian\App;
use Bristolian\Session\UserSession;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

/**
 * This class is aware of UserSession, to prevent non-logged in users from being
 * able to upload files. Presumably.
 */
class UserSessionFileUploadHandler
{
    public function __construct(
        private UserSession $appSession,
        private UploadedFiles $uploadedFiles
    ) {
    }

    /**
     * @param string $formFileName
     * @return StubResponse|UploadedFile
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function fetchUploadedFile(string $formFileName): StubResponse|UploadedFile
    {
        if ($this->appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        $uploadedFile = $this->uploadedFiles->get($formFileName);

        if ($uploadedFile === null) {
            $response = [
                'result' => 'error',
                'detail' => 'Temp file not found.'
            ];

            return new JsonNoCacheResponse($response, [], 500);
        }

        if (file_exists($uploadedFile->getTmpName()) !== true) {
            $response = [
                'result' => 'error',
                'detail' => $uploadedFile->getErrorMessage()
            ];

            return new JsonNoCacheResponse($response, [], 500);
        }

        if ($uploadedFile->getSize() > App::MAX_MEME_FILE_SIZE) {
            $response = [
                'result' => 'error',
                'detail' => 'File size is large than max allowed size of ' . App::MAX_MEME_FILE_SIZE
            ];

            return new JsonNoCacheResponse($response, [], 406);
        }

        return $uploadedFile;
    }
}
