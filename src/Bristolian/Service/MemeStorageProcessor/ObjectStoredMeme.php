<?php

namespace Bristolian\Service\MemeStorageProcessor;

class ObjectStoredMeme
{
    public function __construct(
        public readonly string $normalized_filename,
        public readonly string $meme_id
    ) {
    }
}
