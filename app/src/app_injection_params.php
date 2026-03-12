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
        \Bristolian\Cache\RequestTableAccessRecorder::class,
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

        \Bristolian\Service\YouTube\TranscriptFetcher::class =>
            \Bristolian\Service\YouTube\YouTubeTranscriptFetcher::class,

        Bristolian\ExternalMarkdownRenderer\ExternalMarkdownRenderer::class =>
          \Bristolian\ExternalMarkdownRenderer\StandardExternalMarkdownRenderer::class,

        Bristolian\Repo\AdminRepo\AdminRepo::class =>
            Bristolian\Repo\AdminRepo\PdoAdminRepo::class,

        \Bristolian\Repo\RoomTagRepo\RoomTagRepo::class =>
            \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::class,

        \Bristolian\Repo\FoiRequestRepo\FoiRequestRepo::class =>
          \Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo::class,

        \Bristolian\Repo\RoomFileRepo\RoomFileRepo::class =>
            \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class,

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

        Bristolian\Service\SecureTokenGenerator\SecureTokenGenerator::class =>
            \Bristolian\Service\SecureTokenGenerator\RandomBytesSecureTokenGenerator::class,

        \Bristolian\Service\UuidGenerator\UuidGenerator::class =>
            \Bristolian\Service\UuidGenerator\RamseyUuidGenerator::class,

        Bristolian\Repo\ApiTokenRepo\ApiTokenRepo::class =>
            \Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo::class,

        \Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo::class =>
            \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::class,

        \Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo::class =>
            \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::class,

        \Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::class =>
            \Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo::class,

        \Bristolian\Repo\VideoRepo\VideoRepo::class =>
            \Bristolian\Repo\VideoRepo\PdoVideoRepo::class,

        \Bristolian\Repo\RoomVideoRepo\RoomVideoRepo::class =>
            \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::class,

        \Bristolian\Repo\RoomVideoTranscriptRepo\RoomVideoTranscriptRepo::class =>
            \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::class,

        \Bristolian\Repo\RoomVideoTagRepo\RoomVideoTagRepo::class =>
            \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::class,

        \Bristolian\Cache\TableAccessRecorder::class =>
            \Bristolian\Cache\RequestTableAccessRecorder::class,
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

        \Bristolian\Cache\UnknownQueryHandler::class =>
            'createUnknownQueryHandler',

        \Bristolian\Service\UnknownCacheQueries\UnknownCacheQueriesProvider::class =>
            'createUnknownCacheQueriesProvider',

        \Bristolian\PdoSimple\PdoSimple::class =>
            'createPdoSimpleWithTableTracking',
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
