<?php

declare(strict_types = 1);

namespace Osf\Repo\AdminRepo;

use Bristolian\DataType\CreateUserParams;
use Osf\Model\AdminUser;
use Osf\Params\CreateAdminUserParams;

class FakeAdminRepo implements AdminRepo
{
    private $usernamesPasswordsAndUsers = [];

    /**
     *
     * @param array $usernamesPasswordsAndUsers Example [['John', 'password123']]
     */
    public function __construct(array $usernamesPasswordsAndUsers)
    {
        $this->usernamesPasswordsAndUsers = $usernamesPasswordsAndUsers;
    }


    public function addUser(CreateUserParams $createAdminUserParams): AdminUser
    {
        $username = $createAdminUserParams->getEmailaddress();
        $password = $createAdminUserParams->getPassword();

        $adminUser = AdminUser::fromPartial($password, $username, null);

        $this->usernamesPasswordsAndUsers[] = [$username, $password, $adminUser];

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
//
//
//    private function matchUser(
//        string $username,
//        string $password,
//        $usernamesPasswordsAndUser
//    ): ?AdminUser {
//
//        if ($username !== $usernamesPasswordsAndUser[0]) {
//            return null;
//        }
//
//        if ($password !== $usernamesPasswordsAndUser[1]) {
//            return null;
//        }
//
//        return $usernamesPasswordsAndUser[2];
//    }
//
//    public function getAdminUser(string $username, string $password): ?AdminUser
//    {
//        foreach ($this->usernamesPasswordsAndUsers as $usernamesPasswordsAndUser) {
//            $user = $this->matchUser($username, $password, $usernamesPasswordsAndUser);
//            if ($user !== null) {
//                return $user;
//            }
//        }
//
//        return null;
//    }
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
