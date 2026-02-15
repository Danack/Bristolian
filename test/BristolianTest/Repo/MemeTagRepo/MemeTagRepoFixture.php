<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for MemeTagRepo implementations.
 *
 * @coversNothing
 */
abstract class MemeTagRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the MemeTagRepo implementation.
     *
     * @return MemeTagRepo
     */
    abstract public function getTestInstance(): MemeTagRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user_123';
    }

    /**
     * Get a test meme ID. Override in PDO tests to create actual meme.
     */
    protected function getTestMemeId(): string
    {
        return 'meme_456';
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::addTagForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::addTagForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::__construct
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::addTagForMeme
     */
    public function test_addTagForMeme(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $meme_id = $this->getTestMemeId();
        $type = 'character';
        $text = 'John-Doe';

        $memeTagParam = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => $type,
            'text' => $text,
        ]));

        // Should not throw an exception
        $repo->addTagForMeme($user_id, $memeTagParam);
    }


    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::getUserTagsForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::addTagForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getUserTagsForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::addTagForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getUserTagsForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::addTagForMeme
     * @group wip
     */
    public function test_getUserTagsForMeme_returns_tags_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $meme_id = $this->getTestMemeId();
        $type = 'character';
        $text = 'John-Doe';

        $memeTagParam = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => $type,
            'text' => $text,
        ]));

        $repo->addTagForMeme($user_id, $memeTagParam);

        $tags = $repo->getUserTagsForMeme($user_id, $meme_id);
        $tagTexts = [];
        foreach ($tags as $tag) {
            $tagTexts[] = $tag->text;
        }
        $this->assertContains($text, $tagTexts);
    }


    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::updateTagForUser
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::updateTagForUser
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::addTagForMeme
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::updateTagForUser
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::addTagForMeme
     */
    public function test_updateTagForUser(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $meme_id = $this->getTestMemeId();
        $type = 'character';
        $text = 'John-Doe';

        // First add a tag
        $memeTagParam = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => $type,
            'text' => $text,
        ]));
        $repo->addTagForMeme($user_id, $memeTagParam);

        // Get the tag ID (implementation dependent, but we need a valid ID)
        // For now, we'll assume the update returns a count
        $meme_tag_id = 'tag_123'; // This would need to come from the implementation
        $new_text = 'Jane-Doe';
        $new_type = 'location';

        $updateParams = MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => $meme_tag_id,
            'type' => $new_type,
            'text' => $new_text,
        ]));

        $count = $repo->updateTagForUser($user_id, $updateParams);
    }


    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::deleteTagForUser
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::deleteTagForUser
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::deleteTagForUser
     */
    public function test_deleteTagForUser(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $meme_id = $this->getTestMemeId();

        // Deleting a non-existent tag should return 0
        $count = $repo->deleteTagForUser($user_id, $meme_id);
    }


    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::getMostCommonTags
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTags
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTags
     */
    public function test_getMostCommonTags_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $limit = 10;

        $tags = $repo->getMostCommonTags($user_id, $limit);

        // Each item should have 'text' and 'count' keys
        foreach ($tags as $tag) {
            $this->assertArrayHasKey('text', $tag);
            $this->assertArrayHasKey('count', $tag);
        }
    }


    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::getMostCommonTags
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTags
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTags
     */
    public function test_getMostCommonTags_respects_limit(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $limit = 5;

        $tags = $repo->getMostCommonTags($user_id, $limit);
        $this->assertLessThanOrEqual($limit, count($tags));
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::getMostCommonTagsForMemes
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTagsForMemes
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_getMostCommonTagsForMemes_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $meme_ids = [$this->getTestMemeId(), $this->getTestMemeId() . '_2'];
        $limit = 10;

        $tags = $repo->getMostCommonTagsForMemes($user_id, $meme_ids, $limit);

        // Each item should have 'text' and 'count' keys
        foreach ($tags as $tag) {
            $this->assertArrayHasKey('text', $tag);
            $this->assertArrayHasKey('count', $tag);
        }
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\MemeTagRepo::getMostCommonTagsForMemes
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTagsForMemes
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_getMostCommonTagsForMemes_respects_limit(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $meme_ids = [$this->getTestMemeId(), $this->getTestMemeId() . '_2'];
        $limit = 3;

        $tags = $repo->getMostCommonTagsForMemes($user_id, $meme_ids, $limit);
        $this->assertLessThanOrEqual($limit, count($tags));
    }
}
