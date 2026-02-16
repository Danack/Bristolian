<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AdminRepo;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Parameters\CreateUserParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeAdminRepoTest extends FakeAdminRepoFixture
{
    public function getTestInstance(): AdminRepo
    {
        return new FakeAdminRepo([]);
    }

    /**
     * Explicitly cover FakeAdminRepo so coverage is attributed when running this class.
     *
     * @covers \Bristolian\Repo\AdminRepo\FakeAdminRepo::__construct
     * @covers \Bristolian\Repo\AdminRepo\FakeAdminRepo::addUser
     * @covers \Bristolian\Repo\AdminRepo\FakeAdminRepo::getAdminUser
     * @covers \Bristolian\Repo\AdminRepo\FakeAdminRepo::getAdminUserId
     */
    public function test_fake_admin_repo_add_get_and_get_id(): void
    {
        $repo = new FakeAdminRepo([]);
        $params = CreateUserParams::createFromArray([
            'email_address' => 'admin@example.com',
            'password' => 'secret',
        ]);
        $user = $repo->addUser($params);
        $this->assertInstanceOf(AdminUser::class, $user);
        $this->assertSame('admin@example.com', $user->getEmailAddress());
        $found = $repo->getAdminUser('admin@example.com', 'secret');
        $this->assertNotNull($found);
        $this->assertSame($user->getUserId(), $repo->getAdminUserId('admin@example.com'));
    }
}
