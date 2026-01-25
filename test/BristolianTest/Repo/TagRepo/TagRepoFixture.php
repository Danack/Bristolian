<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TagRepo;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\TagRepo\TagRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for TagRepo implementations.
 */
abstract class TagRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the TagRepo implementation.
     *
     * @return TagRepo
     */
    abstract public function getTestInstance(): TagRepo;

    /**
     * @covers \Bristolian\Repo\TagRepo\TagRepo::getAllTags
     */
    public function test_getAllTags_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $tags = $repo->getAllTags();

        // Note: PDO tests may have existing data, so we don't assert empty
        foreach ($tags as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
        }
    }

    /**
     * @covers \Bristolian\Repo\TagRepo\TagRepo::createTag
     */
    public function test_createTag_creates_and_stores_tag(): void
    {
        $repo = $this->getTestInstance();

        $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'test-tag',
            'description' => 'A test tag',
        ]));

        $tag = $repo->createTag($tagParam);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame('test-tag', $tag->getText());
        $this->assertSame('A test tag', $tag->getDescription());
    }

    /**
     * @covers \Bristolian\Repo\TagRepo\TagRepo::getAllTags
     * @covers \Bristolian\Repo\TagRepo\TagRepo::createTag
     */
    public function test_getAllTags_returns_all_created_tags(): void
    {
        $repo = $this->getTestInstance();

        $tagParam1 = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-1',
            'description' => 'First tag',
        ]));
        $tagParam2 = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-2',
            'description' => 'Second tag',
        ]));

        $tag1 = $repo->createTag($tagParam1);
        $tag2 = $repo->createTag($tagParam2);

        $tags = $repo->getAllTags();

        // Should contain at least the 2 tags we created
        $this->assertGreaterThanOrEqual(2, count($tags));
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        
        // Verify our created tags are in the result
        $tagTexts = array_map(fn(Tag $t) => $t->getText(), $tags);
        $this->assertContains('tag-1', $tagTexts);
        $this->assertContains('tag-2', $tagTexts);
    }
}
