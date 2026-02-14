<?php

declare(strict_types = 1);

namespace Bristolian\Repo\AdminRepo;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Parameters\CreateUserParams;
use Ramsey\Uuid\Uuid;

class FakeAdminRepo implements AdminRepo
{

    /**
     * @var AdminUser[]
     */
    private array $adminUsers = [];

    /**
     * @param AdminUser[] $usernamesPasswordsAndUsers
     */
    public function __construct(array $usernamesPasswordsAndUsers)
    {
        $this->adminUsers = $usernamesPasswordsAndUsers;
    }

    private function createUser(): string
    {
        $uuid = Uuid::uuid7();

        return $uuid->toString();
    }

    public function addUser(CreateUserParams $createAdminUserParams): AdminUser
    {
        $email_address = $createAdminUserParams->getEmailaddress();
        $password = $createAdminUserParams->getPassword();

        $user_id = $this->createUser();
        $password_hash = generate_password_hash($password);

        $adminUser = AdminUser::new($user_id, $email_address, $password_hash);

        $this->adminUsers[] = $adminUser;

        return $adminUser;
    }


    public function getAdminUserId(string $username): ?string
    {
        foreach ($this->adminUsers as $adminUser) {
            if ($adminUser->getEmailAddress() === $username) {
                return $adminUser->getUserId();
            }
        }

        return null;
    }


    /**
     * @param string $email_address
     * @param string $password
     * @return AdminUser|null
     */
    public function getAdminUser(string $email_address, string $password): ?AdminUser
    {
        foreach ($this->adminUsers as $adminUser) {
            if ($adminUser->getEmailAddress() === $email_address) {
                $password_hash = $adminUser->getPasswordHash();

                if (password_verify($password, $password_hash) === true) {
                    return $adminUser;
                }
            }
        }

        return null;
    }
}
