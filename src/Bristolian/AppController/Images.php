<?php

namespace Bristolian\AppController;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Response\StoredFileErrorResponse;
use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\StubResponse;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Exception\ContentNotFoundException;


class Images
{
    public function __construct(
        private MemeStorageRepo $memeStorageRepo,
        private MemeFilesystem $memeFilesystem,
        private LocalCacheFilesystem $localCacheFilesystem
    ) {
    }

    public function show_meme(string $id): StubResponse|string
    {
        $meme = $this->memeStorageRepo->getMeme($id);

        if ($meme === null) {
            return ContentNotFoundException::meme_id_not_found($id);
        }

        $normalized_name = $meme->normalized_name;
        try {
            $contents = ensureFileCachedFromString(
                $this->localCacheFilesystem,
                $this->memeFilesystem,
                $normalized_name
            );
        }
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            return new StoredFileErrorResponse($normalized_name);
        }

        // check file is available locally
        return new ImageResponse(
            $contents,
            // TODO - type isn't guaranteed to be JPG
            ImageResponse::TYPE_JPG,
            []
        );
    }
}
