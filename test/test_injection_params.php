<?php

use Bristolian\InjectionParams;

function testInjectionParams() : InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Redis::class,
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
//        Bristolian\Repo\AdminRepo\AdminRepo::class =>
//            Bristolian\Repo\AdminRepo\PdoAdminRepo::class,
//
//        \Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo::class =>
//            \Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo::class,
//
//        \Bristolian\MarkdownRenderer\MarkdownRenderer::class =>
//            \Bristolian\MarkdownRenderer\CommonMarkRenderer::class,
//
//        \Bristolian\Service\RoomMessageService\RoomMessageService::class =>
//            \Bristolian\Service\RoomMessageService\FakeRoomMessageService::class

        // Tinned Fish Diary
        \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::class =>
            \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::class,
    ];

    // Delegate the creation of types to callables.
    $delegates = [
        \PDO::class => 'createPDOForUser',
        \Redis::class =>
            'createRedis',

        \Bristolian\Filesystem\RoomFileFilesystem::class =>
            'createRoomFileFilesystem',
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
