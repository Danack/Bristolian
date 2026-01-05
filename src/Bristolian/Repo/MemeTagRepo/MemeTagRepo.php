<?php

namespace Bristolian\Repo\MemeTagRepo;

use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;

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

    public function updateTagForUser(
        string $user_id,
        MemeTagUpdateParams $memeTagUpdateParams,
    ): int;

    public function deleteTagForUser(
        string $user_id,
        string $meme_id
    ): int;

    /**
     * Get the most common tags for a user's memes.
     * 
     * @param string $user_id
     * @param int $limit Maximum number of tags to return
     * @return array<array{text: string, count: int}> Array of tags with their counts, sorted by count descending
     */
    public function getMostCommonTags(
        string $user_id,
        int $limit
    ): array;

    /**
     * Get the most common tags that are shared by the specified memes.
     * 
     * @param string $user_id
     * @param string[] $meme_ids Array of meme IDs to find common tags for
     * @param int $limit Maximum number of tags to return
     * @return array<array{text: string, count: int}> Array of tags with their counts, sorted by count descending
     */
    public function getMostCommonTagsForMemes(
        string $user_id,
        array $meme_ids,
        int $limit
    ): array;
}
