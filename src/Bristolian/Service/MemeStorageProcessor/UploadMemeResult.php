<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeStorageProcessor;

use SlimDispatcher\Response\StubResponse;

final class UploadMemeResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly ?ObjectStoredMeme $meme,
        public readonly ?UploadError $error,
        public readonly ?StubResponse $errorResponse
    ) {
    }

    public static function success(ObjectStoredMeme $meme): self
    {
        return new self(true, $meme, null, null);
    }

    public static function failure(UploadError $error): self
    {
        return new self(false, null, $error, null);
    }

    public static function failureResponse(StubResponse $response): self
    {
        return new self(false, null, null, $response);
    }
}
