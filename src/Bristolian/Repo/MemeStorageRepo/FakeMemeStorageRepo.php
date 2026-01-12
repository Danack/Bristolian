<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Model\Meme;
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
        return null;
    }


    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

//        $this->storedMemes[$id] = new Meme(
//            $id,
//            $normalized_filename,
//            $original_filename = $uploadedFile->getOriginalName(),
//            $state = MemeFileState::INITIAL->value,
//            $size = $uploadedFile->getSize(),
//            $user_id,
//        );

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

//        $meme = $this->storedMemes[$meme_id];
//
//        $this->storedMemes[$meme_id] = new Meme(
//            $meme->id,
//            $meme->normalized_name,
//            $meme->original_filename,
//            $meme->state,
//            $meme->size,
//            $meme->user_id,
//        );
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
        throw new \Exception("Implement searchMemesByExactTags() method.");
    }
}
