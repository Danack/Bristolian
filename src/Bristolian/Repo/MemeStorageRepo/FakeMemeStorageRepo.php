<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Model\Types\Meme;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeMemeStorageRepo implements MemeStorageRepo
{
    /**
     * @var Meme[]
     */
    private array $storedMemes = [];

    public function getMeme(string $id): Meme|null
    {
        return $this->storedMemes[$id] ?? null;
    }

    public function getByNormalizedName(string $normalized_name): Meme|null
    {
        foreach ($this->storedMemes as $storedMeme) {
            if ($storedMeme->normalized_name === $normalized_name) {
                return $storedMeme;
            }
        }
        
        return null;
    }

    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $datetime = new \DateTimeImmutable();

        $this->storedMemes[$id] = new Meme(
            $id,
            $user_id,
            $normalized_filename,
            $original_filename = $uploadedFile->getOriginalName(),
            $state = MemeFileState::INITIAL->value,
            $size = $uploadedFile->getSize(),
            $created_at = $datetime,
            $deleted = false
        );

        return $id;
    }

    /**
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id): array
    {
        $memes_for_user = [];
        foreach ($this->storedMemes as $storedMeme) {
            if ($storedMeme->user_id === $user_id) {
                $memes_for_user[] = $storedMeme;
            }
        }

        return $memes_for_user;
    }

    /**
     * @return Meme[]
     */
    public function listAllMemes(): array
    {
        $all = [];
        foreach ($this->storedMemes as $meme) {
            if (!$meme->deleted) {
                $all[] = $meme;
            }
        }
        return $all;
    }

    /**
     * @return Meme[]
     */
    public function listMemesForUserWithNoTags(string $user_id): array
    {
        // Fake has no tag data; return all memes for user so tests can exercise untagged flow
        return $this->listMemesForUser($user_id);
    }

    /**
     * @return Meme[]
     */
    public function searchMemesForUser(
        string $user_id,
        ?string $query,
        ?string $tag_type
    ): array {
        // Fake implementation just returns all memes for user
        // In a real test scenario, you'd want to set up tag data and filter accordingly
        return $this->listMemesForUser($user_id);
    }

    public function setUploaded(string $meme_id): void
    {
        if (array_key_exists($meme_id, $this->storedMemes) === false) {
            throw new BristolianException("meme not found to set as uploaded.");
        }

        $meme = $this->storedMemes[$meme_id];

        $this->storedMemes[$meme_id] = new Meme(
            $meme->id,
            $meme->user_id,
            $meme->normalized_name,
            $meme->original_filename,
            $state = MemeFileState::UPLOADED->value,
            $meme->size,
            $meme->created_at,
            $meme->deleted
        );
    }

    /**
     * @return Meme[]
     */
    public function getStoredMeme(): array
    {
        return $this->storedMemes;
    }

    public function searchMemesByExactTags(string $user_id, array $tagTexts): array
    {
        // Fake implementation: return all memes for user if no tags specified
        // For a full implementation, this would need to check tags via MemeTagRepo
        if (empty($tagTexts)) {
            return $this->listMemesForUser($user_id);
        }
        
        // Basic implementation: return empty array when tags are specified
        // A complete fake would need integration with FakeMemeTagRepo
        return [];
    }

    public function markAsDeleted(string $meme_id): void
    {
        if (array_key_exists($meme_id, $this->storedMemes) === false) {
            throw new BristolianException("meme not found to mark as deleted.");
        }

        $meme = $this->storedMemes[$meme_id];

        $this->storedMemes[$meme_id] = new Meme(
            $meme->id,
            $meme->user_id,
            $meme->normalized_name,
            $meme->original_filename,
            $meme->state,
            $meme->size,
            $meme->created_at,
            $deleted = true
        );
    }

    public function getMemeByOriginalFilename(string $user_id, string $original_filename): Meme|null
    {
        foreach ($this->storedMemes as $storedMeme) {
            if ($storedMeme->user_id === $user_id &&
                $storedMeme->original_filename === $original_filename &&
                $storedMeme->deleted === false) {
                return $storedMeme;
            }
        }
        
        return null;
    }
}
