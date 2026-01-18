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

    /**
     * Gets the text for a meme (returns the most recent entry if multiple exist).
     *
     * @param string $meme_id
     * @return \Bristolian\Model\Generated\MemeText|null
     */
    public function getMemeText(string $meme_id): \Bristolian\Model\Generated\MemeText|null;

    /**
     * Updates the text for a meme. If text exists, updates it; if not, inserts it.
     *
     * @param string $meme_id
     * @param string $text
     * @return void
     */
    public function updateMemeText(string $meme_id, string $text): void;
}
