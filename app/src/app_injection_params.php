<?php

use Bristolian\InjectionParams;


function injectionParams()
{
    // These classes will only be created once by the injector.
    $shares = [
        \DI\Injector::class,
        \Slim\App::class,
        \Bristolian\CSPViolation\RedisCSPViolationStorage::class,
        \Bristolian\Service\RequestNonce::class,
        \Asm\SessionManager::class,
        \Bristolian\Session\AppSessionManager::class,
        \Bristolian\SiteHtml\ExtraAssets::class,
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

        \Bristolian\Session\AppSessionManagerInterface::class =>
            \Bristolian\Session\AppSessionManager::class,

        \Bristolian\Config\AssetLinkEmitterConfig::class =>
            \Bristolian\Config\Config::class,

        Bristolian\Basic\ErrorLogger::class =>
            \Bristolian\Basic\StandardErrorLogger::class,

        \Bristolian\Service\ObjectStore\RoomFileObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardRoomFileObjectStore::class,

        Psr\Http\Message\ResponseFactoryInterface::class =>
          \Laminas\Diactoros\ResponseFactory::class,

        \Bristolian\JsonInput\JsonInput::class =>
          \Bristolian\JsonInput\InputJsonInput::class,

        \Bristolian\Session\UserSession::class =>
            \Bristolian\Session\AppSession::class,

        \Bristolian\MarkdownRenderer\MarkdownRenderer::class =>
            \Bristolian\MarkdownRenderer\CommonMarkRenderer::class,

        \Bristolian\Repo\DbInfo\DbInfo::class =>
            \Bristolian\Repo\DbInfo\PdoDbInfo::class,

        \Bristolian\Session\OptionalUserSession::class =>
            \Bristolian\Session\StandardOptionalUserSession::class,

        Asm\Driver::class => \Asm\Predis\PredisDriver::class,

        UrlFetcher\UrlFetcher::class =>
          UrlFetcher\RedisCachedUrlFetcher::class,

        Bristolian\ExternalMarkdownRenderer\ExternalMarkdownRenderer::class =>
          \Bristolian\ExternalMarkdownRenderer\StandardExternalMarkdownRenderer::class,

        Bristolian\Repo\AdminRepo\AdminRepo::class =>
            Bristolian\Repo\AdminRepo\PdoAdminRepo::class,

        \Bristolian\Repo\TagRepo\TagRepo::class =>
          \Bristolian\Repo\TagRepo\PdoTagRepo::class,

        \Bristolian\Repo\FoiRequestRepo\FoiRequestRepo::class =>
          \Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo::class,

        \Bristolian\Repo\RoomFileRepo\RoomFileRepo::class =>
            \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class,

        \user_repo\UserRepo\UserRepo::class =>
          deadish\HardcodedUserRepo::class,

        \deadish\UserDocumentRepo\UserDocumentRepo::class =>
          \deadish\UserDocumentRepo\HardcodedUserDocumentRepo::class,

        \Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo::class =>
          \Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo::class,

        \Bristolian\Service\RoomFileStorage\RoomFileStorage::class =>
            \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::class,

        \Bristolian\UploadedFiles\UploadedFiles::class =>
            \Bristolian\UploadedFiles\ServerFilesUploadedFiles::class,

        \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::class =>
          \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::class,

        \Bristolian\Repo\MemeTagRepo\MemeTagRepo::class =>
            \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::class,

        \Bristolian\Repo\UserSearch\UserSearch::class =>
            \Bristolian\Repo\UserSearch\PdoUserSearch::class,

        \Bristolian\UserNotifier\UserNotifier::class =>
          \Bristolian\UserNotifier\StandardUserNotifier::class,

        \Bristolian\Repo\RoomRepo\RoomRepo::class =>
            \Bristolian\Repo\RoomRepo\PdoRoomRepo::class,

        \Bristolian\Repo\LinkRepo\LinkRepo::class =>
            \Bristolian\Repo\LinkRepo\PdoLinkRepo::class,

        Bristolian\Repo\ProcessorRepo\ProcessorRepo::class =>
            Bristolian\Repo\ProcessorRepo\PdoProcessorRepo::class,

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

        \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::class =>
          \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo::class,

        Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::class =>
            \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::class,

        \Bristolian\Repo\MemeStorageRepo\MemeStorageRepo::class =>
            \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class,


        Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::class =>
            \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::class,

        Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::class =>
            \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class,
    ];

    // Delegate the creation of types to callables.
    $delegates = [
        \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class =>
          'createMemoryWarningCheck',
        \Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware::class =>
          'createExceptionToErrorPageResponseMiddleware',
        \PDO::class =>
          'createPDOForUser',
        \Bristolian\Data\ApiDomain::class =>
          'createApiDomain',

        \Redis::class =>
          'createRedis',

        \Bristolian\Filesystem\AvatarImageFilesystem::class =>
            'createAvatarImageFilesystem',

        \UrlFetcher\RedisCachedUrlFetcher::class => 'createRedisCachedUrlFetcher',

        \Predis\Client::class =>
          'createPredisClient',

        \Asm\SessionConfig::class =>
            'createSessionConfig',

        \Bristolian\Filesystem\LocalFilesystem::class =>
          'createLocalFilesystem',

        \Bristolian\Filesystem\MemeFilesystem::class =>
            'createMemeFilesystem',

        \Bristolian\Filesystem\RoomFileFilesystem::class =>
            'createRoomFileFilesystem',

        \Bristolian\Filesystem\LocalCacheFilesystem::class =>
            'createLocalCacheFilesystem',

        \Bristolian\Service\DeployLogRenderer\DeployLogRenderer::class =>
            'createDeployLogRenderer',

        \Bristolian\Filesystem\BristolStairsFilesystem::class =>
            'createBristolStairsFilesystem',

        \Bristolian\Middleware\ContentSecurityPolicyMiddleware::class =>
            'createContentSecurityPolicyMiddleware',

        \Bristolian\Session\StandardOptionalUserSession::class => 'createOptionalUserSession',

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
