<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\App;
use Bristolian\Data\StripeConnectData;
use Bristolian\Model\BankDetails;
use Bristolian\Model\ContactInformation;
use Bristolian\Model\Project;
use Bristolian\Model\PurchaseOrder;
use Bristolian\Params\RaiseInvoiceParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\SkuRepo\SkuRepo;
use Bristolian\Repo\SkuPriceRepo\SkuPriceRepo;
use Bristolian\Repo\ProjectRepo\ProjectRepo;
use Bristolian\Params\CreateAdminUserParams;
use Bristolian\Repo\ManageAdminsRepo\ManageAdminsRepo;
use Bristolian\Factory\ProjectStripeClientFactory\ProjectStripeClientFactory;
use Bristolian\Config;
use Bristolian\Repo\PurchaseOrderRepo\PdoPurchaseOrderRepo;
use Bristolian\Data\PurchaseOrderRequest;
use Bristolian\Data\PurchaseRequestSku;
use Bristolian\Repo\InvoiceRepo\DatabaseInvoiceRepo;
use Bristolian\Repo\ProjectCredentialRepo\DoctrineProjectStripeConnectRepo;

class DataSeed
{
    public function seedDatabase(
        ProjectRepo $projectRepo,
        SkuRepo $skuRepo,
        SkuPriceRepo $skuPriceRepo,
        AdminRepo $adminRepo,
        ManageAdminsRepo $manageAdminsRepo,
        ProjectStripeClientFactory $projectStripeClientFactory,
        PdoPurchaseOrderRepo $pdoPurchaseOrderRepo,
        DatabaseInvoiceRepo $databaseInvoiceRepo,
        DoctrineProjectStripeConnectRepo $doctrineProjectStripeConnectRepo,
        Config $config
    ) {

        $projectName = 'Exemplum';

        if (Config::isProductionEnv() === true) {
            echo "Skipping data seeding as running in prod.";
            return;
        }

        echo "fyi, environment is: " . Config::getEnvironment() . "\n";

        $project = $projectRepo->getProjectByName($projectName);

        if ($project === null) {
            $project = $projectRepo->createProject($projectName, false);
        }

        $skusForProject = $skuRepo->getAllSkusForProject($project);
        if (count($skusForProject) === 0) {
            $sku = $skuRepo->createSkuForProject(
                $project,
                'Maintenance',
                'Ongoing bugfixes and support of new Imagick versions.'
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
                'Updating documentation at php.net and examples at phpimagick.com.'
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
                'Adding new features to Exemplum.'
            );
            $skuPriceRepo->setSkuPrice(
                $sku,
                10000,
                11000,
                12500
            );
        }

        $adminUser = $manageAdminsRepo->getAdminUserByName(\Bristolian\App::TEST_ADMIN_USERNAME);

        if ($adminUser === null) {
            $adminUserParams = CreateAdminUserParams::createFromArray([
                'username' => \Bristolian\App::TEST_ADMIN_USERNAME,
                'password' => \Bristolian\App::TEST_ADMIN_PASSWORD
            ]);
            $adminUser = $adminRepo->addAdminUser($adminUserParams);
        }

        $projects = $manageAdminsRepo->getProjectsForAdminUser($adminUser);

        if (count($projects) === 0) {
            $manageAdminsRepo->addAdminUserToProject($adminUser, $project);
        }

        $projectStripeClient = $projectStripeClientFactory->createStripeClientForProject(
            $project
        );

        if ($projectStripeClient === null) {
            $stripeConnectData = $config->getTestStripeConnectData();
            $doctrineProjectStripeConnectRepo->setStripeConnectForProject(
                $stripeConnectData,
                $project
            );
        }

        $purchaseOrders = $pdoPurchaseOrderRepo->getPurchaseOrdersForProject($project);

        if (count($purchaseOrders) === 0) {
            $skuPrices = $skuPriceRepo->getAllSkuPricesForProject($project);
            $purchaseRequestSkus = [];
            foreach ($skuPrices as $skuPrice) {
                $purchaseRequestSkus[] = new PurchaseRequestSku(
                    $unitPrice = $skuPrice->getPriceCostInGBPPence(),
                    $quantity = 3,
                    $name = $skuPrice->getSkuName(),
                    $skuId = $skuPrice->getSkuId(),
                    $skuPriceId = $skuPrice->getPriceId()
                );

                break;
            }

            $purchaseOrderRequest = new PurchaseOrderRequest(
                $purchaseRequestSkus,
                $project,
                $currency = 'GBP',
                $name = 'Dan the man',
                $email_address = 'dan@example.com',
                $company_name = null
            );

            $purchaseOrder = $pdoPurchaseOrderRepo->createPurchaseOrder(
                $project,
                $purchaseOrderRequest
            );
        }
        else {
            $purchaseOrder = $purchaseOrders[0];
        }

        $databaseInvoiceRepo->getInvoicesForProject($project);
        $invoiceParams = RaiseInvoiceParams::createFromArray([]);

        $databaseInvoiceRepo->approvePurchaseOrderAsInvoice(
            $project,
            $purchaseOrder,
            $invoiceParams
        );

        $addressLines = [
            '30 Berkeley House',
            'Charlotte Street',
            'Bristol',
            'BS1 5PY'
        ];

        $projectRepo->setProjectContactInformation(
            $project,
            'Daniel Ackroyd',
            '+44 (0) 7473 305 865',
            "Danack@basereality.com",
            'http://phpimagick.com',
            $addressLines
        );

        $projectRepo->setProjectBankDetails(
            $project,
            'Barclays Bank',
            '10-20-30',
            '12345678'
        );

        // Create longer Project
        $projectName = 'The most awesomest OpenSourceProject in the history of the world';
        $project2 = $projectRepo->getProjectByName($projectName);

        if ($project2 === null) {
            $project2 = $projectRepo->createProject($projectName, true);
        }

        $sku = $skuRepo->createSkuForProject(
            $project2,
            'Very long feature that people might like to pay for',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        );

        $skuPriceRepo->setSkuPrice(
            $sku,
            10000,
            11000,
            12500
        );

        $projectStripeClient2 = $projectStripeClientFactory->createStripeClientForProject(
            $project2
        );

        if ($projectStripeClient2 === null) {
            $stripeConnectData2 = $config->getTestStripeConnectData();
            $doctrineProjectStripeConnectRepo->setStripeConnectForProject(
                $stripeConnectData2,
                $project2
            );
        }
    }
}
