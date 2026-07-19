<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\meme_tag;
use Bristolian\Database\stored_meme;
use Bristolian\Model\Types\Meme;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about a meme file in the local database.
 * The actual file will be stored in an object store.
 */
interface MemeStorageRepo
{
    /**
     * Stores information about a file that a user is uploading.
     * This happens before the file is put in the object store.
     *
     * @param string $normalized_filename
     * @param UploadedFile $uploadedFile
     * @return string The 'file_storage_id'
     */
    #[WritesTable(stored_meme::class)]
    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    #[ReadsTable(stored_meme::class)]
    public function getMeme(string $id): Meme|null;

    #[ReadsTable(stored_meme::class)]
    public function getByNormalizedName(string $normalized_name): Meme|null;

    /**
     * @return Meme[]
     */
    #[ReadsTable(stored_meme::class)]
    public function listMemesForUser(string $user_id): array;

    /**
     * List all non-deleted memes in uploaded state (for admin checks e.g. storage consistency).
     *
     * @return Meme[]
     */
    #[ReadsTable(stored_meme::class)]
    public function listAllMemes(): array;

    /**
     * List memes for a user that have no user_tag tags (so the user can add some).
     *
     * @return Meme[]
     */
    #[ReadsTable(stored_meme::class)]
    #[ReadsTable(meme_tag::class)]
    public function listMemesForUserWithNoTags(string $user_id): array;

    /**
     * Search memes for a user by tag text content and/or tag type.
     *
     * @param string $user_id
     * @param string|null $query Search query for tag text (uses LIKE matching)
     * @param string|null $tag_type Filter by tag type
     * @return Meme[]
     */
    #[ReadsTable(stored_meme::class)]
    #[ReadsTable(meme_tag::class)]
    public function searchMemesForUser(
        string $user_id,
        ?string $query,
        ?string $tag_type
    ): array;

    #[WritesTable(stored_meme::class)]
    public function setUploaded(string $meme_id): void;

    #[WritesTable(stored_meme::class)]
    public function markAsDeleted(string $meme_id): void;

    /**
     * Search memes for a user by exact tag texts (all tags must match).
     * Returns memes that have ALL of the specified tags.
     *
     * @param string $user_id
     * @param string[] $tagTexts Array of exact tag texts to search for
     * @return Meme[]
     */
    #[ReadsTable(meme_tag::class)]
    #[ReadsTable(stored_meme::class)]
    public function searchMemesByExactTags(
        string $user_id,
        array $tagTexts
    ): array;

    /**
     * Check if a user already has a meme with the given original filename.
     * Only checks non-deleted memes.
     *
     * @param string $user_id
     * @param string $original_filename
     * @return Meme|null Returns the existing meme if found, null otherwise
     */
    #[ReadsTable(stored_meme::class)]
    public function getMemeByOriginalFilename(string $user_id, string $original_filename): Meme|null;
}
