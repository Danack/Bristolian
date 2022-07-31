<?php

use Bristolian\InjectionParams;
//use Bristolian\Config;

if (function_exists('injectionParams') == false) {

    function injectionParams() : InjectionParams
    {
        // These classes will only be created once by the injector.
        $shares = [
//            \Doctrine\ORM\EntityManager::class,
            \Redis::class,
//            \Airbrake\Notifier::class
        ];

        // Alias interfaces (or classes) to the actual types that should be used
        // where they are required.
        $aliases = [
            \Bristolian\Repo\PhpBugsFetcher\PhpBugsFetcher::class =>
              \Bristolian\Repo\PhpBugsFetcher\CurlPhpBugsFetcher::class,
            \Bristolian\Repo\PhpBugsStorage\PhpBugsStorage::class =>
              \Bristolian\Repo\PhpBugsStorage\RedisPhpBugsStorage::class,
        ];

//        $environment = getConfig(Config::OSF_ENVIRONMENT);
//        if ($environment !== 'production') {
//            $aliases[\Osf\Service\NotificationSender\NotificationSender::class] =
//                \Osf\Service\NotificationSender\LocalDevNotificationSender::class;
//        }

        // Delegate the creation of types to callables.
        $delegates = [
//            \PDO::class => 'createPDO',
            \Redis::class => 'createRedis',
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
}


return injectionParams();
