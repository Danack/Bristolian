<?php

declare(strict_types = 1);

namespace BristolianTest\Repo;

//use Osf\Data\ContentPolicyViolationReport;
use Bristolian\Model\AdminUser;
//use Osf\Model\Project;
//use Osf\Repo\ProjectRepo\DoctrineProjectRepo;
//use Osf\Repo\SkuRepo\DatabaseSkuRepo;
//use Osf\RouteInfo\StandardRouteInfo;
//use OsfTest\BaseTestCase;
//use Osf\Model\Sku;
//use VarMap\ArrayVarMap;
//use Osf\Params\StripePurchaseParams;
//use Osf\Repo\SkuPriceRepo\DatabaseSkuPriceRepo;
//use Osf\Repo\AdminRepo\DoctrineAdminRepo;
//use Osf\Params\CreateAdminUserParams;
//use Osf\Factory\ProjectStripeClientFactory\StandardProjectStripeClientFactory;
//use Osf\Data\PurchaseRequest;
//use Osf\Data\PurchaseRequestSku;
//use Osf\Repo\PurchaseOrderRepo\PdoPurchaseOrderRepo;
//use Osf\Data\PurchaseOrderRequest;
//use Osf\Model\SkuPrice;
//use Osf\Repo\SkuPriceRepo\FakeSkuPriceRepo;
//use Osf\Factory\ProjectStripeClientFactory\FakeProjectStripeClientFactory;
//use Osf\Stripe\ProjectStripeClient\AlwaysSucceedsStripeClient;
//use Osf\Repo\SkuRepo\InMemorySkuRepo;

trait Testing
{
    /**
     * @var \Auryn\Injector
     */
    private $injector;


    public function setup(): void
    {
        parent::setup();
        $this->injector = createInjector();
    }

    private function createInjector()
    {
        if ($this->injector === null) {
            $this->injector = createInjector();
        }
    }



    public function createRouteInfo(): \Osf\RouteInfo\StandardRouteInfo
    {
        return $this->injector->make(StandardRouteInfo::class);
    }

    public function createTestProject(): Project
    {
        $this->createInjector();
        $doctrineProjectRepo = $this->injector->make(DoctrineProjectRepo::class);

        $name = 'project_test_' . time() . '_' . random_int(1000, 9999);

        return $doctrineProjectRepo->createProject($name);
    }

    public function createSkuPriceForProject(
        Project $project,
        int $costInEURCents,
        int $costInGBPPence,
        int $costInUSDCents
    ): \Osf\Data\SkuPrice {
        $sku = $this->createSkuForProject($project);
        $databaseSkuPriceRepo = $this->injector->make(DatabaseSkuPriceRepo::class);
        $databaseSkuPriceRepo->setSkuPrice(
            $sku,
            $costInEURCents,
            $costInGBPPence,
            $costInUSDCents
        );

        $skuPrices = $databaseSkuPriceRepo->getAllSkuPricesForProject($project);

        return $skuPrices[0];
    }

    public function createEmptySkuPriceRepoForProject()
    {
        $repo = new FakeSkuPriceRepo();

        return $repo;
    }

    public function createEmptyProjectStripeClientFactory()
    {
        $stripeClientFactory = new FakeProjectStripeClientFactory([]);
        return $stripeClientFactory;
    }


    public function createSkuPriceRepoForProject(Project $project)
    {
        $repo = new FakeSkuPriceRepo();
        $sku = $this->createSkuForProject($project);
        $repo->setSkuPrice($sku, 10000, 10000, 10000);

        return $repo;
    }

    public function createFakeProjectStripeClientFactory(Project $project)
    {

        $stripeClient = new AlwaysSucceedsStripeClient();

        $stripeClientFactory = new FakeProjectStripeClientFactory([]);
        $stripeClientFactory->addStripeClientForProject($project, $stripeClient);

        return $stripeClientFactory;
    }



    public function createTestAdmin()
    {
        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $adminRepo = $this->injector->make(DoctrineAdminRepo::class);

        $createAdminUserParams = CreateAdminUserParams::createFromArray([
            'username' => $username,
            'password' => $password
        ]);

        $adminUser = $adminRepo->addAdminUser($createAdminUserParams);

        return [$username, $password, $adminUser];
    }

