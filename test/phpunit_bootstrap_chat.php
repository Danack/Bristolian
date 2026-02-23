<?php

require_once __DIR__ . "/../chat/src/chat_includes.php";





/**
 * Returns a unique per test run id. Though doesn't account for tests
 * running in parallel.
 * @return string
 */
function create_test_uniqid(): string
{
    static $counter = 0;
    static $previous_time = null;

    $new_time = time();

    if ($previous_time === null || $new_time > $previous_time) {
        // seconds have changed.
        $counter = 0;
    }

    $id = 'time_' . time() . '_counter_' . $counter . '_rand_' . random_int(1000, 9999);;

    $counter += 1;

    return $id;
}


/**
 * @return \DI\Injector
 */
function createTestInjector()
{
//    $injectionParams = testInjectionParams();

    $injector = new \DI\Injector();
//    $injectionParams->addToInjector($injector);

//    foreach ($shareDoubles as $shareDouble) {
//        $injector->share($shareDouble);
//    }

    $injector->share($injector); //Yolo ServiceLocator
    return $injector;
}