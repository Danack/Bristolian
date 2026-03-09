<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeStorageProcessor;

use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\StubResponse;

final class HandleMemeUpload
{
    public function __construct(
        private MemeStorageProcessor $memeStorageProcessor,
        private UserSessionFileUploadHandler $uploadHandler,
        private MemeObjectStore $memeObjectStore
    ) {
    }

    public function handle(string $userId, string $formFieldName): UploadMemeResult
    {
        $fileOrResponse = $this->uploadHandler->fetchUploadedFile($formFieldName);

        if ($fileOrResponse instanceof StubResponse) {
            return UploadMemeResult::failureResponse($fileOrResponse);
        }

        $storedFileOrError = $this->memeStorageProcessor->storeMemeForUser(
            $userId,
            $fileOrResponse,
            get_supported_meme_file_extensions(),
            $this->memeObjectStore
        );

        if ($storedFileOrError instanceof UploadError) {
            return UploadMemeResult::failure($storedFileOrError);
        }

        return UploadMemeResult::success($storedFileOrError);
    }
}
