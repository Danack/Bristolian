<?php

namespace Bristolian\Repo\MemeTextRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\meme_text;
use BristolianGenerated\Database\stored_meme;
use BristolianGenerated\Model\StoredMeme;

interface MemeTextRepo
{
    #[ReadsTable(stored_meme::class)]
    #[ReadsTable(meme_text::class)]
    public function getNextMemeToOCR(): StoredMeme|null;

    #[WritesTable(meme_text::class)]
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
    #[ReadsTable(stored_meme::class)]
    #[ReadsTable(meme_text::class)]
    public function searchMemeIdsByText(
        string $user_id,
        string $search_text
    ): array;

    /**
     * Gets the text for a meme (returns the most recent entry if multiple exist).
     *
     * @param string $meme_id
     * @return \BristolianGenerated\Model\MemeText|null
     */
    #[ReadsTable(meme_text::class)]
    public function getMemeText(string $meme_id): \BristolianGenerated\Model\MemeText|null;

    /**
     * Updates the text for a meme. If text exists, updates it; if not, inserts it.
     *
     * @param string $meme_id
     * @param string $text
     * @return void
     */
    #[ReadsTable(meme_text::class)]
    #[WritesTable(meme_text::class)]
    public function updateMemeText(string $meme_id, string $text): void;
}
