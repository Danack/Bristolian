<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/fixtures.php";
require_once __DIR__ . "/../config.generated.php";
require_once __DIR__ . "/../src/factories.php";
require_once __DIR__ . "/../src/react_widgets.php";
require_once __DIR__ . "/../credentials.php";
require_once __DIR__ . "/test_injection_params.php";
require_once __DIR__ . "/../src/error_functions.php";
require_once __DIR__ . "/../api/src/api_convert_exception_to_json_functions.php";
require_once __DIR__ . "/../api/src/api_factories.php";
require_once __DIR__ . "/../api/src/api_functions.php";
require_once __DIR__ . "/../api/src/api_injection_params.php";
require_once __DIR__ . "/../api/src/api_routes.php";
require_once __DIR__ . "/../src/site_html.php";

use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Session\UserSession;
use Bristolian\Session\MockUserSession;


/**
 * Creates a UserSession from the test user account stored in the database.
 *
 * @param PdoAdminRepo $pdoAdminRepo
 * @return UserSession
 */
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


