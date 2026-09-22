<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use PHPUnit\Framework\Attributes\CoversMethod;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use BristolianTest\BaseTestCase;

/**
 * @internal
 */

#[CoversMethod(\Bristolian\Repo\BccTroRepo\FakeBccTroRepo::class, 'saveData')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\FakeBccTroRepo::class, 'saveDataIfNew')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\FakeBccTroRepo::class, 'getMostRecentData')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'saveData')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'saveDataIfNew')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'getMostRecentData')]

abstract class BccTroRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the BccTroRepo implementation.
     *
     * @return BccTroRepo
     */
    abstract public function getTestInstance(): BccTroRepo;

    public function test_saveData_stores_page_html(): void
    {
        $repo = $this->getTestInstance();
        $html = $this->createUniquePageHtml('save-data');

        $saveId = $repo->saveData($html);

        $this->assertGreaterThanOrEqual(0, $saveId);
    }

    public function test_saveDataIfNew_saves_when_no_previous_data(): void
    {
        $repo = $this->getTestInstance();
        $html = $this->createUniquePageHtml('first-save');

        $saveId = $repo->saveDataIfNew($html);

        $this->assertNotNull($saveId);
    }

    public function test_saveDataIfNew_returns_null_when_page_unchanged(): void
    {
        $repo = $this->getTestInstance();
        $html = $this->createUniquePageHtml('unchanged');

        $firstSaveId = $repo->saveDataIfNew($html);
        $this->assertNotNull($firstSaveId);

        $secondSaveId = $repo->saveDataIfNew($html);
        $this->assertNull($secondSaveId);
    }

    public function test_saveDataIfNew_saves_when_page_differs(): void
    {
        $repo = $this->getTestInstance();
        $firstHtml = $this->createUniquePageHtml('before-change');
        $secondHtml = $this->createUniquePageHtml('after-change');

        $firstSaveId = $repo->saveDataIfNew($firstHtml);
        $this->assertNotNull($firstSaveId);

        $secondSaveId = $repo->saveDataIfNew($secondHtml);
        $this->assertNotNull($secondSaveId);
        $this->assertNotSame($firstSaveId, $secondSaveId);
    }

    private function createUniquePageHtml(string $label): string
    {
        $unique = create_test_uniqid();

        return '<html><body>TRO page ' . $label . ' ' . $unique . '</body></html>';
    }
}
