<?php

namespace Bristolian\Repo\MemeTagRepo;

use Bristolian\Parameters\MemeTagParams;

interface MemeTagRepo
{
    public function addTagForMeme(
        string        $user_id,
        MemeTagParams $memeTagParam,
    ): void;

    /**
     * @param string $user_id
     * @param string $meme_id
     * @return array<int, string>
     */
    public function getUserTagsForMeme(
        string $user_id,
        string $meme_id
    ): array;

    public function deleteTagForUser(
        string $user_id,
        string $meme_id
    ): int;
}
