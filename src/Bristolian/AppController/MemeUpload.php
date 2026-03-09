<?php

namespace Bristolian\AppController;

use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\MemeUploadErrorResponse;
use Bristolian\Response\MemeUploadSuccessResponse;
use Bristolian\Service\MemeStorageProcessor\HandleMemeUpload;
use Bristolian\Session\UserSession;
use SlimDispatcher\Response\StubResponse;

class MemeUpload
{
    public const MEME_FILE_UPLOAD_FORM_NAME = "meme_file";

    public function handleMemeUpload_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function handleMemeUpload(
        HandleMemeUpload $handleMemeUpload,
        UserSession $appSession,
    ): StubResponse {
        $result = $handleMemeUpload->handle(
            $appSession->getUserId(),
            self::MEME_FILE_UPLOAD_FORM_NAME
        );

        if ($result->errorResponse !== null) {
            return $result->errorResponse;
        }

        if ($result->ok === false) {
            return new MemeUploadErrorResponse($result->error);
        }

        return new MemeUploadSuccessResponse($result->meme);
    }
}
