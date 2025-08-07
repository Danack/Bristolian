<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Parameters\CreateUserParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\AdminRepo\DuplicateEntryException;
use VarMap\VarMap;

/**
 * Code of adding users.
 * Not unit-tested, as currently not worth it.
 *
 * @codeCoverageIgnore
 */
class Admin
{
    public function createAdminLogin(
        VarMap $varMap,
        AdminRepo $adminUserAddRepo
    ): void {
        $createAdminUserParams = CreateUserParams::createFromVarMap($varMap);

        try {
            $adminUserAddRepo->addUser($createAdminUserParams);
        }
        catch (DuplicateEntryException $ucve) {
            echo "username already exists\n";
            exit(-1);
        }

        printf(
            "Admin added.\n\tusername: [%s]\n\tpassword: [%s]",
            $createAdminUserParams->getEmailaddress(),
            $createAdminUserParams->getPassword()
        );
    }
}
