<?php

use Bristolian\InjectionParams;

function apiInjectionParams() : InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Di\Injector::class,
        \Asm\SessionManager::class,
        \Bristolian\Session\AppSessionManager::class,
        \Bristolian\Session\AppSession::class
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
        \VarMap\VarMap::class => \VarMap\Psr7VarMap::class,

        \Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier::class =>
          \Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier::class,

        \Bristolian\CSPViolation\CSPViolationStorage::class =>
          \Bristolian\CSPViolation\RedisCSPViolationStorage::class,

        \Asm\RequestSessionStorage::class =>
            \StandardRequestSessionStorage::class,

        \Psr\Http\Message\ResponseFactoryInterface::class =>
          \Laminas\Diactoros\ResponseFactory::class,

        \Bristolian\JsonInput\JsonInput::class =>
          \Bristolian\JsonInput\InputJsonInput::class,

        \Bristolian\Basic\ErrorLogger::class =>
            \Bristolian\Basic\StandardErrorLogger::class,

        Asm\Driver::class => \Asm\Predis\PredisDriver::class,

        \Bristolian\Repo\RoomFileRepo\RoomFileRepo::class =>
            \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class,

        \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::class =>
            \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class,

        \Bristolian\Service\RoomFileStorage\RoomFileStorage::class =>
            \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::class,

        \Bristolian\Service\FileStorageProcessor\FileStorageProcessor::class =>
            \Bristolian\Service\FileStorageProcessor\StandardFileStorageProcessor::class,

        \Bristolian\Repo\FileStorageInfoRepo\FileStorageInfoRepo::class =>
            \Bristolian\Repo\FileStorageInfoRepo\PdoFileStorageInfoRepo::class,

        \Bristolian\Service\ObjectStore\RoomFileObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardRoomFileObjectStore::class,

        \Bristolian\UploadedFiles\UploadedFiles::class =>
            \Bristolian\UploadedFiles\ServerFilesUploadedFiles::class,

        \Bristolian\Session\UserSession::class =>
            \Bristolian\Session\AppSession::class,

        \Bristolian\Repo\RoomRepo\RoomRepo::class =>
            \Bristolian\Repo\RoomRepo\PdoRoomRepo::class,

        \Bristolian\Repo\LinkRepo\LinkRepo::class =>
            \Bristolian\Repo\LinkRepo\PdoLinkRepo::class,

        \Bristolian\Session\OptionalUserSession::class =>
            \Bristolian\Session\StandardOptionalUserSession::class,

        \Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo::class =>
            \Bristolian\Repo\RoomSourceLinkRepo\PdoRoomSourceLinkRepo::class,

        \Bristolian\Repo\MemeStorageRepo\MemeStorageRepo::class =>
            \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class,

        \Bristolian\Service\MemeStorage\MemeStorage::class =>
            \Bristolian\Service\MemeStorage\StandardMemeStorage::class,

        \Bristolian\Service\ObjectStore\MemeObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardMemeObjectStore::class,

        \Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor::class =>
            \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::class,

    ];
    // Delegate the creation of types to callables.
    $delegates = [
        \Redis::class =>
          'createRedis',

        \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class
          => 'createMemoryWarningCheck',

        \Slim\App::class =>
          'createSlimAppForApi',

        \Bristolian\Middleware\ExceptionToJsonResponseMiddleware::class =>
            'createExceptionMiddlewareForApi',

        \Asm\SessionConfig::class =>
            'createSessionConfig',

        \Predis\Client::class =>
            'createPredisClient',

        \PDO::class =>
            'createPDOForUser',

        \Bristolian\Filesystem\LocalFilesystem::class =>
            'createLocalFilesystem',

        \Bristolian\Filesystem\MemeFilesystem::class =>
            'createMemeFilesystem',

        \Bristolian\Filesystem\RoomFileFilesystem::class =>
            'createRoomFileFilesystem',

        \Bristolian\Filesystem\LocalCacheFilesystem::class =>
            'createLocalCacheFilesystem',

        \Bristolian\Session\StandardOptionalUserSession::class =>
            'createOptionalUserSession',

        \Bristolian\Session\AppSession::class =>
            'createAppSession',
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
