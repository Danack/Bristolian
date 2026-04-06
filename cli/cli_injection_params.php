<?php

use Bristolian\InjectionParams;
use Bristolian\Repo\BccTroRepo\BccTroRepo;


function injectionParams() : InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Redis::class,
        \Mailgun\Mailgun::class,
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
        Bristolian\Repo\AdminRepo\AdminRepo::class =>
            Bristolian\Repo\AdminRepo\PdoAdminRepo::class,

        \Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo::class =>
          \Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo::class,

        \Bristolian\Service\WebPushService\WebPushService::class =>
          \Bristolian\Service\WebPushService\StandardWebPushService::class,

        \Bristolian\Repo\RoomRepo\RoomRepo::class =>
          \Bristolian\Repo\RoomRepo\PdoRoomRepo::class,

        \Bristolian\Service\RoomFileStorage\RoomFileStorage::class =>
            \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::class,

        \Bristolian\Service\ObjectStore\RoomFileObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardRoomFileObjectStore::class,

        \Bristolian\Repo\RoomFileRepo\RoomFileRepo::class =>
            \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class,

        \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::class =>
            \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::class,

        \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::class =>
            \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::class,

        \Bristolian\MoonAlert\MoonAlertRepo::class =>
            \Bristolian\MoonAlert\StandardMoonAlertRepo::class,

        \Bristolian\Repo\EmailQueue\EmailQueue::class =>
            \Bristolian\Repo\EmailQueue\PdoEmailQueue::class,

        \Bristolian\Repo\LinkRepo\LinkRepo::class =>
            \Bristolian\Repo\LinkRepo\PdoLinkRepo::class,

        \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::class =>
            \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class,

        \Bristolian\Repo\VideoRepo\VideoRepo::class =>
            \Bristolian\Repo\VideoRepo\PdoVideoRepo::class,

        \Bristolian\Repo\RoomVideoRepo\RoomVideoRepo::class =>
            \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::class,

        \Bristolian\Service\EmailSender\EmailClient::class =>
            \Bristolian\Service\EmailSender\MailgunEmailClient::class,

        \Bristolian\Repo\ProcessorRepo\ProcessorRepo::class =>
            \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo::class,

        \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::class =>
            \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo::class,

        Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::class =>
            \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo::class,

        \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::class =>
          \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo::class,

        Bristolian\Service\ObjectStore\BristolianStairImageObjectStore::class =>
          \Bristolian\Service\ObjectStore\StandardBristolianStairImageObjectStore::class,

        \Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage::class =>
          \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::class,

        \Bristolian\Service\RoomMessageService\RoomMessageService::class =>
          \Bristolian\Service\RoomMessageService\StandardRoomMessageService::class,

        \Bristolian\Config\EnvironmentName::class => \Bristolian\Config\Config::class,

        \Bristolian\Service\HttpFetcher\HttpFetcher::class =>
          \Bristolian\Service\HttpFetcher\FetchUriHttpFetcher::class,

        \Bristolian\Service\BccTroFetcher\BccTroFetcher::class =>
          \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::class,

        \Bristolian\Service\WhatDoTheyKnowFeedFetcher\WhatDoTheyKnowFeedFetcher::class =>
            \Bristolian\Service\WhatDoTheyKnowFeedFetcher\StandardWhatDoTheyKnowFeedFetcher::class,

        \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\WhatDoTheyKnowRequestEventRepo::class =>
            \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo::class,

        \Bristolian\Service\UuidGenerator\UuidGenerator::class =>
            \Bristolian\Service\UuidGenerator\RamseyUuidGenerator::class,

        \Bristolian\Repo\BccTroRepo\BccTroRepo::class =>
            \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::class,

        \Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor::class =>
            \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::class,

        \Bristolian\Repo\MemeStorageRepo\MemeStorageRepo::class =>
            \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class,

        \Bristolian\Service\ObjectStore\MemeObjectStore::class =>
            \Bristolian\Service\ObjectStore\StandardMemeObjectStore::class,

        \Bristolian\Repo\MemeTagRepo\MemeTagRepo::class =>
            \Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo::class,

        \Bristolian\Repo\MemeTextRepo\MemeTextRepo::class =>
            \Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo::class,

        \Bristolian\Service\CliOutput\CliOutput::class =>
            \Bristolian\Service\CliOutput\EchoCliOutput::class,

        \Bristolian\Service\DailyProcessorSchedule\DailyProcessorSchedule::class =>
            \Bristolian\Service\DailyProcessorSchedule\StandardDailyProcessorSchedule::class,

        \Bristolian\Service\MemeImageOcr\MemeImageOcrRunner::class =>
            \Bristolian\Service\MemeImageOcr\ProcOpenPythonMemeImageOcrRunner::class,

        \Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCached::class =>
            \Bristolian\Service\MemeFileLocalCache\FlysystemEnsureMemeFileCached::class,

        Bristolian\Repo\RoomTagRepo\RoomTagRepo::class =>
            \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::class,

        Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::class =>
            Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo::class,

        Bristolian\Repo\UserRepo\UserRepo::class =>
            \Bristolian\Repo\UserRepo\PdoUserRepo::class,

        Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::class =>
            Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::class,

        Bristolian\MarkdownRenderer\MarkdownRenderer::class =>
            \Bristolian\MarkdownRenderer\CommonMarkRenderer::class,

    ];

    // Delegate the creation of types to callables.
    $delegates = [
        \PDO::class => 'createPDOForUser',
        \Bristolian\Filesystem\LocalFilesystem::class =>
            'createLocalFilesystem',
        \Bristolian\Filesystem\LocalCacheFilesystem::class =>
            'createLocalCacheFilesystem',
        \Bristolian\Filesystem\MemeFilesystem::class =>
            'createMemeFilesystem',
        \Bristolian\Filesystem\RoomFileFilesystem::class =>
            'createRoomFileFilesystem',
        \Mailgun\Mailgun::class => 'createMailgun',
        \Bristolian\Filesystem\UserDocumentsFilesystem::class =>
            'createUserDocumentsFilesystem',
        \Redis::class =>
            'createRedis',
        \Bristolian\Filesystem\BristolStairsFilesystem::class => 'createBristolStairsFilesystem'
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


return injectionParams();
