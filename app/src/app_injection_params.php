<?php

use Bristolian\InjectionParams;
use Bristolian\Repo\DbInfo\DbInfo;

function injectionParams()
{
    // These classes will only be created once by the injector.
    $shares = [
        \DI\Injector::class,
        \Slim\App::class,
        \Bristolian\CSPViolation\RedisCSPViolationStorage::class,
        \Bristolian\Service\RequestNonce::class,
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
        \VarMap\VarMap::class =>
          \VarMap\Psr7VarMap::class,
        \Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier::class =>
          \Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier::class,
        \Bristolian\CSPViolation\CSPViolationReporter::class =>
          \Bristolian\CSPViolation\RedisCSPViolationStorage::class,
        \Bristolian\CSPViolation\CSPViolationStorage::class =>
          \Bristolian\CSPViolation\RedisCSPViolationStorage::class,
        Psr\Http\Message\ResponseFactoryInterface::class =>
          \Laminas\Diactoros\ResponseFactory::class,

        \Bristolian\MarkdownRenderer\MarkdownRenderer::class =>
            \Bristolian\MarkdownRenderer\CommonMarkRenderer::class,

        \Bristolian\Repo\DbInfo\DbInfo::class =>
            \Bristolian\Repo\DbInfo\PdoDbInfo::class,
    ];



    // Delegate the creation of types to callables.
    $delegates = [
        \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class =>
          'createMemoryWarningCheck',
        \Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware::class =>
          'createExceptionToErrorPageResponseMiddleware',
        \PDO::class =>
          'createPDOForUser',
        \Slim\App::class =>
          'createSlimAppForApp',
        \Bristolian\AppErrorHandler\AppErrorHandler::class =>
          'createHtmlAppErrorHandler',
        \Bristolian\Data\ApiDomain::class =>
          'createApiDomain',
        \Redis::class =>
          'createRedis',
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
