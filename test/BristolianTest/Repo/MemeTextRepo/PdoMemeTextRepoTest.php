<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\Model\Generated\StoredMeme;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo;
use BristolianTest\Repo\DbTransactionIsolation;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeTextRepoTest extends MemeTextRepoFixture
{
//    use DbTransactionIsolation;

    private ?string $testUserId = null;
    private ?string $testUserId2 = null;

//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->dbTransactionSetUp();
//    }
//
//    public function tearDown(): void
//    {
//        $this->dbTransactionTearDown();
//        parent::tearDown();
//    }

    protected function dbTransactionClearTables(): void
    {
        $pdoSimple = $this->injector->make(PdoSimple::class);
//        $pdoSimple->execute('DELETE FROM meme_tag', []);
//        $pdoSimple->execute('DELETE FROM meme_text', []);
//        $pdoSimple->execute('DELETE FROM stored_meme', []);
    }

    public function getTestInstance(): MemeTextRepo
    {
        return $this->injector->make(PdoMemeTextRepo::class);
    }

    protected function getMemeStorageRepo(): MemeStorageRepo
    {
        return $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }

    protected function getTestUserId2(): string
    {
        if ($this->testUserId2 === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId2 = $adminUser->getUserId();
        }
        return $this->testUserId2;
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::__construct
     * @covers \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::getNextMemeToOCR
     */
    public function test_pdo_getNextMemeToOCR_returns_meme_without_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->injector->make(PdoMemeTextRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $memeStorageRepo->storeMeme($this->getTestUserId(), 'ocr_candidate.jpg', $uploadedFile);

        $result = $repo->getNextMemeToOCR();

        $this->assertInstanceOf(StoredMeme::class, $result);
        $this->assertNotEmpty($result->id);
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::searchMemeIdsByText
     */
    public function test_pdo_searchMemeIdsByText_returns_matching_meme_ids(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->injector->make(PdoMemeTextRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $memeId = $memeStorageRepo->storeMeme($this->getTestUserId(), 'searchable.jpg', $uploadedFile);
        $meme = $memeStorageRepo->getMeme($memeId);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $repo->saveMemeText($storedMeme, 'UniqueSearchableContent');

        $result = $repo->searchMemeIdsByText($this->getTestUserId(), 'Searchable');
        $this->assertCount(1, $result);
        $this->assertContains($memeId, $result);
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::updateMemeText
     */
    public function test_pdo_updateMemeText_inserts_when_no_existing_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->injector->make(PdoMemeTextRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $memeId = $memeStorageRepo->storeMeme($this->getTestUserId(), 'update_insert.jpg', $uploadedFile);

        $repo->updateMemeText($memeId, 'New text via update');

        $text = $repo->getMemeText($memeId);
        $this->assertNotNull($text);
        $this->assertSame('New text via update', $text->text);
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::updateMemeText
     */
    public function test_pdo_updateMemeText_updates_existing_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->injector->make(PdoMemeTextRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $memeId = $memeStorageRepo->storeMeme($this->getTestUserId(), 'update_existing.jpg', $uploadedFile);
        $meme = $memeStorageRepo->getMeme($memeId);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $repo->saveMemeText($storedMeme, 'Original');

        $repo->updateMemeText($memeId, 'Replaced text');

        $text = $repo->getMemeText($memeId);
        $this->assertNotNull($text);
        $this->assertSame('Replaced text', $text->text);
    }
}
