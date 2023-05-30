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

//    public function setPasswordForAdminUser(AdminUser $adminUser, string $newPassword)
//    {
//        foreach ($this->usernamesPasswordsAndUsers as &$userInfo) {
//            if ($userInfo[0] === $adminUser->getUsername()) {
//                $userInfo[1] = $newPassword;
//            }
//        }
//    }

    /**
     * @param string $email_address
     * @param string $password
     * @param mixed[] $usernamesPasswordsAndUser
     * @return AdminUser|null
     */
    private function matchUser(
        string $email_address,
        string $password,
        array $usernamesPasswordsAndUser
    ): ?AdminUser {

        if ($email_address !== $usernamesPasswordsAndUser[0]) {
            return null;
        }

        if ($password !== $usernamesPasswordsAndUser[1]) {
            return null;
        }

        return $usernamesPasswordsAndUser[2];
    }

    public function getAdminUser(string $email_address, string $password): ?AdminUser
    {
        foreach ($this->email_addresses_PasswordsAndUsers as $usernamesPasswordsAndUser) {
            $user = $this->matchUser($email_address, $password, $usernamesPasswordsAndUser);
            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }
//
//    public function setGoogle2FaSecret(AdminUser $adminUser, string $secret): AdminUser
//    {
//        throw new \Exception("setGoogle2FaSecret not implemented yet.");
//    }
//
//    public function removeGoogle2FaSecret(AdminUser $adminUser)
//    {
//        throw new \Exception("removeGoogle2FaSecret not implemented yet.");
//    }
}
