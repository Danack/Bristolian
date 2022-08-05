<?php

use AurynConfig\InjectionParams;

function injectionParams() : InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Auryn\Injector::class,
//        \Doctrine\ORM\EntityManager::class,
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
        \VarMap\VarMap::class => \VarMap\Psr7VarMap::class,
        \Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier::class =>
          \Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier::class,
        \Bristolian\CSPViolation\CSPViolationStorage::class =>
          \Bristolian\CSPViolation\RedisCSPViolationStorage::class,
        Psr\Http\Message\ResponseFactoryInterface::class =>
          \Laminas\Diactoros\ResponseFactory::class,
        \Bristolian\JsonInput\JsonInput::class =>
          \Bristolian\JsonInput\InputJsonInput::class,
    ];

    // Delegate the creation of types to callables.
    $delegates = [
        \Redis::class =>
          'createRedis',
        \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class
          => 'createMemoryWarningCheck',
        \Slim\App::class =>
          'createSlimAppForApi',

        \Bristolian\AppErrorHandler\AppErrorHandler::class
          => 'createJsonAppErrorHandler',
        \Bristolian\Middleware\ExceptionToJsonResponseMiddleware::class =>
            'createExceptionMiddlewareForApi',
    ];

    // Define some params that can be injected purely by name.
    $params = [];

    $prepares = [
    ];

    $defines = [];

    $injectionParams = new InjectionParams(
        $shares,
        $aliases,
        $delegates,
        $params,
        $prepares,
        $defines
    );

    return $injectionParams;
}
