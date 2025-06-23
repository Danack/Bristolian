<?php

namespace Bristolian\UserUploadedFile;

use Bristolian\App;
use Bristolian\Session\UserSession;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

class UserSessionFileUploaderHandler
{
    public function __construct(
        private UserSession $appSession,
        private UploadedFiles $uploadedFiles
    ) {
    }

    public function processFile(string $formFileName): StubResponse|UploadedFile
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
