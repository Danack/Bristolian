<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\UploadedFiles\UploadedFile;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeTagRepoTest extends MemeTagRepoFixture
{
    private ?string $testUserId = null;
    private ?string $testMemeId = null;

    public function getTestInstance(): MemeTagRepo
    {
        return $this->injector->make(PdoMemeTagRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }

    protected function getTestMemeId(): string
    {
        if ($this->testMemeId === null) {
            $memeStorageRepo = $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
            $uploadedFile = UploadedFile::fromFile(__FILE__);
            $this->testMemeId = $memeStorageRepo->storeMeme(
                $this->getTestUserId(),
                'test_meme_' . uniqid() . '.jpg',
                $uploadedFile
            );
            $memeStorageRepo->setUploaded($this->testMemeId);
        }
        return $this->testMemeId;
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::__construct
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::addTagForMeme
     */
    public function test_pdo_addTagForMeme_persists_tag(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $userId = $this->getTestUserId();
        $memeId = $this->getTestMemeId();
        $params = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'tag_text',
        ]));
        $repo->addTagForMeme($userId, $params);
        $tags = $repo->getUserTagsForMeme($userId, $memeId);
        $this->assertCount(1, $tags);
        $this->assertSame('tag_text', $tags[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getUserTagsForMeme
     */
    public function test_pdo_getUserTagsForMeme_returns_empty_before_adding(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $tags = $repo->getUserTagsForMeme($this->getTestUserId(), $this->getTestMemeId());
        $this->assertSame([], $tags);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::updateTagForUser
     */
    public function test_pdo_updateTagForUser_updates_user_tag_text(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $userId = $this->getTestUserId();
        $memeId = $this->getTestMemeId();
        $params = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'original',
        ]));
        $repo->addTagForMeme($userId, $params);
        $tags = $repo->getUserTagsForMeme($userId, $memeId);
        $this->assertCount(1, $tags);
        $tagId = $tags[0]->id;
        $updateParams = MemeTagUpdateParams::createFromVarMap(new ArrayVarMap([
            'meme_tag_id' => $tagId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'updated_text',
        ]));
        $count = $repo->updateTagForUser($userId, $updateParams);
        $this->assertSame(1, $count);
        $tagsAfter = $repo->getUserTagsForMeme($userId, $memeId);
        $this->assertSame('updated_text', $tagsAfter[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::deleteTagForUser
     */
    public function test_pdo_deleteTagForUser_removes_tag(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $userId = $this->getTestUserId();
        $memeId = $this->getTestMemeId();
        $params = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'to_delete',
        ]));
        $repo->addTagForMeme($userId, $params);
        $tags = $repo->getUserTagsForMeme($userId, $memeId);
        $tagId = $tags[0]->id;
        $count = $repo->deleteTagForUser($userId, $tagId);
        $this->assertSame(1, $count);
        $tagsAfter = $repo->getUserTagsForMeme($userId, $memeId);
        $this->assertSame([], $tagsAfter);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTags
     */
    public function test_pdo_getMostCommonTags_returns_tags_sorted_by_count(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $userId = $this->getTestUserId();
        $memeId = $this->getTestMemeId();
        $common = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'common',
        ]));
        $repo->addTagForMeme($userId, $common);
        $repo->addTagForMeme($userId, $common);
        $rare = MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'rare',
        ]));
        $repo->addTagForMeme($userId, $rare);
        $result = $repo->getMostCommonTags($userId, 10);
        $texts = array_column($result, 'text');
        $this->assertContains('common', $texts);
        $this->assertContains('rare', $texts);
        $commonRow = array_values(array_filter($result, fn ($r) => $r['text'] === 'common'))[0];
        $this->assertSame(2, $commonRow['count']);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_pdo_getMostCommonTagsForMemes_empty_array_returns_empty(): void
    {
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $result = $repo->getMostCommonTagsForMemes($this->getTestUserId(), [], 10);
        $this->assertSame([], $result);
    }

    /**
     * @covers \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::getMostCommonTagsForMemes
     */
    public function test_pdo_getMostCommonTagsForMemes_returns_common_tags_for_given_memes(): void
    {
        $memeStorageRepo = $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $memeId1 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'm1_' . uniqid() . '.jpg', $uploadedFile);
        $memeStorageRepo->setUploaded($memeId1);
        $memeId2 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'm2_' . uniqid() . '.jpg', $uploadedFile);
        $memeStorageRepo->setUploaded($memeId2);
        $repo = $this->injector->make(PdoMemeTagRepo::class);
        $userId = $this->getTestUserId();
        $repo->addTagForMeme($userId, MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId1,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'shared',
        ])));
        $repo->addTagForMeme($userId, MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $memeId2,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'shared',
        ])));
        $result = $repo->getMostCommonTagsForMemes($userId, [$memeId1, $memeId2], 10);
        $this->assertNotEmpty($result);
        $shared = array_values(array_filter($result, fn ($r) => $r['text'] === 'shared'))[0] ?? null;
        $this->assertNotNull($shared);
        $this->assertSame(2, $shared['count']);
    }
}
