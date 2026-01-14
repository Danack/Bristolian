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
    public function addUser(CreateUserParams $createUserParams): AdminUser;

    public function getAdminUserId(string $username): ?string;

    public function getAdminUser(string $username, string $password): ?AdminUser;
}