    public function createTestStripePurchaseParams(): StripePurchaseParams
    {
        $data = [
            'form_type' => 'stripe',
            'purchase_currency' => 'GBP',
            'purchase_price_cents' => '3000',
            'stripe_json' => '{"id":"tok_1EmKysFsTaCI8JaTOQWVvtzN","object":"token","card":{"id":"card_1EmKysFsTaCI8JaT8PdUFJcV","object":"card","address_city":"Bristol","address_country":"United Kingdom","address_line1":"Flat 30, Berkeley House, Charlotte Str.","address_line1_check":"pass","address_line2":null,"address_state":"ENG","address_zip":"BS1 5PY","address_zip_check":"pass","brand":"Visa","country":"US","cvc_check":"pass","dynamic_last4":null,"exp_month":2,"exp_year":2022,"funding":"credit","last4":"4242","metadata":{},"name":"Dan Ackroyd","tokenization_method":null},"client_ip":"86.7.192.139","created":1560778482,"email":"danack@basereality.com","livemode":false,"type":"card","used":false}',
            'purchase_quantities_json' => '[{"sku_id":"1","sku_price_id":"24","quantity":"2","unit_price":1000},{"sku_id":"3","sku_price_id":"26","quantity":"1","unit_price":1000}]',
            'project_name' => 'Imagick',
            'project_id' => '1',
        ];


        $varMap = new ArrayVarMap($data);

        [$params, $error] = StripePurchaseParams::createOrErrorFromVarMap($varMap);

        return $params;
    }

    public function createTestStripePurchaseParamsForProject(Project $project): StripePurchaseParams
    {
        $skuPricesRepo = $this->injector->make(DatabaseSkuPriceRepo::class);
        $skuPrices = $skuPricesRepo->getAllSkuPricesForProject($project);

        $skuDetails = [];

        foreach ($skuPrices as $skuPrice) {
            $skuDetail = [];

            $skuDetail["sku_id"] = "" . $skuPrice->getSkuId();
            $skuDetail["sku_price_id"] = "" . $skuPrice->getPriceId();
            $skuDetail["quantity"] = "1";
            $skuDetail["unit_price"] = 1000;
            $skuDetails[] = $skuDetail;
        }

        $data = [
            'form_type' => 'stripe',
            'purchase_currency' => 'GBP',
            'purchase_price_cents' => '3000',
            'stripe_json' => '{"id":"tok_1EmKysFsTaCI8JaTOQWVvtzN","object":"token","card":{"id":"card_1EmKysFsTaCI8JaT8PdUFJcV","object":"card","address_city":"Bristol","address_country":"United Kingdom","address_line1":"Flat 30, Berkeley House, Charlotte Str.","address_line1_check":"pass","address_line2":null,"address_state":"ENG","address_zip":"BS1 5PY","address_zip_check":"pass","brand":"Visa","country":"US","cvc_check":"pass","dynamic_last4":null,"exp_month":2,"exp_year":2022,"funding":"credit","last4":"4242","metadata":{},"name":"Dan Ackroyd","tokenization_method":null},"client_ip":"86.7.192.139","created":1560778482,"email":"danack@basereality.com","livemode":false,"type":"card","used":false}',
            'purchase_quantities_json' => json_encode_safe($skuDetails),
            'project_name' => $project->getName(),
            'project_id' => $project->getId(),
        ];


        $varMap = new ArrayVarMap($data);

        [$params, $error] = StripePurchaseParams::createOrErrorFromVarMap($varMap);

        return $params;
    }


    public function createSkuForProject(Project $project): Sku
    {
        $skuRepo = $this->injector->make(DatabaseSkuRepo::class);

        $createdSku = $skuRepo->createSkuForProject(
            $project,
            $name = 'SkuName ' . time(),
            $description = 'this is a description ' . time()
        );

        return $createdSku;
    }


    public function createFakeSkuPriceRepo(Project $project): FakeSkuPriceRepo
    {
        $repo = new \Osf\Repo\SkuPriceRepo\FakeSkuPriceRepo();
        $skuRepo = $this->createInMemorySkuRepoForProject($project);

        $skus = $skuRepo->getAllSkusForProject($project);

        foreach ($skus as $sku) {
            $repo->setSkuPrice($sku, 10000, 10000, 10000);
        }

        return $repo;
    }


