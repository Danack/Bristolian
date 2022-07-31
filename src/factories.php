<?php /** @noinspection ALL */

declare (strict_types = 1);

/**
 * This file contains factory functions that create objects from either
 * configuration values, user input or other external data.
 *
 * We deliberately do not import most of the classes referenced in this file to the current namespace
 * as that would make it harder to read, not easier.
 */

use Auryn\Injector;
use Bristolian\Config;
use Psr\Http\Message\ResponseInterface;
use SlimAuryn\AurynCallableResolver;
use Laminas\Diactoros\ResponseFactory;

function forbidden(\Auryn\Injector $injector): void
{
    $injector->make("Please don't use this object directly; create a more specific type to use.");
}


function createMemoryWarningCheck(
    Config $config,
    Injector $injector
) : \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck {

    if ($config->isProductionEnv()) {
        return $injector->make(\Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::class);
    }

    return $injector->make(\Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning::class);
}

/**
 * @return Redis
 * @throws Exception
 */
function createRedis(Config $config)
{
    $redisInfo = $config->getRedisInfo();

    $redis = new Redis();
    $redis->connect(
        $redisInfo['host'],
        $redisInfo['port'],
        $timeout = 2.0
    );
    $redis->auth($redisInfo['password']);
    $redis->ping();

    return $redis;
}



/**
 * This is a generic (i.e. not app or api specific) function.
 *
 * @param Config $config
 * @return \Bristolian\Data\ApiDomain
 */
function createApiDomain(Config $config)
{
    if ($config->isProductionEnv()) {
        return new \Bristolian\Data\ApiDomain("https://api.Bristolian.com");
    }

    return new \Bristolian\Data\ApiDomain("http://local.api.Bristolian.com");
}



