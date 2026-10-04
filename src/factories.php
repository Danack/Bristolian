<?php /** @noinspection ALL */

declare(strict_types = 1);

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
use Bristolian\Service\UuidGenerator\UuidGenerator;
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
function createRedis(Config $config): \Redis
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
        getMysqlHostForCurrentEnvironment($db_config->host),
        $db_config->schema
    );
    $pdo_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
//        PDO::ATTR_PERSISTENT => true
    ];

    var_dump($dsn_string);
    var_dump(getenv());
    exit(0);

    // PHP 8.5 moved MYSQL_ATTR_FOUND_ROWS onto Pdo\Mysql.
    if (PHP_VERSION_ID < 80500) {
        $pdo_options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
    }
    else {
        $pdo_options[\Pdo\Mysql::ATTR_FOUND_ROWS] = true;
    }

    // TODO - return a readonly connection.
    // this needs a little thought to allow people to login, or you know, write to the DB
    $pdo = new \PDO(
        $dsn_string,
        $db_config->username,
        $db_config->password,
        $pdo_options
    );

    return $pdo;
}

/**
 * @return \Amp\Mysql\MysqlConnection
 * @throws Exception
 */
function createMysqlClient(Config $config): \Amp\Mysql\MysqlConnection
{
    $db_config = $config->getDatabaseUserConfig();

    $mysql_config = new \Amp\Mysql\MysqlConfig(
        getMysqlHostForCurrentEnvironment($db_config->host),
        \Amp\Mysql\MysqlConfig::DEFAULT_PORT,
        $db_config->username,
        $db_config->password,
        $db_config->schema,
    );

    return \Amp\Mysql\connect($mysql_config);
}


