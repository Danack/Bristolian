<?php

declare(strict_types = 1);

namespace Bristolian\Repo\TagRepo;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of TagRepo for testing.
 */
class FakeTagRepo implements TagRepo
{
    /**
     * @var Tag[]
     */
    private array $tags = [];

    /**
     * @param Tag[] $initialTags
     */
    public function __construct(array $initialTags = [])
    {
        foreach ($initialTags as $tag) {
            $this->tags[$tag->getTagId()] = $tag;
        }
    }

    public function createTag(TagParams $tagParam): Tag
    {
        $uuid = Uuid::uuid7();
        $tag_id = $uuid->toString();

        $tag = Tag::fromParam($tag_id, $tagParam);
        $this->tags[$tag_id] = $tag;

        return $tag;
    }

    /**
     * @return Tag[]
     */
    public function getAllTags(): array
    {
        return array_values($this->tags);
    }
}
