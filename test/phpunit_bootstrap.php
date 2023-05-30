<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/fixtures.php";
require_once __DIR__ . "/../config.generated.php";
require_once __DIR__ . "/../src/factories.php";
require_once __DIR__ . "/../cli/cli_injection_params.php";

//function createProcessedValuesFromArray(array $keyValues): ProcessedValues
//{
//    $processedValues = [];
//
//    foreach ($keyValues as $key => $value) {
//        $extractRule = new GetInt();
//        $inputParameter = new InputTypeSpec($key, $extractRule);
//        $processedValues[] = new ProcessedValue($inputParameter, $value);
//    }
//
//    return ProcessedValues::fromArray($processedValues);
//}

/**
 * @param array<string, mixed> $testDoubles
 * @param array<string, mixed> $shareDoubles
 * @return \DI\Injector
 */
function createInjector(array $testDoubles = [], array $shareDoubles = [])
{
    $injectionParams = injectionParams($testDoubles);

    $injector = new \DI\Injector();
    $injectionParams->addToInjector($injector);

    foreach ($shareDoubles as $shareDouble) {
        $injector->share($shareDouble);
    }

    $injector->share($injector); //Yolo ServiceLocator
    return $injector;
}