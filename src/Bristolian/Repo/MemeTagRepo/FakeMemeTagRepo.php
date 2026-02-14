<?php

declare(strict_types = 1);

namespace Bristolian\Repo\MemeTagRepo;

use Bristolian\Model\Generated\MemeTag;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\Repo\MemeStorageRepo\MemeFileState;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of MemeTagRepo for testing.
 */
class FakeMemeTagRepo implements MemeTagRepo
{
    /**
     * @var array<string, array{id: string, user_id: string, meme_id: string, type: string, text: string, created_at: \DateTimeImmutable}>
     */
    private array $tags = [];

    /**
     * @var \Bristolian\Repo\MemeStorageRepo\MemeStorageRepo|null
     */
    private ?\Bristolian\Repo\MemeStorageRepo\MemeStorageRepo $memeStorageRepo = null;

    public function __construct(?\Bristolian\Repo\MemeStorageRepo\MemeStorageRepo $memeStorageRepo = null)
    {
        $this->memeStorageRepo = $memeStorageRepo;
    }

    public function addTagForMeme(
        string $user_id,
        MemeTagParams $memeTagParam,
    ): void {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $now = new \DateTimeImmutable();

        $this->tags[$id] = [
            'id' => $id,
            'user_id' => $user_id,
            'meme_id' => $memeTagParam->meme_id,
            'type' => $memeTagParam->type,
            'text' => $memeTagParam->text,
            'created_at' => $now,
        ];
    }

    /**
     * @param string $user_id
     * @param string $meme_id
     * @return MemeTag[]
     */
    public function getUserTagsForMeme(
        string $user_id,
        string $meme_id
    ): array {
        // Check if meme belongs to user (if memeStorageRepo is available)
        if ($this->memeStorageRepo !== null) {
            $meme = $this->memeStorageRepo->getMeme($meme_id);
            if ($meme === null || $meme->user_id !== $user_id) {
                return [];
            }
        }

        $result = [];
        foreach ($this->tags as $tag) {
            if ($tag['meme_id'] === $meme_id) {
                $result[] = new MemeTag(
                    $tag['id'],
                    $tag['user_id'],
                    $tag['meme_id'],
                    $tag['type'],
                    $tag['text'],
                    $tag['created_at']
                );
            }
        }

        return $result;
    }

    public function updateTagForUser(
        string $user_id,
        MemeTagUpdateParams $memeTagUpdateParams,
    ): int {
        if (!isset($this->tags[$memeTagUpdateParams->meme_tag_id])) {
            return 0;
        }

        $tag = $this->tags[$memeTagUpdateParams->meme_tag_id];

        // Check if meme belongs to user (if memeStorageRepo is available)
        if ($this->memeStorageRepo !== null) {
            $meme = $this->memeStorageRepo->getMeme($tag['meme_id']);
            if ($meme === null || $meme->user_id !== $user_id) {
                return 0;
            }
        }

        // Only allow updating user_tag type tags
        if ($tag['type'] !== MemeTagType::USER_TAG->value) {
            return 0;
        }

        // Update the tag text and type (type is forced to user_tag)
        $this->tags[$memeTagUpdateParams->meme_tag_id]['text'] = $memeTagUpdateParams->text;
        $this->tags[$memeTagUpdateParams->meme_tag_id]['type'] = MemeTagType::USER_TAG->value;

        return 1;
    }

    public function deleteTagForUser(
        string $user_id,
        string $meme_tag_id
    ): int {
        if (!isset($this->tags[$meme_tag_id])) {
            return 0;
        }

        $tag = $this->tags[$meme_tag_id];

        // Check if meme belongs to user (if memeStorageRepo is available)
        if ($this->memeStorageRepo !== null) {
            $meme = $this->memeStorageRepo->getMeme($tag['meme_id']);
            if ($meme === null || $meme->user_id !== $user_id) {
                return 0;
            }
        }

        // Only allow deleting user_tag type tags
        if ($tag['type'] !== MemeTagType::USER_TAG->value) {
            return 0;
        }

        unset($this->tags[$meme_tag_id]);

        return 1;
    }

    /**
     * @return array<array{text: string, count: int}>
     */
    public function getMostCommonTags(
        string $user_id,
        int $limit
    ): array {
        // Count tags for memes owned by the user
        $tagCounts = [];

        foreach ($this->tags as $tag) {
            // Check if meme belongs to user and is UPLOADED (if memeStorageRepo is available)
            if ($this->memeStorageRepo !== null) {
                $meme = $this->memeStorageRepo->getMeme($tag['meme_id']);
                if ($meme === null || $meme->user_id !== $user_id || $meme->state !== MemeFileState::UPLOADED->value) {
                    continue;
                }
            }

            // Only count user_tag type tags
            if ($tag['type'] !== MemeTagType::USER_TAG->value) {
                continue;
            }

            $text = $tag['text'];
            $tagCounts[$text] = ($tagCounts[$text] ?? 0) + 1;
        }

        // Sort by count descending, then by text ascending
        arsort($tagCounts);
        $sortedByCount = $tagCounts;

        // For tags with same count, sort by text
        $grouped = [];
        foreach ($sortedByCount as $text => $count) {
            $grouped[$count][] = $text;
        }

        $result = [];
        foreach ($grouped as $count => $texts) {
            sort($texts);
            foreach ($texts as $text) {
                $result[] = ['text' => $text, 'count' => $count];
            }
        }

        // Re-sort to maintain count order after text sorting
        usort($result, fn($a, $b) => $b['count'] <=> $a['count'] ?: strcmp($a['text'], $b['text']));

        return array_slice($result, 0, $limit);
    }

    /**
     * @return array<array{text: string, count: int}>
     */
    public function getMostCommonTagsForMemes(
        string $user_id,
        array $meme_ids,
        int $limit
    ): array {
        if (count($meme_ids) === 0) {
            return [];
        }

        $memeIdSet = array_flip($meme_ids);
        $tagCounts = [];

        foreach ($this->tags as $tag) {
            // Only include tags for the specified memes
            if (!isset($memeIdSet[$tag['meme_id']])) {
                continue;
            }

            // Check if meme belongs to user and is UPLOADED (if memeStorageRepo is available)
            if ($this->memeStorageRepo !== null) {
                $meme = $this->memeStorageRepo->getMeme($tag['meme_id']);
                if ($meme === null || $meme->user_id !== $user_id || $meme->state !== MemeFileState::UPLOADED->value) {
                    continue;
                }
            }

            // Only count user_tag type tags
            if ($tag['type'] !== MemeTagType::USER_TAG->value) {
                continue;
            }

            $text = $tag['text'];
            $tagCounts[$text] = ($tagCounts[$text] ?? 0) + 1;
        }

        // Sort by count descending, then by text ascending
        arsort($tagCounts);
        $sortedByCount = $tagCounts;

        // For tags with same count, sort by text
        $grouped = [];
        foreach ($sortedByCount as $text => $count) {
            $grouped[$count][] = $text;
        }

        $result = [];
        foreach ($grouped as $count => $texts) {
            sort($texts);
            foreach ($texts as $text) {
                $result[] = ['text' => $text, 'count' => $count];
            }
        }

        // Re-sort to maintain count order after text sorting
        usort($result, fn($a, $b) => $b['count'] <=> $a['count'] ?: strcmp($a['text'], $b['text']));

        return array_slice($result, 0, $limit);
    }
}
