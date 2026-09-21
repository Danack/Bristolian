<?php

namespace Functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use Bristolian\App;
use Bristolian\Config\Config;
use Bristolian\Data\DatabaseUserConfig;
use Bristolian\Config\RedisConfig;
use Bristolian\Session\FakeAppSessionManager;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Test Config class that allows testing production environment paths
 *
 */
class TestProductionConfig extends Config
{
    private bool $isProduction;

    public function __construct(bool $isProduction = false)
    {
        parent::__construct();
        $this->isProduction = $isProduction;
    }

    public function isProductionEnv(): bool
    {
        return $this->isProduction;
    }
}

/**
 * AppSessionManager that returns null from getCurrentAppSession for testing createAppSession throw path.
 *
 */
class AppSessionManagerReturnsNull extends \Bristolian\Session\AppSessionManager
{
    public function __construct(\Asm\SessionManager $sessionManager)
    {
        parent::__construct($sessionManager);
    }

    public function getCurrentAppSession(): \Bristolian\Session\AppSession|null
    {
        return null;
    }
}

/**
 * AppSessionManager that returns a given session from getCurrentAppSession for testing success path.
 *
 */
class AppSessionManagerReturnsSession extends \Bristolian\Session\AppSessionManager
{
    public function __construct(
        \Asm\SessionManager $sessionManager,
        private \Bristolian\Session\AppSession $appSession
    ) {
        parent::__construct($sessionManager);
    }

    public function getCurrentAppSession(): \Bristolian\Session\AppSession|null
    {
        return $this->appSession;
    }
}

#[CoversFunction('createApiDomain')]
#[CoversFunction('createAppSession')]
#[CoversFunction('createAvatarImageFilesystem')]
#[CoversFunction('createBristolStairsFilesystem')]
#[CoversFunction('createDeployLogRenderer')]
#[CoversFunction('createLocalCacheFilesystem')]
#[CoversFunction('createLocalFilesystem')]
#[CoversFunction('createMailgun')]
#[CoversFunction('createMemeFilesystem')]
#[CoversFunction('createMemoryWarningCheck')]
#[CoversFunction('createMysqlClient')]
#[CoversFunction('createOptionalUserSession')]
#[CoversFunction('createPDOForUser')]
#[CoversFunction('createPdoSimpleWithTableTracking')]
#[CoversFunction('createPredisClient')]
#[CoversFunction('createRedis')]
#[CoversFunction('createRedisCachedUrlFetcher')]
#[CoversFunction('createRoomFileFilesystem')]
#[CoversFunction('createSessionConfig')]
#[CoversFunction('createUnknownCacheQueriesProvider')]
#[CoversFunction('createUnknownQueryHandler')]
#[CoversFunction('createUserDocumentsFilesystem')]
#[CoversFunction('forbidden')]
#[CoversFunction('getRedisConfig')]
#[CoversFunction('getRedisOptions')]

class FactoriesFunctionsTest extends BaseTestCase
{
    public function test_forbidden()
    {
        $this->expectException(\DI\InjectionException::class);
        forbidden($this->injector);
    }

    public function test_createMemoryWarningCheck()
    {
        $result = $this->injector->execute(createMemoryWarningCheck(...));
        
        $this->assertInstanceOf(
            \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class,
            $result
        );
        // In test environment, should return DevEnvironmentMemoryWarning
        $this->assertInstanceOf(
            \Bristolian\Service\MemoryWarningCheck\DevEnvironmentMemoryWarning::class,
            $result
        );
    }

    public function test_createMemoryWarningCheck_production(): void
    {
        $config = new TestProductionConfig(true);
        $result = createMemoryWarningCheck($config, $this->injector);
        $this->assertInstanceOf(
            \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck::class,
            $result
        );
        $this->assertInstanceOf(
            \Bristolian\Service\MemoryWarningCheck\ProdMemoryWarningCheck::class,
            $result
        );
    }

    #[RequiresPhpExtension('redis')]
    public function test_createRedis()
    {
        $result = $this->injector->execute('createRedis');
        
        $this->assertInstanceOf(\Redis::class, $result);
    }

    #[RequiresPhpExtension('redis')]
    public function test_createRedisCachedUrlFetcher()
    {
        $result = $this->injector->execute(createRedisCachedUrlFetcher(...));
        
        $this->assertInstanceOf(\UrlFetcher\RedisCachedUrlFetcher::class, $result);
    }

    #[RequiresPhpExtension('redis')]
    public function test_getRedisConfig()
    {
        $config = $this->injector->make(Config::class);
        $result = getRedisConfig($config);
        
        $this->assertArrayHasKey('scheme', $result);
        $this->assertArrayHasKey('host', $result);
        $this->assertArrayHasKey('port', $result);
        $this->assertArrayHasKey('password', $result);
        $this->assertSame('tcp', $result['scheme']);
    }

