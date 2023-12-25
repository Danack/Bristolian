<?php

use Bristolian\InjectionParams;



function injectionParams() : InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Redis::class,
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

    ];

//        $environment = getConfig(Config::OSF_ENVIRONMENT);
//        if ($environment !== 'production') {
//            $aliases[\Osf\Service\NotificationSender\NotificationSender::class] =
//                \Osf\Service\NotificationSender\LocalDevNotificationSender::class;
//        }

    // Delegate the creation of types to callables.
    $delegates = [
        \PDO::class => 'createPDOForUser',
//            \Redis::class => 'createRedis',
//            \Doctrine\ORM\EntityManager::class => 'createDoctrineEntityManager',

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
