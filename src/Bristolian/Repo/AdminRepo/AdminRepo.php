<?php

declare(strict_types = 1);

namespace Bristolian\Repo\AdminRepo;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Parameters\CreateUserParams;

/**
 * Allows admins to interact with the Admin repo.
 */
interface AdminRepo
{
    public function addUser(CreateUserParams $createAdminUserParams): AdminUser;

    public function getAdminUserId(string $username): ?string;

    /**
     * We really need to standardise on username or email. Or something.
     *
     * @param string $username
     * @param string $password
     * @return AdminUser|null
     */
    public function getAdminUser(string $username, string $password): ?AdminUser;
}
