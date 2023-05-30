<?php /** @noinspection ALL */

declare (strict_types = 1);

/**
 * This file contains factory functions that create objects from either
 * configuration values, user input or other external data.
 *
 * We deliberately do not import most of the classes referenced in this file to the current namespace
 * as that would make it harder to read, not easier.
 */

use DI\Injector;
use Bristolian\Config;
use Psr\Http\Message\ResponseInterface;
use SlimAuryn\AurynCallableResolver;
use Laminas\Diactoros\ResponseFactory;

function forbidden(\DI\Injector $injector): void
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
    $redisConfig = $config->getRedisInfo();

    $redis = new Redis();
    $redis->connect(
        $redisConfig->host,
        $redisConfig->port,
        $timeout = 2.0
    );
    $redis->auth($redisConfig->password);
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
        return new \Bristolian\Data\ApiDomain("https://api.Bristolian.org");
    }

    return new \Bristolian\Data\ApiDomain("http://local.api.Bristolian.org");
}



/**
 * @return PDO
 * @throws Exception
 */
function createPDOForUser(Config $config)
{
    $db_config = $config->getDatabaseUserConfig();

    $dsn_string = sprintf(
        'mysql:host=%s;dbname=%s',
        $db_config->host,
        $db_config->schema
    );

    $pdo_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
        PDO::MYSQL_ATTR_FOUND_ROWS => true
    ];

    try {
        $pdo = new \PDO(
            $dsn_string,
            $db_config->username,
            $db_config->password,
            $pdo_options
        );
    }
    catch (\Exception $e) {
        throw new \Exception(
            "Error creating PDO:" . $e->getMessage(),
            $e->getCode(),
            $e
        );
    }

    return $pdo;
}
