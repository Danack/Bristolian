<?php

declare(strict_types=1);

namespace Bristolian\Service\AvatarImageStorage;

use SlimDispatcher\Response\StubResponse;

final class UploadAvatarResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly ?string $avatarImageId,
        public readonly ?UploadError $error,
        public readonly ?StubResponse $errorResponse
    ) {
    }

    public static function success(string $avatarImageId): self
    {
        return new self(true, $avatarImageId, null, null);
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
