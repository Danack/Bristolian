<?php

declare(strict_types=1);

namespace BristolianTest\MoonAlert;

use Bristolian\MoonAlert\StandardMoonAlertRepo;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class StandardMoonAlertRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\MoonAlert\StandardMoonAlertRepo::getUsersForMoonAlert
     */
    public function test_getUsersForMoonAlert_returns_configured_emails(): void
    {
        $repo = new StandardMoonAlertRepo();

        $users = $repo->getUsersForMoonAlert();

        $this->assertIsArray($users);
        $this->assertNotEmpty($users);
        $this->assertContains('danack@basereality.com', $users);
        $this->assertContainsOnly('string', $users);
    }
}
