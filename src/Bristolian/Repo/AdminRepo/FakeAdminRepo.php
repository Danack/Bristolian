<?php

declare(strict_types = 1);

namespace Bristolian\Repo\AdminRepo;

use Bristolian\DataType\CreateUserParams;
use Bristolian\Model\AdminUser;

class FakeAdminRepo implements AdminRepo
{
    /**
     * @var mixed[]
     *
     * should probably be  array<array{0: string, 1: string, 2: AdminUser}> or similar
     */
    private array $email_addresses_PasswordsAndUsers = [];

    /**
     *
     * @param mixed[] $usernamesPasswordsAndUsers Example [['John', 'password123']]
     */
    public function __construct(array $usernamesPasswordsAndUsers)
    {
        $this->email_addresses_PasswordsAndUsers = $usernamesPasswordsAndUsers;
    }

    public function addUser(CreateUserParams $createAdminUserParams): AdminUser
    {
        $email_address = $createAdminUserParams->getEmailaddress();
        $password = $createAdminUserParams->getPassword();

        $adminUser = AdminUser::fromPartial($email_address, $password);

        $this->email_addresses_PasswordsAndUsers[] = [$email_address, $password, $adminUser];

        return $adminUser;
    }

    /**
     * @param string $email_address
     * @param mixed[] $usernamesPasswordsAndUser
     * @return AdminUser|null
     */
    private function matchUser(
        string $email_address,
        array $usernamesPasswordsAndUser
    ): ?AdminUser {

        if ($email_address !== $usernamesPasswordsAndUser[0]) {
            return null;
        }

        return $usernamesPasswordsAndUser[2];
    }

    public function getAdminUserId(string $username): ?string
    {
        foreach ($this->email_addresses_PasswordsAndUsers as $usernamesPasswordsAndUser) {
            $user = $this->matchUser($username, $usernamesPasswordsAndUser);
            if ($user !== null) {
                return $user->getEmailAddress();
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
        foreach ($this->email_addresses_PasswordsAndUsers as $usernamesPasswordsAndUser) {
            $user = $this->matchUser($email_address, $usernamesPasswordsAndUser);
            if ($user !== null) {
                if ($password === $usernamesPasswordsAndUser[1]) {
                    return $user;
                }
            }
        }

        return null;
    }
}
