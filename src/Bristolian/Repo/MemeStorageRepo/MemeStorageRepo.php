<?php

namespace Bristolian\Repo\MemeStorageRepo;

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
    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    public function getMeme(string $id): Meme|null;

    public function getByNormalizedName(string $normalized_name): Meme|null;

    /**
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id): array;

    /**
     * Search memes for a user by tag text content and/or tag type.
     *
     * @param string $user_id
     * @param string|null $query Search query for tag text (uses LIKE matching)
     * @param string|null $tag_type Filter by tag type
     * @return Meme[]
     */
    public function searchMemesForUser(
        string $user_id,
        ?string $query,
        ?string $tag_type
    ): array;

    public function setUploaded(string $meme_id): void;

    public function markAsDeleted(string $meme_id): void;

    /**
     * Search memes for a user by exact tag texts (all tags must match).
     * Returns memes that have ALL of the specified tags.
     *
     * @param string $user_id
     * @param string[] $tagTexts Array of exact tag texts to search for
     * @return Meme[]
     */
    public function searchMemesByExactTags(
        string $user_id,
        array $tagTexts
    ): array;
}
