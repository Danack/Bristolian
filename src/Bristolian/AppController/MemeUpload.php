<?php

namespace Bristolian\AppController;

use Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploaderHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

class MemeUpload
{
    public const MEME_FILE_UPLOAD_FORM_NAME = "meme_file";

    public function handleMemeUpload_get(): string
    {
        return "You probably meant to do a POST to this endpoint.";
    }

    public function handleMemeUpload(
        MemeStorageProcessor $memeStorageProcessor,
        UserSession $appSession,
        UserSessionFileUploaderHandler $usfuh,
        MemeObjectStore $memeObjectStore,
    ): StubResponse {

        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->processFile(self::MEME_FILE_UPLOAD_FORM_NAME);
        if ($fileOrResponse instanceof StubResponse) {
            return $fileOrResponse;
        }

        $storedFileOrError = $memeStorageProcessor->storeMemeForUser(
            $appSession->getUserId(),
            $fileOrResponse,
            get_supported_meme_file_extensions(),
            $memeObjectStore
        );

        if ($storedFileOrError instanceof UploadError) {
            $data = [
                'result' => 'error',
                'error' => $storedFileOrError->error_message
            ];
            // todo - change to helper function
            return new JsonNoCacheResponse($data, [], 400);
        }

        $response = [
            'result' => 'success',
            'next' => 'actually upload to file_server.',
            'meme_id' => $storedFileOrError->meme_id
        ];

        $response = new JsonNoCacheResponse($response);

        return $response;
    }
}
