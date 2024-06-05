<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/fixtures.php";
require_once __DIR__ . "/../config.generated.php";
require_once __DIR__ . "/../src/factories.php";
require_once __DIR__ . "/test_injection_params.php";

use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\UserSession;
use Bristolian\MockUserSession;


function getTestingUserSession(PdoAdminRepo $pdoAdminRepo): UserSession
{
    $user = $pdoAdminRepo->getAdminUser("testing@example.com", 'testing');

    return new MockUserSession(
        true,
        $user->getUserId(),
        $user->getEmailAddress()
    );
}

/**
 * @return \DI\Injector
 */
function createTestInjector()
{
    $injectionParams = testInjectionParams();

    $injector = new \DI\Injector();
    $injectionParams->addToInjector($injector);

//    foreach ($shareDoubles as $shareDouble) {
//        $injector->share($shareDouble);
//    }

    $injector->share($injector); //Yolo ServiceLocator
    return $injector;
}


$injector = createTestInjector();
$pdoAdminRepo = $injector->make(\Bristolian\Repo\AdminRepo\PdoAdminRepo::class);
$user = $pdoAdminRepo->getAdminUser("testing@example.com", 'testing');

if ($user === null) {
    $data = [
        'email_address' => "testing@example.com",
        'password' => 'testing',
    ];

    $createUserParams = \Bristolian\DataType\CreateUserParams::createFromArray($data);

    $pdoAdminRepo->addUser($createUserParams);
}