    public function test_getRedisOptions()
    {
        $result = getRedisOptions();
        
        $this->assertArrayHasKey('profile', $result);
        $this->assertArrayHasKey('prefix', $result);
        $this->assertSame('2.6', $result['profile']);
        $this->assertSame('bristolian:', $result['prefix']);
    }

    public function test_createPredisClient()
    {
        $result = $this->injector->execute(createPredisClient(...));
        
        $this->assertInstanceOf(\Predis\Client::class, $result);
    }

    public function test_createApiDomain()
    {
        $config = $this->injector->make(Config::class);
        $result = createApiDomain($config);
        
        $this->assertInstanceOf(\Bristolian\Data\ApiDomain::class, $result);
        // In test environment, should return local domain
        $this->assertStringContainsString('local', $result->getDomain());
        $this->assertSame('http://local.api.bristolian.org', $result->getDomain());
    }

    public function test_createApiDomain_production()
    {
        $config = new TestProductionConfig(true);
        $result = createApiDomain($config);
        
        $this->assertInstanceOf(\Bristolian\Data\ApiDomain::class, $result);
        $this->assertSame('https://api.bristolian.org', $result->getDomain());
    }

    public function test_createPDOForUser()
    {
        $result = $this->injector->execute(createPDOForUser(...));
        
        $this->assertInstanceOf(\PDO::class, $result);
    }

    public function test_createMysqlClient(): void
    {
        $result = $this->injector->execute(createMysqlClient(...));

        $this->assertInstanceOf(\Amp\Mysql\MysqlConnection::class, $result);
    }

    public function test_createSessionConfig()
    {
        $result = createSessionConfig();
        
        $this->assertInstanceOf(\Asm\SessionConfig::class, $result);
        $thirty_days = 3600 * 24 * 30;
        $this->assertSame($thirty_days, $result->getLifetime());
        $this->assertSame('john_is_my_name', $result->getSessionName());
    }

    public function test_createLocalFilesystem()
    {
        $result = createLocalFilesystem();
        
        $this->assertInstanceOf(\Bristolian\Filesystem\LocalFilesystem::class, $result);
    }

    public function test_createLocalCacheFilesystem()
    {
        $result = createLocalCacheFilesystem();
        
        $this->assertInstanceOf(\Bristolian\Filesystem\LocalCacheFilesystem::class, $result);
    }

    public function test_createMemeFilesystem()
    {
        $result = $this->injector->execute(createMemeFilesystem(...));
        
        $this->assertInstanceOf(\Bristolian\Filesystem\MemeFilesystem::class, $result);
    }

    public function test_createRoomFileFilesystem()
    {
        $result = $this->injector->execute(createRoomFileFilesystem(...));
        
        $this->assertInstanceOf(\Bristolian\Filesystem\RoomFileFilesystem::class, $result);
    }

    public function test_createBristolStairsFilesystem()
    {
        $result = $this->injector->execute(createBristolStairsFilesystem(...));
        
        $this->assertInstanceOf(\Bristolian\Filesystem\BristolStairsFilesystem::class, $result);
    }

    public function test_createAvatarImageFilesystem()
    {
        $result = $this->injector->execute(createAvatarImageFilesystem(...));
        
        $this->assertInstanceOf(\Bristolian\Filesystem\AvatarImageFilesystem::class, $result);
    }

    public function test_createUserDocumentsFilesystem()
    {
        $result = $this->injector->execute(createUserDocumentsFilesystem(...));
        
        $this->assertInstanceOf(\Bristolian\Filesystem\UserDocumentsFilesystem::class, $result);
    }

    public function test_createDeployLogRenderer()
    {
        $result = $this->injector->execute(createDeployLogRenderer(...));
        
        $this->assertInstanceOf(
            \Bristolian\Service\DeployLogRenderer\DeployLogRenderer::class,
            $result
        );
        // In test environment, should return LocalDeployLogRenderer
        $this->assertInstanceOf(
            \Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer::class,
            $result
        );
    }

    public function test_createDeployLogRenderer_production()
    {
        $config = new TestProductionConfig(true);
        $result = createDeployLogRenderer($config);
        
        $this->assertInstanceOf(
            \Bristolian\Service\DeployLogRenderer\DeployLogRenderer::class,
            $result
        );
        $this->assertInstanceOf(
            \Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer::class,
            $result
        );
    }

    public function test_createMemeFilesystem_production_bucket()
    {
        $config = new TestProductionConfig(true);
        $result = createMemeFilesystem($config);
        
        $this->assertInstanceOf(\Bristolian\Filesystem\MemeFilesystem::class, $result);
    }

