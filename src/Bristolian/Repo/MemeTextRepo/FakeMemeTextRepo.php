<?php

declare(strict_types = 1);

namespace Bristolian\Repo\MemeTextRepo;

use Bristolian\Model\Generated\MemeText;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Types\Meme;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;

/**
 * Fake implementation of MemeTextRepo for testing.
 * Requires MemeStorageRepo dependency to find memes for OCR.
 */
class FakeMemeTextRepo implements MemeTextRepo
{
    /**
     * @var MemeText[]
     * Keyed by meme_id, with most recent entry taking precedence
     */
    private array $memeTexts = [];

    /**
     * @var MemeText[]
     * All texts stored, ordered by creation
     */
    private array $allTexts = [];

    private int $nextTextId = 1;

    public function __construct(
        private MemeStorageRepo $memeStorageRepo
    ) {
    }

    public function getNextMemeToOCR(): StoredMeme|null
    {
        // Get all memes from storage - FakeMemeStorageRepo exposes getStoredMeme() for testing
        if (!method_exists($this->memeStorageRepo, 'getStoredMeme')) {
            // If not FakeMemeStorageRepo, can't iterate all memes - return null
            // In tests, FakeMemeStorageRepo will be used
            return null;
        }
        
        /** @var Meme[] $allMemes */
        $allMemes = $this->memeStorageRepo->getStoredMeme();
        
        // Filter: memes without text, not deleted
        $candidates = [];
        foreach ($allMemes as $meme) {
            if ($meme->deleted) {
                continue;
            }
            
            // Check if meme has text
            if (!isset($this->memeTexts[$meme->id])) {
                $candidates[] = $meme;
            }
        }
        
        if (empty($candidates)) {
            return null;
        }
        
        // Sort by created_at ascending (oldest first)
        usort($candidates, function (Meme $a, Meme $b) {
            return $a->created_at <=> $b->created_at;
        });
        
        // Convert first Meme to StoredMeme
        $meme = $candidates[0];
        return new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
    }

    public function saveMemeText(
        StoredMeme $storedMeme,
        string $found_text
    ): void {
        $now = new \DateTimeImmutable();
        
        $memeText = new MemeText(
            id: $this->nextTextId++,
            text: $found_text,
            meme_id: $storedMeme->id,
            created_at: $now,
        );
        
        $this->memeTexts[$storedMeme->id] = $memeText;
        $this->allTexts[] = $memeText;
    }

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
    ): array {
        $searchLower = strtolower($search_text);
        $result = [];
        
        foreach ($this->memeTexts as $meme_id => $memeText) {
            // Check if meme belongs to user
            $meme = $this->memeStorageRepo->getMeme($meme_id);
            if ($meme === null || $meme->user_id !== $user_id || $meme->deleted) {
                continue;
            }
            
            // Check if text contains search text (case-insensitive)
            if (str_contains(strtolower($memeText->text), $searchLower)) {
                $result[] = $meme_id;
            }
        }
        
        return array_unique($result);
    }

    /**
     * Gets the text for a meme (returns the most recent entry if multiple exist).
     */
    public function getMemeText(string $meme_id): MemeText|null
    {
        return $this->memeTexts[$meme_id] ?? null;
    }

    /**
     * Updates the text for a meme. If text exists, updates it; if not, inserts it.
     */
    public function updateMemeText(string $meme_id, string $text): void
    {
        $now = new \DateTimeImmutable();
        
        if (isset($this->memeTexts[$meme_id])) {
            // Update existing - create new entry (immutable)
            $memeText = new MemeText(
                id: $this->nextTextId++,
                text: $text,
                meme_id: $meme_id,
                created_at: $now,
            );
            $this->memeTexts[$meme_id] = $memeText;
            $this->allTexts[] = $memeText;
        } else {
            // Insert new
            $memeText = new MemeText(
                id: $this->nextTextId++,
                text: $text,
                meme_id: $meme_id,
                created_at: $now,
            );
            $this->memeTexts[$meme_id] = $memeText;
            $this->allTexts[] = $memeText;
        }
    }
}
