<?php

namespace Bristolian\AppController;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Filesystem\LocalCacheFilesystem;
use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\StubResponse;

class Images
{
    public function __construct(
        private MemeFilesystem $memeFilesystem,
        private LocalCacheFilesystem $localCacheFilesystem
    ) {
    }

    public function show_meme(string $id): StubResponse
    {
        // validate the meme id
        $meme_filename = "018cb227-6119-712a-b1e2-47840e59c370-0.61385500 1703850685.jpeg";

        if ($this->localCacheFilesystem->fileExists($meme_filename) === true) {
            $contents = $this->localCacheFilesystem->read($meme_filename);
        }
        else {
            $contents = $this->memeFilesystem->read($meme_filename);
            $this->localCacheFilesystem->write($meme_filename, $contents);
        }

        // check file is available locally
        return new ImageResponse(
            $contents,
            ImageResponse::TYPE_JPG,
            []
        );
    }
}
