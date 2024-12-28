<?php

namespace Functions;

use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 * @TODO - these tests could really do with some assertions.
 */
class FactoriesFunctionsTest extends BaseTestCase
{
    /**
     * @covers ::forbidden
     */
    public function test_forbidden()
    {
        $this->expectException(\DI\InjectionException::class);
        forbidden($this->injector);
    }

    /**
     * @covers ::createMemoryWarningCheck
     */
    public function test_createMemoryWarningCheck()
    {
        $this->injector->execute(createMemoryWarningCheck(...));
    }

    /**
     * @covers ::createRedis
     */
    public function test_createRedis()
    {
        $result = $this->injector->execute('createRedis');
    }

    /**
     * @covers ::createRedisCachedUrlFetcher
     */
    public function test_createRedisCachedUrlFetcher()
    {
        $this->injector->execute(createRedisCachedUrlFetcher(...));
    }


    /**
     * @covers ::getRedisConfig
     */
    public function test_getRedisConfig()
    {
        $this->injector->execute(getRedisConfig(...));
    }

    /**
     * @covers ::getRedisOptions
     */
    public function test_getRedisOptions()
    {
        $this->injector->execute(getRedisOptions(...));
    }

    /**
     * @covers ::createPredisClient
     */
    public function test_createPredisClient()
    {
        $this->injector->execute(createPredisClient(...));
    }


    /**
     * @covers ::createApiDomain
     */
    public function test_createApiDomain()
    {
        $this->injector->execute(createApiDomain(...));
    }

    /**
     * @covers ::createPDOForUser
     */
    public function test_createPDOForUser()
    {
        $this->injector->execute(createPDOForUser(...));
    }

    /**
     * @covers ::createSessionConfig
     */
    public function test_createSessionConfig()
    {
        $this->injector->execute(createSessionConfig(...));
    }

    /**
     * @covers ::createLocalFilesystem
     */
    public function test_createLocalFilesystem()
    {
        $this->injector->execute(createLocalFilesystem(...));
    }

    /**
     * @covers ::createLocalCacheFilesystem
     */
    public function test_createLocalCacheFilesystem()
    {
        $this->injector->execute(createLocalCacheFilesystem(...));
    }

    /**
     * @covers ::createMemeFilesystem
     */
    public function test_createMemeFilesystem()
    {
        $this->injector->execute(createMemeFilesystem(...));
    }

    /**
     * @covers ::createRoomFileFilesystem
     */
    public function test_createRoomFileFilesystem()
    {
        $this->injector->execute(createRoomFileFilesystem(...));
    }

    /**
     * @covers ::createDeployLogRenderer
     */
    public function test_createDeployLogRenderer()
    {
        $this->injector->execute(createDeployLogRenderer(...));
    }

    /**
     * @covers ::createMailgun
     */
    public function test_createMailgun()
    {
        $this->injector->execute(createMailgun(...));
    }
}
