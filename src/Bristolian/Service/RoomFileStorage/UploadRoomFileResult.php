<?php

declare(strict_types=1);

namespace Bristolian\Service\RoomFileStorage;

use SlimDispatcher\Response\StubResponse;

final class UploadRoomFileResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly ?string $fileId,
        public readonly ?UploadError $error,
        public readonly ?StubResponse $errorResponse
    ) {
    }

    public static function success(string $fileId): self
    {
        return new self(true, $fileId, null, null);
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
