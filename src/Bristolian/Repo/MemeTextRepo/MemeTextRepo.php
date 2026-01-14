<?php

namespace Bristolian\Repo\MemeTextRepo;


use Bristolian\Model\Generated\StoredMeme;


interface MemeTextRepo
{
    public function getNextMemeToOCR(): StoredMeme|null;

    public function saveMemeText(
        StoredMeme $storedMeme,
        string $found_text
    ): void;

    /**
     * Search for meme IDs by text content (case-insensitive).
     * 
     * @param string $user_id
     * @param string $search_text
     * @return array<string> Array of meme IDs
     */
    public function searchMemeIdsByText(
        string $user_id,
        string $search_text
    ): array;
}


