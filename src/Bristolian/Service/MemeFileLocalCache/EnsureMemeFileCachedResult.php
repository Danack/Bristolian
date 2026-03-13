<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeFileLocalCache;

/**
 * Outcome of copying a meme object-store file into the local OCR cache.
 */
final class EnsureMemeFileCachedResult
{
    public function __construct(
        public readonly bool $succeeded,
        public readonly ?string $failureDebugInfo = null,
    ) {
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $debugInfo): self
    {
        return new self(false, $debugInfo);
    }
}
