<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\PdoBccTroRepo;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group db
 */

#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'getMostRecentData')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'saveData')]
#[CoversMethod(\Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class, 'saveDataIfNew')]

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
