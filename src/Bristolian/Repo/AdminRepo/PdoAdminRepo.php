<?php

declare(strict_types = 1);

namespace Bristolian\Repo\AdminRepo;

use Bristolian\PdoSimple;
use Bristolian\DataType\CreateUserParams;
use Bristolian\Model\AdminUser;
use Ramsey\Uuid\Uuid;

class PdoAdminRepo implements AdminRepo
{
    public function __construct(private PdoSimple $pdo)
    {
    }

    private function createUser(): string
    {
        $uuid = Uuid::uuid7();
        $userSQL = <<< SQL
insert into user (
  id
)
values (
  :id
)
SQL;

        $this->pdo->insert($userSQL, ['id' => $uuid->toString()]);

        return $uuid->toString();
    }

    public function addUser(CreateUserParams $createUserParams): AdminUser
    {
        $user_id = $this->createUser();
        $password_hash = generate_password_hash($createUserParams->getPassword());

        $userAuthSQL = <<< SQL
insert into user_auth_email_password (
  user_id,
  email_address,
  password_hash
)
values (
  :user_id,
  :email_address,
  :password_hash
)
SQL;
        $params = [
            ':user_id' => $user_id,
            ':email_address' => $createUserParams->getEmailAddress(),
            ':password_hash' => $password_hash,
        ];

        $insert_id = $this->pdo->insert($userAuthSQL, $params);

        return AdminUser::new(
            $insert_id,
            $createUserParams->getEmailAddress(),
            $password_hash
        );
    }

//    public function setPasswordForAdminUser(AdminUser $adminUser, string $newPassword)
//    {
//        $password_hash = generate_password_hash($newPassword);
//        $adminUser->setPasswordHash($password_hash);
//
//        $this->em->persist($adminUser);
//        $this->em->flush($adminUser);
//    }

//    /**
//     * Gets the user and validates their password
//     */
//    public function getAdminUser(string $username, string $password): ?AdminUser
//    {
//        $repo = $this->em->getRepository(\Osf\Model\AdminUser::class);
//
//        /** @var \Osf\Model\AdminUser|null $adminUser */
//        $adminUser = $repo->findOneBy(['username' => mb_strtolower($username)]);
//
//        if ($adminUser === null) {
////            log_admin_login_failed("Unknown username.");
//            return null;
//        }
//
//        if (password_verify($password, $adminUser->getPasswordHash()) !== true) {
////            log_admin_login_failed("password_verify failed.");
//            return null;
//        }
//
//        $options = get_password_options();
//
//        // Check if a newer hashing algorithm is available
//        // or the cost has changed
//        if (password_needs_rehash($adminUser->getPasswordHash(), PASSWORD_DEFAULT, $options)) {
//            // If so, create a new hash, and replace the old one
//            $newHash = password_hash($password, PASSWORD_DEFAULT, $options);
//
////            log_admin_login_failed("Rehashing password.");
//            $adminUser->setPasswordHash($newHash);
//
//            $this->em->persist($adminUser);
//            $this->em->flush();
//        }
//
//        return $adminUser;
//    }

//    public function setGoogle2FaSecret(AdminUser $adminUser, string $secret): AdminUser
//    {
//        $repo = $this->em->getRepository(\Osf\Model\AdminUser::class);
//
//        /** @var \Osf\Model\AdminUser|null $adminUserFromDB */
//        $adminUserFromDB = $repo->find($adminUser->getId());
//
//        if ($adminUserFromDB === null) {
//            throw new \Exception("Failed to find user in DB.");
//        }
//        $adminUserFromDB->setGoogle2faSecret($secret);
//        $this->em->persist($adminUserFromDB);
//        $this->em->flush();
//
//        return $adminUserFromDB;
//    }

//    public function removeGoogle2FaSecret(AdminUser $adminUser)
//    {
//        $repo = $this->em->getRepository(\Osf\Model\AdminUser::class);
//
//        /** @var \Osf\Model\AdminUser|null $adminUserFromDB */
//        $adminUserFromDB = $repo->find($adminUser->getId());
//
//        if ($adminUserFromDB === null) {
//            throw new \Exception("Failed to find user in DB.");
//        }
//
//        $adminUserFromDB->clearGoogle2faSecret();
//        $this->em->persist($adminUserFromDB);
//        $this->em->flush();
//    }
}
