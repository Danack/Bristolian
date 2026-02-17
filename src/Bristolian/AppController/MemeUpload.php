<?php

namespace Bristolian\AppController;

use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\StubResponse;

class MemeUpload
{
    public const MEME_FILE_UPLOAD_FORM_NAME = "meme_file";

    public function handleMemeUpload_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function handleMemeUpload(
        MemeStorageProcessor         $memeStorageProcessor,
        UserSession                  $appSession,
        UserSessionFileUploadHandler $usfuh,
        MemeObjectStore              $memeObjectStore,
    ): StubResponse {

//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->fetchUploadedFile(self::MEME_FILE_UPLOAD_FORM_NAME);
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
                'error' => $storedFileOrError->error_message,
            ];
            
            // Include error code and data if available (for duplicate filename errors)
            if ($storedFileOrError->error_code !== null) {
                $data['error_code'] = $storedFileOrError->error_code;
            }
            if ($storedFileOrError->error_data !== null) {
                $data['error_data'] = $storedFileOrError->error_data;
            }
            
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
