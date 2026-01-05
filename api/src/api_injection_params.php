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

        \Bristolian\Session\AppSessionManagerInterface::class =>
            \Bristolian\Session\AppSessionManager::class,

        \Bristolian\CSPViolation\CSPViolationStorage::class =>
          \Bristolian\CSPViolation\RedisCSPViolationStorage::class,

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

//        \Bristolian\Service\FileStorageProcessor\FileStorageProcessor::class =>
//            \Bristolian\Service\FileStorageProcessor\StandardFileStorageProcessor::class,

        \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::class =>
            \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::class,

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

        \Bristolian\Service\ObjectStore\MemeObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardMemeObjectStore::class,

        \Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor::class =>
            \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::class,

        \Bristolian\Service\Mailgun\PayloadValidator::class =>
            \Bristolian\Service\Mailgun\StandardPayloadValidator::class,

        \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::class =>
            \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo::class,

        \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::class =>
            \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo::class,

        \Bristolian\Repo\UserProfileRepo\UserProfileRepo::class =>
            \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::class,

        \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::class =>
            \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::class,

        \Bristolian\Service\AvatarImageStorage\AvatarImageStorage::class =>
            \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage::class,

        \Bristolian\Service\ObjectStore\AvatarImageObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardAvatarImageObjectStore::class,

        \Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage::class =>
            \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::class,

        \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::class =>
            \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo::class,

        \Bristolian\Service\ObjectStore\BristolianStairImageObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardBristolianStairImageObjectStore::class,

        Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::class =>
            Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::class,

        \Bristolian\Service\RoomMessageService\RoomMessageService::class =>
            \Bristolian\Service\RoomMessageService\StandardRoomMessageService::class,

        \Bristolian\Repo\MemeTagRepo\MemeTagRepo::class =>
            \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::class,

        \Bristolian\Repo\MemeTextRepo\MemeTextRepo::class =>
            \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::class,
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

        \Bristolian\Filesystem\BristolStairsFilesystem::class =>
            'createBristolStairsFilesystem',

        \Bristolian\Filesystem\RoomFileFilesystem::class =>
            'createRoomFileFilesystem',

        \Bristolian\Filesystem\LocalCacheFilesystem::class =>
            'createLocalCacheFilesystem',

        \Bristolian\Filesystem\AvatarImageFilesystem::class =>
            'createAvatarImageFilesystem',

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
