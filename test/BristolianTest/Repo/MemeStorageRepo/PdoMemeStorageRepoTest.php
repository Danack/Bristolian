<?php

declare(strict_types=1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Model\Types\Meme;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\FixedUuidGenerator;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Ramsey\Uuid\Uuid;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeStorageRepoTest extends MemeStorageRepoFixture
{
    use HasTestWorld;
    use TestPlaceholders;

    public function getTestInstance(): MemeStorageRepo
    {
        return $this->injector->make(PdoMemeStorageRepo::class);
    }

    protected function getValidUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::__construct
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::storeMeme
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::setUploaded
     */
    public function test_createEntry(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $normalized_name = Uuid::uuid7()->toString() . '.jpg';
        $testUser = $this->createTestAdminUser();

        $file_id = $repo->storeMeme(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );
        $this->assertNotEmpty($file_id);
        $repo->setUploaded($file_id);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::getMeme
     */
    public function test_getMeme_returns_meme_after_store(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $normalized_name = Uuid::uuid7()->toString() . '.jpg';

        $meme_id = $repo->storeMeme($testUser->getUserId(), $normalized_name, $uploadedFile);
        $repo->setUploaded($meme_id);

        $meme = $repo->getMeme($meme_id);
        $this->assertInstanceOf(Meme::class, $meme);
        $this->assertSame($meme_id, $meme->id);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::getByNormalizedName
     */
    public function test_getByNormalizedName_returns_meme(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $normalized_name = Uuid::uuid7()->toString() . '.jpg';

        $repo->storeMeme($testUser->getUserId(), $normalized_name, $uploadedFile);

        $meme = $repo->getByNormalizedName($normalized_name);
        $this->assertInstanceOf(Meme::class, $meme);
        $this->assertSame($normalized_name, $meme->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::listMemesForUser
     */
    public function test_listMemesForUser_returns_uploaded_memes(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);

        $memes = $repo->listMemesForUser($testUser->getUserId());
        $this->assertNotEmpty($memes);
        $this->assertContainsOnlyInstancesOf(Meme::class, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::listAllMemes
     */
    public function test_listAllMemes_returns_uploaded_memes(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);

        $memes = $repo->listAllMemes();
        $this->assertNotEmpty($memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::listMemesForUserWithNoTags
     */
    public function test_listMemesForUserWithNoTags_returns_memes(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);

        $memes = $repo->listMemesForUserWithNoTags($testUser->getUserId());
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::searchMemesForUser
     */
    public function test_searchMemesForUser_null_criteria_returns_listMemesForUser(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);

        $memes = $repo->searchMemesForUser($testUser->getUserId(), null, null);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::searchMemesForUser
     */
    public function test_searchMemesForUser_with_query_returns_matching_memes(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $memeTagRepo = $this->injector->make(PdoMemeTagRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);
        $memeTagRepo->addTagForMeme($testUser->getUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'searchable-tag-text',
        ])));

        $memes = $repo->searchMemesForUser($testUser->getUserId(), 'searchable', null);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::searchMemesByExactTags
     */
    public function test_searchMemesByExactTags_empty_returns_listMemesForUser(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);

        $memes = $repo->searchMemesByExactTags($testUser->getUserId(), []);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::searchMemesByExactTags
     */
    public function test_searchMemesByExactTags_with_matching_tags_returns_meme(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $memeTagRepo = $this->injector->make(PdoMemeTagRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);
        $memeTagRepo->addTagForMeme($testUser->getUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'exact-tag-a',
        ])));
        $memeTagRepo->addTagForMeme($testUser->getUserId(), MemeTagParams::createFromVarMap(new ArrayVarMap([
            'meme_id' => $meme_id,
            'type' => MemeTagType::USER_TAG->value,
            'text' => 'exact-tag-b',
        ])));

        $memes = $repo->searchMemesByExactTags($testUser->getUserId(), ['exact-tag-a', 'exact-tag-b']);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::markAsDeleted
     */
    public function test_markAsDeleted(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);
        $repo->setUploaded($meme_id);
        $repo->markAsDeleted($meme_id);

        $this->assertNull($repo->getMeme($meme_id));
        $memes = $repo->listAllMemes();
        $ids = array_map(fn (Meme $m) => $m->id, $memes);
        $this->assertNotContains($meme_id, $ids);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::getMemeByOriginalFilename
     */
    public function test_getMemeByOriginalFilename_returns_meme(): void
    {
        $repo = $this->injector->make(PdoMemeStorageRepo::class);
        $testUser = $this->createTestAdminUser();
        $baseFile = UploadedFile::fromFile(__FILE__);
        $originalName = 'unique-original-' . Uuid::uuid7()->toString() . '.jpg';
        $uploadedFile = new UploadedFile($baseFile->getTmpName(), $baseFile->getSize(), $originalName, 0);

        $meme_id = $repo->storeMeme($testUser->getUserId(), Uuid::uuid7()->toString() . '.jpg', $uploadedFile);

        $meme = $repo->getMemeByOriginalFilename($testUser->getUserId(), $originalName);
        $this->assertInstanceOf(Meme::class, $meme);
        $this->assertSame($meme_id, $meme->id);
    }

    /**
     * Duplicate id triggers constraint violation (23000); repo throws UserConstraintFailedException.
     *
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::storeMeme
     */
    public function test_storeMeme_throws_UserConstraintFailedException_on_duplicate_id(): void
    {
        $fixedUuid = Uuid::uuid7()->toString();
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $uuidGenerator = new FixedUuidGenerator($fixedUuid);
        $repo = new PdoMemeStorageRepo($pdoSimple, $uuidGenerator);

        $testUser = $this->createTestAdminUser();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $repo->storeMeme($testUser->getUserId(), 'first.jpg', $uploadedFile);

        $this->expectException(UserConstraintFailedException::class);
        $repo->storeMeme($testUser->getUserId(), 'second.jpg', $uploadedFile);
    }
}