    public function createInMemorySkuRepoForProject(Project $project): InMemorySkuRepo
    {
        $skuRepo = new \Osf\Repo\SkuRepo\InMemorySkuRepo();

        $createdSku = $skuRepo->createSkuForProject(
            $project,
            $name = 'SkuName ' . time(),
            $description = 'this is a description ' . time()
        );

        return $skuRepo;
    }



    public function getProjectFromDB(string $projectName): Project
    {
        $this->createInjector();

        $projectRepo = $this->injector->make(DoctrineProjectRepo::class);

        return $projectRepo->getProjectByName($projectName);
    }

    public function createStandardProjectStripeClient()
    {
        $stripeClientFactory = $this->injector->make(StandardProjectStripeClientFactory::class);
        $projectRepo = $this->injector->make(DoctrineProjectRepo::class);
        $project = $projectRepo->getProjectByName('Imagick');
        return $stripeClientFactory->createStripeClientForProject($project);
    }

    public function createPurchaseOrderForProject(
        string $projectName,
        string $nameOnPurchaseOrder,
        string $email_address
    ) {
        $project = $this->getProjectFromDB($projectName);
        $purchaseRequest = $this->createPurchaseOrderRequestForProject(
            $projectName,
            $nameOnPurchaseOrder,
            $email_address
        );
        $pdoPurchaseOrderRepo = $this->injector->make(PdoPurchaseOrderRepo::class);
        return $pdoPurchaseOrderRepo->createPurchaseOrder(
            $project,
            $purchaseRequest
        );
    }

    public function createPurchaseOrderRequestForProject(
        string $projectName,
        string $nameOnPurchaseOrder,
        string $email_address
    ): PurchaseOrderRequest {
        $project = $this->getProjectFromDB($projectName);
        $skuPriceRepo = $this->injector->make(DatabaseSkuPriceRepo::class);
        $skuPrices = $skuPriceRepo->getAllSkuPricesForProject($project);

        $company_name = null;
        foreach ($skuPrices as $skuPrice) {
            return new PurchaseOrderRequest(
                [new PurchaseRequestSku(
                    $price_unit = $skuPrice->getPriceCostInGBPPence(),
                    $quantity = 4,
                    $skuName = $skuPrice->getSkuName(),
                    $sku_id = $skuPrice->getSkuId(),
                    $price_id = $skuPrice->getPriceId()
                )],
                $project,
                'GBP',
                $nameOnPurchaseOrder,
                $email_address,
                $company_name
            );
        }

        throw new \Exception("Couldn't find price in DB for project.");
    }

    public function createPurchaseRequestForProject($projectName)
    {
        $project = $this->getProjectFromDB($projectName);
        $skuPriceRepo = $this->injector->make(DatabaseSkuPriceRepo::class);
        $skuPrices = $skuPriceRepo->getAllSkuPricesForProject($project);

        foreach ($skuPrices as $skuPrice) {
            return new PurchaseRequest(
                [new PurchaseRequestSku(
                    $price_unit = $skuPrice->getPriceCostInGBPPence(),
                    $quantity = 4,
                    $name = $skuPrice->getSkuName(),
                    $sku_id = $skuPrice->getSkuId(),
                    $price_id = $skuPrice->getPriceId()
                )],
                $project,
                'GBP'
            );
        }

        throw new \Exception("Couldn't find price in DB for project.");
    }

    public function createContentPolicyViolationReport(array $particulars)
    {
        $input = [
            'csp-report' =>
                [
                    'document-uri' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
                    'referrer' => '',
                    'violated-directive' => 'script-src-elem',
                    'effective-directive' => 'script-src-elem',
                    'original-policy' => 'default-src \'self\'; connect-src \'self\' https://checkout.stripe.com; frame-src *; img-src *; script-src \'self\' \'nonce-b8b4cf51f675e0fc13a6b959\' https://checkout.stripe.com; object-src *; style-src \'self\'; report-uri /csp',
                    'disposition' => 'enforce',
                    'blocked-uri' => 'inline',
                    'line-number' => 13,
                    'source-file' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
                    'status-code' => 200,
                    'script-sample' => '',
                ],
        ];

        foreach ($particulars as $key => $value) {
            $input['csp-report'][$key] = $value;
        }

        return ContentPolicyViolationReport::fromCSPPayload($input);
    }
}