function createSessionConfig(): Asm\SessionConfig
{
    $thirty_days = 3600 * 24 * 30;

    return new Asm\SessionConfig(
        "john_is_my_name",
        $thirty_days,
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

function createBristolStairsFilesystem(Config $config): \Bristolian\Filesystem\BristolStairsFilesystem
{
    $bucketName = 'bristolian-stairs-images';

    if ($config->isProductionEnv() !== true) {
        $bucketName = 'bristolian-stairs-images-dev';
    }

    // SETUP
    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud',
        'http' => [
            'timeout' => 15,          // seconds to wait for response
            'connect_timeout' => 4,  // seconds to wait for TCP connection
        ],
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
    $filesystem = new \Bristolian\Filesystem\BristolStairsFilesystem($adapter, $config);

    return $filesystem;
}


function createAvatarImageFilesystem(Config $config): \Bristolian\Filesystem\AvatarImageFilesystem
{
    $bucketName = 'bristolian-avatar-images';

    if ($config->isProductionEnv() !== true) {
        $bucketName = 'bristolian-avatar-images-dev';
    }

    // SETUP
    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud',
        'http' => [
            'timeout' => 15,          // seconds to wait for response
            'connect_timeout' => 4,  // seconds to wait for TCP connection
        ],
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
    $filesystem = new \Bristolian\Filesystem\AvatarImageFilesystem($adapter, $config);

    return $filesystem;
}



function createUserDocumentsFilesystem(Config $config): \Bristolian\Filesystem\UserDocumentsFilesystem
{
    $bucketName = 'bristolian-user-documents';

    if ($config->isProductionEnv() !== true) {
        $bucketName = 'bristolian-user-documents-dev';
    }

    // SETUP
    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud',
        'http' => [
            'timeout' => 15,          // seconds to wait for response
            'connect_timeout' => 4,  // seconds to wait for TCP connection
        ],
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
    $filesystem = new \Bristolian\Filesystem\UserDocumentsFilesystem($adapter, $config);

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
 * Create a Flysystem filesystem for a Scaleway bucket that must be a -dev bucket.
 * Used by destructive CLI cleanup so production buckets cannot be targeted.
 *
 * @throws \InvalidArgumentException when the bucket name does not end with -dev
 */
function createDevOnlyObjectFilesystem(string $bucketName): \League\Flysystem\Filesystem
{
    if (str_ends_with($bucketName, '-dev') !== true) {
        throw new \InvalidArgumentException(
            "Refusing to create filesystem for non-dev bucket: {$bucketName}"
        );
    }

    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud',
        'http' => [
            'timeout' => 15,
            'connect_timeout' => 4,
        ],
    ]);

    $adapter = new AwsS3V3Adapter(
        $client,
        $bucketName,
        '',
        new PortableVisibilityConverter(
            Visibility::PRIVATE
        )
    );

    return new \League\Flysystem\Filesystem($adapter, []);
}

/**
 * Create a read-only Flysystem filesystem for a Scaleway production bucket.
 * Used by the archive CLI so production buckets cannot be written or deleted through this handle.
 *
 * @throws \InvalidArgumentException when the bucket name ends with -dev
 */
function createProductionOnlyObjectFilesystem(string $bucketName): \League\Flysystem\Filesystem
{
    if (str_ends_with($bucketName, '-dev') === true) {
        throw new \InvalidArgumentException(
            "Refusing to create production filesystem for -dev bucket: {$bucketName}"
        );
    }

    $client = new S3Client([
        'credentials' => [
            'key' => getScalewayApiKey(),
            'secret' => getScalewayApiSecret(),
        ],
        'region' => 'nl-ams',
        'endpoint' => 'https://s3.nl-ams.scw.cloud',
        'http' => [
            'timeout' => 15,
            'connect_timeout' => 4,
        ],
    ]);

    $adapter = new AwsS3V3Adapter(
        $client,
        $bucketName,
        '',
        new PortableVisibilityConverter(
            Visibility::PRIVATE
        )
    );

    $readOnlyAdapter = new \League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter($adapter);

    return new \League\Flysystem\Filesystem($readOnlyAdapter, []);
}

/**
 * Local Flysystem rooted at the project archive/ directory (often a symlink).
 *
 * @throws \RuntimeException when archive/ is missing or not writable
 */
function createArchiveLocalFilesystem(): \League\Flysystem\Filesystem
{
    $archiveRoot = __DIR__ . '/../archive';

    if (is_dir($archiveRoot) !== true) {
        throw new \RuntimeException(
            "Archive directory does not exist: {$archiveRoot}"
        );
    }

    if (is_writable($archiveRoot) !== true) {
        throw new \RuntimeException(
            "Archive directory is not writable: {$archiveRoot}"
        );
    }

    $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($archiveRoot);

    return new \League\Flysystem\Filesystem($adapter, []);
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


function createUnknownQueryHandler(
    Config $config,
    \Redis $redis
): \Bristolian\Cache\UnknownQueryHandler {
    if ($config->isProductionEnv()) {
        return new \Bristolian\Cache\RedisLogUnknownQuery($redis);
    }

    return new \Bristolian\Cache\ThrowOnUnknownQuery();
}


function createUnknownCacheQueriesProvider(\Redis $redis): \Bristolian\Service\UnknownCacheQueries\UnknownCacheQueriesProvider
{
    return new \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider($redis);
}


function createPdoSimpleWithTableTracking(
    \PDO $pdo,
    UuidGenerator $uuidGenerator,
    \Bristolian\Cache\TableAccessRecorder $recorder,
    \Bristolian\Cache\UnknownQueryHandler $unknownQueryHandler
): \Bristolian\PdoSimple\PdoSimpleWithTableTracking {
    $exactMappings = \Bristolian\Cache\QueryTagMapping::getExactMappings();
    $patternMappings = \Bristolian\Cache\QueryTagMapping::getPatternMappings();

    return new \Bristolian\PdoSimple\PdoSimpleWithTableTracking(
        $pdo,
        $uuidGenerator,
        $recorder,
        $exactMappings,
        $patternMappings,
        $unknownQueryHandler
    );
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


/**
 * If any controller requests a UserSession but the person making the
 * request is not logged in, then an UnauthorisedException is thrown.
 *
 * @param \Bristolian\Session\AppSessionManager $appSessionManager
 * @return \Bristolian\Session\AppSession
 * @throws \Bristolian\Exception\BristolianException
 * @throws \Bristolian\Exception\UnauthorisedException
 */
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

function createBccTroExecutionCheck(Config $config): \Bristolian\Service\DailyProcessorSchedule\LocalDevBccTroExecutionCheck
{
    if ($config->isProductionEnv()) {
        throw new \Exception("haven't written production version yet.");
    }

    return new \Bristolian\Service\DailyProcessorSchedule\LocalDevBccTroExecutionCheck();
}
