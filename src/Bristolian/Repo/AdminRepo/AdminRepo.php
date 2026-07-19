<?php

declare(strict_types = 1);

namespace Bristolian\Repo\AdminRepo;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Parameters\CreateUserParams;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\user_auth_email_password;

/**
 * Allows admins to interact with the Admin repo.
 */
interface AdminRepo
{
    #[WritesTable(user_auth_email_password::class)]
    public function addUser(CreateUserParams $createAdminUserParams): AdminUser;

    #[ReadsTable(user_auth_email_password::class)]
    public function getAdminUserId(string $username): ?string;

    /**
     * We really need to standardise on username or email. Or something.
     *
     * @param string $username
     * @param string $password
     * @return AdminUser|null
     */
    #[ReadsTable(user_auth_email_password::class)]
    public function getAdminUser(string $username, string $password): ?AdminUser;
}
