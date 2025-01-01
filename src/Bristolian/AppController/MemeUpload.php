<?php

namespace Bristolian\AppController;

use Bristolian\Service\MemeStorage\MemeStorage;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploaderHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

class MemeUpload
{
    public const MEME_FILE_UPLOAD_FORM_NAME = "meme_file";

    public function handleFileUpload_get(): string
    {
        return "You probably meant to do a POST to this endpoint.";
    }

    public function handleFileUpload(
        MemeStorage $memeStorage,
        UserSession $appSession,
        UserSessionFileUploaderHandler $usfuh
    ): StubResponse {

        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }
//
//        if (file_exists($_FILES["myFile"]["tmp_name"]) !== true) {
//            $response = [
//                'result' => 'error',
//                'detail' => 'Temp file unreadable.'
//            ];
//
//            return new JsonNoCacheResponse($response, [], 500);
//        }
//
//        if ($_FILES["myFile"]["size"] > App::MAX_MEME_FILE_SIZE) {
//            $response = [
//                'result' => 'error',
//                'detail' => 'File size is large than max allowed size of ' . App::MAX_MEME_FILE_SIZE
//            ];
//
//            return new JsonNoCacheResponse($response, [], 406);
//        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->processFile("myFile");
        if ($fileOrResponse instanceof StubResponse) {
            return $fileOrResponse;
        }

        $response = [
            'result' => 'success',
            'next' => 'actually upload to file_server.'
        ];

        $memeStorage->storeMemeForUser(
            $appSession->getUserId(),
            $fileOrResponse
        );

        $response = new JsonNoCacheResponse($response);

        return $response;
    }
}
