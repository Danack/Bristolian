<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\UploadedFiles\UploadedFile;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeMemeTagRepoTest extends MemeTagRepoFixture
{
    public function getTestInstance(): MemeTagRepo
    {
        return new FakeMemeTagRepo();
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getUserTagsForMeme
     */
    public function test_getUserTagsForMeme_returns_empty_when_meme_not_in_storage(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => 'nonexistent-meme',
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'tag1',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), 'nonexistent-meme');
        $this->assertSame([], $tags);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getUserTagsForMeme
     */
    public function test_getUserTagsForMeme_returns_empty_when_meme_belongs_to_other_user(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $meme_id = $memeStorageRepo->storeMeme('other-user', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'tag1',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $this->assertSame([], $tags);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::updateTagForUser
     */
    public function test_updateTagForUser_returns_zero_for_nonexistent_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $count = $repo->updateTagForUser($this->getTestUserId(), MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => 'nonexistent-tag-id',
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'new_text',
        ])));
        $this->assertSame(0, $count);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::updateTagForUser
     */
    public function test_updateTagForUser_returns_zero_when_tag_type_is_not_user_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $meme_id = $this->getTestMemeId();
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => 'character',
            'text' => 'original',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $this->assertCount(1, $tags);
        $tag_id = $tags[0]->id;
        $count = $repo->updateTagForUser($this->getTestUserId(), MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => $tag_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'updated',
        ])));
        $this->assertSame(0, $count);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::updateTagForUser
     */
    public function test_updateTagForUser_updates_when_tag_is_user_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $meme_id = $this->getTestMemeId();
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'original',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $tag_id = $tags[0]->id;
        $count = $repo->updateTagForUser($this->getTestUserId(), MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => $tag_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'updated_text',
        ])));
        $this->assertSame(1, $count);
        $tagsAfter = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $this->assertSame('updated_text', $tagsAfter[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::deleteTagForUser
     */
    public function test_deleteTagForUser_returns_zero_for_nonexistent_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $count = $repo->deleteTagForUser($this->getTestUserId(), 'nonexistent-tag-id');
        $this->assertSame(0, $count);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::deleteTagForUser
     */
    public function test_deleteTagForUser_returns_zero_when_tag_type_is_not_user_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $meme_id = $this->getTestMemeId();
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => 'character',
            'text' => 'tag1',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $tag_id = $tags[0]->id;
        $count = $repo->deleteTagForUser($this->getTestUserId(), $tag_id);
        $this->assertSame(0, $count);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::deleteTagForUser
     */
    public function test_deleteTagForUser_removes_tag_when_tag_is_user_tag(): void
    {
        $repo = new FakeMemeTagRepo();
        $meme_id = $this->getTestMemeId();
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'to-delete',
        ])));
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id);
        $tag_id = $tags[0]->id;
        $count = $repo->deleteTagForUser($this->getTestUserId(), $tag_id);
        $this->assertSame(1, $count);
        $this->assertSame([], $repo->getUserTagsForMeme($this->getTestUserId(), $meme_id));
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTags
     */
    public function test_getMostCommonTags_returns_tags_sorted_by_count_without_storage(): void
    {
        $repo = new FakeMemeTagRepo();
        $meme_id = $this->getTestMemeId();
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'common',
        ])));
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'common',
        ])));
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'once',
        ])));
        $result = $repo->getMostCommonTags($this->getTestUserId(), 10);
        $this->assertCount(2, $result);
        $this->assertSame('common', $result[0]['text']);
        $this->assertSame(2, $result[0]['count']);
        $this->assertSame('once', $result[1]['text']);
        $this->assertSame(1, $result[1]['count']);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTags
     */
    public function test_getMostCommonTags_only_counts_uploaded_memes_when_storage_set(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $memeStorageRepo->setUploaded($meme_id);
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'uploaded-tag',
        ])));
        $result = $repo->getMostCommonTags($this->getTestUserId(), 10);
        $this->assertCount(1, $result);
        $this->assertSame('uploaded-tag', $result[0]['text']);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTags
     */
    public function test_getMostCommonTags_excludes_non_uploaded_memes_when_storage_set(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'initial-state-tag',
        ])));
        $result = $repo->getMostCommonTags($this->getTestUserId(), 10);
        $this->assertSame([], $result);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_getMostCommonTagsForMemes_returns_empty_when_meme_ids_empty(): void
    {
        $repo = new FakeMemeTagRepo();
        $this->assertSame([], $repo->getMostCommonTagsForMemes($this->getTestUserId(), [], 10));
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_getMostCommonTagsForMemes_returns_common_tags_for_specified_memes(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $m1 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'a.jpg', UploadedFile::fromFile(__FILE__));
        $m2 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'b.jpg', UploadedFile::fromFile(__FILE__));
        $memeStorageRepo->setUploaded($m1);
        $memeStorageRepo->setUploaded($m2);
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $m1,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'shared',
        ])));
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $m2,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'shared',
        ])));
        $repo->addTagForMeme($this->getTestUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $m1,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'only-m1',
        ])));
        $result = $repo->getMostCommonTagsForMemes($this->getTestUserId(), [$m1, $m2], 10);
        $this->assertCount(2, $result);
        $this->assertSame('shared', $result[0]['text']);
        $this->assertSame(2, $result[0]['count']);
        $this->assertSame('only-m1', $result[1]['text']);
        $this->assertSame(1, $result[1]['count']);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::updateTagForUser
     */
    public function test_updateTagForUser_returns_zero_when_meme_not_owned_by_user(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $meme_id = $memeStorageRepo->storeMeme('other-user', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme('other-user', MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'tag1',
        ])));
        $tags = $repo->getUserTagsForMeme('other-user', $meme_id);
        $this->assertCount(1, $tags);
        $tag_id = $tags[0]->id;
        $count = $repo->updateTagForUser($this->getTestUserId(), MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => $tag_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'hacked1',
        ])));
        $this->assertSame(0, $count);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo::deleteTagForUser
     */
    public function test_deleteTagForUser_returns_zero_when_meme_not_owned_by_user(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $meme_id = $memeStorageRepo->storeMeme('other-user', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo = new FakeMemeTagRepo($memeStorageRepo);
        $repo->addTagForMeme('other-user', MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'tag1',
        ])));
        $tags = $repo->getUserTagsForMeme('other-user', $meme_id);
        $tag_id = $tags[0]->id;
        $count = $repo->deleteTagForUser($this->getTestUserId(), $tag_id);
        $this->assertSame(0, $count);
    }
}
