<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group standard_repo
 */

#[CoversClass(FakeBccTroRepo::class)]
class FakeBccTroRepoTest extends BccTroRepoFixture
{
    public function getTestInstance(): BccTroRepo
    {
        return new FakeBccTroRepo();
    }

    public function test_fake_saveDataIfNew_does_not_append_duplicate_page(): void
    {
        $repo = new FakeBccTroRepo();
        $html = '<html>duplicate page</html>';

        $firstId = $repo->saveDataIfNew($html);
        $secondId = $repo->saveDataIfNew($html);

        $this->assertSame(0, $firstId);
        $this->assertNull($secondId);
        $this->assertCount(1, $repo->getSavedPages());
        $this->assertSame([$html], $repo->getSavedPages());
    }

    public function test_fake_getMostRecentData_returns_null_when_nothing_saved(): void
    {
        $repo = new FakeBccTroRepo();

        $this->assertNull($repo->getMostRecentData());
    }

    public function test_fake_getMostRecentData_returns_last_saved_page(): void
    {
        $repo = new FakeBccTroRepo();
        $firstHtml = '<html>first</html>';
        $secondHtml = '<html>second</html>';

        $repo->saveData($firstHtml);
        $repo->saveData($secondHtml);

        $this->assertSame($secondHtml, $repo->getMostRecentData());
    }
}
