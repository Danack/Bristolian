<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TagRepo;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\TagRepo\FakeTagRepo;
use Bristolian\Repo\TagRepo\TagRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeTagRepoFixture extends TagRepoFixture
{
    public function getTestInstance(): TagRepo
    {
        return new FakeTagRepo();
    }

    /**
     * Test FakeTagRepo-specific constructor behavior
     *
     * @covers \Bristolian\Repo\TagRepo\FakeTagRepo::__construct
     */
    public function test_constructor_accepts_initial_tags(): void
    {
        $tag1 = Tag::fromParam('id1', new TagParams('tag-1', 'First'));
        $tag2 = Tag::fromParam('id2', new TagParams('tag-2', 'Second'));

        $repo = new FakeTagRepo([$tag1, $tag2]);

        $tags = $repo->getAllTags();
        $this->assertCount(2, $tags);
        $this->assertSame('id1', $tag1->getTagId());
        $this->assertSame('id2', $tag2->getTagId());
    }
}
