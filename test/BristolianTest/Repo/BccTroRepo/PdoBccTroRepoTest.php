<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\PdoBccTroRepo;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group db
 */

#[CoversClass(PdoBccTroRepo::class)]
class PdoBccTroRepoTest extends BccTroRepoFixture
{
    public function getTestInstance(): BccTroRepo
    {
        return $this->injector->make(PdoBccTroRepo::class);
    }

    public function test_pdo_saveData_then_getMostRecentData_returns_stored_html(): void
    {
        $repo = $this->injector->make(PdoBccTroRepo::class);
        $html = '<html>persisted page ' . create_test_uniqid() . '</html>';

        $repo->saveData($html);

        $this->assertSame($html, $repo->getMostRecentData());
    }
}
