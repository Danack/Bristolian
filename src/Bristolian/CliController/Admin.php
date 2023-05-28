<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

//use Osf\Params\CreateAdminUserParams;
use Bristolian\DataType\CreateUserParams;
//use Osf\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\AdminRepo\AdminRepo;
//use Osf\Repo\SkuPriceRepo\SkuPriceRepo;
use VarMap\VarMap;

//use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
//use Osf\Repo\ManageAdminsRepo\ManageAdminsRepo;
//use Osf\Repo\ProjectRepo\ProjectRepo;
//use Osf\Repo\SkuRepo\SkuRepo;

class Admin
{
    public function createAdminLogin(
        VarMap $varMap,
        AdminRepo $adminUserAddRepo
    ) {
        $createAdminUserParams = CreateUserParams::createFromVarMap($varMap);

        try {
            $adminUserAddRepo->addUser($createAdminUserParams);
        }
        catch (UniqueConstraintViolationException $ucve) {
            echo "username already exists\n";
            exit(-1);
        }

        printf(
            "Admin added.\n\tusername: [%s]\n\tpassword: [%s]",
            $createAdminUserParams->getEmailaddress(),
            $createAdminUserParams->getPassword()
        );
    }

    public function createProject(
        string $project_name,
        string $visible,
        ProjectRepo $projectRepo,
        SkuRepo $skuRepo,
        SkuPriceRepo $skuPriceRepo
    ) {
        $visibleBoolean = false;

        if ($visible === 'true') {
            $visibleBoolean = true;
        }

        $project = $projectRepo->createProject($project_name, $visibleBoolean);
        $sku = $skuRepo->createSkuForProject(
            $project,
            'Quality assurance',
            'This includes bug-fixes, working on CI pipelines and general code maintenance.'
        );
        $skuPriceRepo->setSkuPrice(
            $sku,
            10000,
            11000,
            12500
        );

        $sku = $skuRepo->createSkuForProject(
            $project,
            'Documentation',
            'This includes all written documentation for the project, as well as code examples.'
        );
        $skuPriceRepo->setSkuPrice(
            $sku,
            10000,
            11000,
            12500
        );

        $sku = $skuRepo->createSkuForProject(
            $project,
            'New features',
            'Adding new features to the project.'
        );
        $skuPriceRepo->setSkuPrice(
            $sku,
            10000,
            11000,
            12500
        );
    }


    public function resetPassword(
        string $username,
        ManageAdminsRepo $adminUserRepo,
        AdminRepo $adminRepo
    ) {
        $adminUser = $adminUserRepo->getAdminUserByName($username);

        if ($adminUser === null) {
            echo "Failed to find user with name [$username].\n";
            exit(-1);
        }

        $newPassword = bin2hex(random_bytes(16));

        $adminRepo->setPasswordForAdminUser($adminUser, $newPassword);
        printf(
            "Admin password changed.\n\tusername: [%s]\n\tpassword[%s]",
            $adminUser->getUsername(),
            $newPassword
        );
    }



    public function resetGoogle2FA(
        string $username,
        ManageAdminsRepo $adminUserRepo,
        AdminRepo $adminRepo
    ) {
        $adminUser = $adminUserRepo->getAdminUserByName($username);

        if ($adminUser === null) {
            echo "Failed to find user with name [$username].\n";
            exit(-1);
        }

        $adminRepo->removeGoogle2FaSecret($adminUser);
        printf(
            "Admin google 2fa remove.\n\tusername: [%s]\n",
            $adminUser->getUsername()
        );
    }

    public function addToProject(
        string $username,
        string $project_name,
        ManageAdminsRepo $adminUserRepo,
        ProjectRepo $projectRepo
    ) {

        $adminUser = $adminUserRepo->getAdminUserByName($username);

        if ($adminUser === null) {
            echo "Could not find user [$username] to add to project.\n";
            exit(-1);
        }

        $project = $projectRepo->getProjectByName($project_name);
        if ($project === null) {
            echo "Could not find project [$project_name] to add user to.\n";
            exit(-1);
        }

        $adminUserRepo->addAdminUserToProject($adminUser, $project);

        echo "Done\n";
    }


    public function removeFromProject(string $username, string $project_name)
    {
        echo "not implemented";
        exit(-1);
    }
}
