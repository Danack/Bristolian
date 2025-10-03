<?php

use Bristolian\InjectionParams;



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

        \Bristolian\MoonAlert\MoonAlertRepo::class =>
            \Bristolian\MoonAlert\StandardMoonAlertRepo::class,

        Bristolian\Service\MoonAlertNotifier\MoonAlertNotifier::class =>
            \Bristolian\Service\MoonAlertNotifier\StandardMoonAlertNotifier::class,

        \Bristolian\Repo\EmailQueue\EmailQueue::class =>
            \Bristolian\Repo\EmailQueue\PdoEmailQueue::class,

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
          \Bristolian\Service\RoomMessageService\RedisRoomMessageService::class

    ];

    // Delegate the creation of types to callables.
    $delegates = [
        \PDO::class => 'createPDOForUser',

        \Bristolian\Filesystem\LocalFilesystem::class =>
            'createLocalFilesystem',
        \Bristolian\Filesystem\MemeFilesystem::class =>
            'createMemeFilesystem',
        \Mailgun\Mailgun::class => 'createMailgun',


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
