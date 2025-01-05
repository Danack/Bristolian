<?php /** @noinspection ALL */

declare (strict_types = 1);

/**
 * This file contains factory functions that create objects from either
 * configuration values, user input or other external data.
 *
 * We deliberately do not import most of the classes referenced in this file to the current namespace
 * as that would make it harder to read, not easier.
 */

use Aws\S3\S3Client;
use Bristolian\Config\Config;
use Bristolian\Service\DeployLogRenderer\DeployLogRenderer;
use DI\Injector;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use SlimAuryn\AurynCallableResolver;

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


function createRedisCachedUrlFetcher(\Redis $redis): \UrlFetcher\RedisCachedUrlFetcher
{
    $urlFetcher = new \UrlFetcher\CurlUrlFetcher();

    return new \UrlFetcher\RedisCachedUrlFetcher($redis, $urlFetcher);
}


/**
 * @return array<string, string|int>
 */
function getRedisConfig(Config $config): array
{
    $redisConfig = $config->getRedisInfo();
    $redisConfig = array(
        "scheme" => "tcp",
        "host" => $redisConfig->host,
        "port" => $redisConfig->port,
        "password" => $redisConfig->password
    );

    return $redisConfig;
}

/**
 * @return array<string, string>
 */
function getRedisOptions(): array
{
//    static $unique = null;
//
//    if ($unique == null) {
//        $unique = date("Ymdhis").uniqid();
//    }

    $redisOptions = array(
        'profile' => '2.6',
        // This should be random for testing
        'prefix' => 'bristolian:',
    );

    return $redisOptions;
}


function createPredisClient(Config $config): \Predis\Client
{
    return new \Predis\Client(getRedisConfig($config), getRedisOptions());
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
        return new \Bristolian\Data\ApiDomain("https://api.bristolian.org");
    }

    return new \Bristolian\Data\ApiDomain("http://local.api.bristolian.org");
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


function createSessionConfig(): Asm\SessionConfig
{
    return new Asm\SessionConfig(
        "john_is_my_name",
        3600,
    );
}

function createLocalFilesystem(): \Bristolian\Filesystem\LocalFilesystem
{
    // SETUP
    $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter(__DIR__ . "/../data/temp");
    $filesystem = new \Bristolian\Filesystem\LocalFilesystem($adapter);

    return $filesystem;
}


function createLocalCacheFilesystem(): \Bristolian\Filesystem\LocalCacheFilesystem
{

    $rootLocation = __DIR__ . "/../data/cache";
    $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($rootLocation);

    // LocalFilesystemAdapter has no way of reporting the location
    // as we're just reading directly from disk in some places in the code
    // we use an extension class to make life easier.
    $filesystem = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $rootLocation);

    return $filesystem;
}


function createMemeFilesystem(Config $config): \Bristolian\Filesystem\MemeFilesystem
{
    $bucketName = 'bristolian-memes';

    if ($config->isProductionEnv() !== true) {
        $bucketName = 'bristolian-memes-dev';
    }

    // SETUP
    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud'
    ]);

    // The internal adapter
    $adapter = new AwsS3V3Adapter(
        $client,
        $bucketName,
        // Optional path prefix
        '', //'path/prefix',
        new PortableVisibilityConverter(
            Visibility::PRIVATE
        )
    );

    $config = [];

    // The FilesystemOperator
    $filesystem = new \Bristolian\Filesystem\MemeFilesystem($adapter, $config);

    return $filesystem;
}


function createRoomFileFilesystem(Config $config): \Bristolian\Filesystem\RoomFileFilesystem
{
    $bucketName = 'bristolian-room-files';

    if ($config->isProductionEnv() !== true) {
        $bucketName = 'bristolian-room-files-dev';
    }

    // SETUP
    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud'
    ]);

    // The internal adapter
    $adapter = new AwsS3V3Adapter(
        $client,
        $bucketName,
        // Optional path prefix
        '', //'path/prefix',
        new PortableVisibilityConverter(
            Visibility::PRIVATE // or ::PRIVATE
        )
    );

    $config = [];

    // The FilesystemOperator
    $filesystem = new \Bristolian\Filesystem\RoomFileFilesystem($adapter, $config);

    return $filesystem;
}















/**
 * This is a generic (i.e. not app or api specific) function.
 *
 */
function createDeployLogRenderer(Config $config): DeployLogRenderer
{
    if ($config->isProductionEnv()) {
        return new \Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer();
    }

    return new \Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer();
}


/**
 * @param Config $config
 * @return \Mailgun\Mailgun
 */
function createMailgun(Config $config): \Mailgun\Mailgun
{
    $mg = \Mailgun\Mailgun::create(
        $config->getMailgunApiKey(),
        'https://api.eu.mailgun.net'
    );

    return $mg;
}

function createOptionalUserSession(
    \Bristolian\Session\AppSessionManager $appSessionManager
): \Bristolian\Session\StandardOptionalUserSession {
    return new \Bristolian\Session\StandardOptionalUserSession(
        $appSessionManager->getCurrentAppSession()
    );
}




function createAppSession(
    \Bristolian\Session\AppSessionManager $appSessionManager
): \Bristolian\Session\AppSession {

    $app_session = $appSessionManager->getCurrentAppSession();

    if ($app_session === null) {
        throw new \Bristolian\Exception\UnauthorisedException(
            "Something depends on AppSession but user not logged in."
        );
    }

    return $app_session;
}
