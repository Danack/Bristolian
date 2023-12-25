<?php

declare(strict_types = 1);

namespace BristolianTest\Repo;

use Bristolian\DataType\CreateUserParams;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Model\AdminUser;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;

trait TestPlaceholders
{
    /**
     * @var \DI\Injector
     */
    private $injector;

    /**
     * @param Object[] $testDoubles
     * @return void
     * @throws \DI\ConfigException
     * @throws \DI\InjectionException
     */
    protected function initLoggedInUser(array $testDoubles): void
    {
        $placeholders = [
            JsonInput::class
        ];

        foreach ($testDoubles as $testDouble) {
            foreach ($placeholders as $placeholder) {
                if ($testDouble instanceof $placeholder) {
                    $this->injector->alias($placeholder, get_class($testDouble));
                    $this->injector->share($testDouble);
                }
            }
        }

        $session = $this->injector->execute('getTestingUserSession');

        $this->injector->alias(
            \Bristolian\UserSession::class,
            \Bristolian\MockUserSession::class
        );
        $this->injector->share($session);
    }

    public function setup(): void
    {
        parent::setup();
        $this->injector = createTestInjector();
    }

    private function createInjector()
    {
        if ($this->injector === null) {
            $this->injector = createTestInjector();
        }
    }

    public function createTestAdminUser(): AdminUser
    {
        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $pdo_admin_repo = $this->injector->make(PdoAdminRepo::class);
        $adminUser = $pdo_admin_repo->addUser($createAdminUserParams);

        return $adminUser;
    }

//
//    public function createContentPolicyViolationReport(array $particulars)
//    {
//        $input = [
//            'csp-report' =>
//                [
//                    'document-uri' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
//                    'referrer' => '',
//                    'violated-directive' => 'script-src-elem',
//                    'effective-directive' => 'script-src-elem',
//                    'original-policy' => 'default-src \'self\'; connect-src \'self\' https://checkout.stripe.com; frame-src *; img-src *; script-src \'self\' \'nonce-b8b4cf51f675e0fc13a6b959\' https://checkout.stripe.com; object-src *; style-src \'self\'; report-uri /csp',
//                    'disposition' => 'enforce',
//                    'blocked-uri' => 'inline',
//                    'line-number' => 13,
//                    'source-file' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
//                    'status-code' => 200,
//                    'script-sample' => '',
//                ],
//        ];
//
//        foreach ($particulars as $key => $value) {
//            $input['csp-report'][$key] = $value;
//        }
//
//        return ContentPolicyViolationReport::fromCSPPayload($input);
//    }
}