    public function test_createBristolStairsFilesystem_production_bucket()
    {
        $config = new TestProductionConfig(true);
        $result = createBristolStairsFilesystem($config);
        
        $this->assertInstanceOf(\Bristolian\Filesystem\BristolStairsFilesystem::class, $result);
    }

    public function test_createAvatarImageFilesystem_production_bucket()
    {
        $config = new TestProductionConfig(true);
        $result = createAvatarImageFilesystem($config);
        
        $this->assertInstanceOf(\Bristolian\Filesystem\AvatarImageFilesystem::class, $result);
    }

    public function test_createUserDocumentsFilesystem_production_bucket()
    {
        $config = new TestProductionConfig(true);
        $result = createUserDocumentsFilesystem($config);
        
        $this->assertInstanceOf(\Bristolian\Filesystem\UserDocumentsFilesystem::class, $result);
    }

    public function test_createRoomFileFilesystem_production_bucket()
    {
        $config = new TestProductionConfig(true);
        $result = createRoomFileFilesystem($config);
        
        $this->assertInstanceOf(\Bristolian\Filesystem\RoomFileFilesystem::class, $result);
    }

    /**
     * @group db
     */
    #[RequiresPhpExtension('redis')]
    public function test_createUnknownQueryHandler_dev_returns_ThrowOnUnknownQuery(): void
    {
        $config = new TestProductionConfig(false);
        $redis = $this->injector->make(\Redis::class);
        $result = createUnknownQueryHandler($config, $redis);
        $this->assertInstanceOf(\Bristolian\Cache\ThrowOnUnknownQuery::class, $result);
    }

    /**
     * @group db
     */
    #[RequiresPhpExtension('redis')]
    public function test_createUnknownQueryHandler_production_returns_RedisLogUnknownQuery(): void
    {
        $config = new TestProductionConfig(true);
        $redis = $this->injector->make(\Redis::class);
        $result = createUnknownQueryHandler($config, $redis);
        $this->assertInstanceOf(\Bristolian\Cache\RedisLogUnknownQuery::class, $result);
    }

    /**
     * @group db
     */
    #[RequiresPhpExtension('redis')]
    public function test_createUnknownCacheQueriesProvider_returns_RedisProvider(): void
    {
        $redis = $this->injector->make(\Redis::class);
        $result = createUnknownCacheQueriesProvider($redis);
        $this->assertInstanceOf(
            \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider::class,
            $result
        );
    }

    /**
     * @group db
     */
    public function test_createPdoSimpleWithTableTracking_returns_instance(): void
    {
        $result = $this->injector->execute(createPdoSimpleWithTableTracking(...));
        $this->assertInstanceOf(
            \Bristolian\PdoSimple\PdoSimpleWithTableTracking::class,
            $result
        );
    }

    public function test_createMailgun()
    {
        $result = $this->injector->execute(createMailgun(...));
        
        $this->assertInstanceOf(\Mailgun\Mailgun::class, $result);
    }

    public function test_createOptionalUserSession(): void
    {
        $sessionManager = new \Asm\SessionManager(
            createSessionConfig(),
            new \Bristolian\Session\FakeAsmDriver()
        );
        $appSessionManager = new AppSessionManagerReturnsNull($sessionManager);
        $result = createOptionalUserSession($appSessionManager);
        $this->assertInstanceOf(
            \Bristolian\Session\StandardOptionalUserSession::class,
            $result
        );
        $this->assertNull($result->getAppSession());
    }

    public function test_createAppSession_throws_when_not_logged_in(): void
    {
        $sessionManager = new \Asm\SessionManager(
            createSessionConfig(),
            new \Bristolian\Session\FakeAsmDriver()
        );
        $appSessionManager = new AppSessionManagerReturnsNull($sessionManager);
        $this->expectException(\Bristolian\Exception\UnauthorisedException::class);
        $this->expectExceptionMessage('Something depends on AppSession but user not logged in');
        createAppSession($appSessionManager);
    }

    public function test_createAppSession_returns_session_when_logged_in(): void
    {
        $sessionManager = new \Asm\SessionManager(
            createSessionConfig(),
            new \Bristolian\Session\FakeAsmDriver()
        );
        $appSession = new \Bristolian\Session\AppSession(new \BristolianTest\Session\FakeAsmSession());
        $appSessionManager = new AppSessionManagerReturnsSession($sessionManager, $appSession);
        $result = createAppSession($appSessionManager);
        $this->assertInstanceOf(\Bristolian\Session\AppSession::class, $result);
        $this->assertSame($appSession, $result);
    }
}
