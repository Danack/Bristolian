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
    function test_forbidden()
    {
        $this->expectException(\DI\InjectionException::class);
        forbidden($this->injector);
    }

    /**
     * @covers ::createMemoryWarningCheck
     */
    function test_createMemoryWarningCheck()
    {
        $this->injector->execute(createMemoryWarningCheck(...));
    }

    /**
     * @covers ::createRedis
     */
    function test_createRedis()
    {
        $result = $this->injector->execute('createRedis');
    }

    /**
     * @covers ::createRedisCachedUrlFetcher
     */
    function test_createRedisCachedUrlFetcher()
    {
        $this->injector->execute(createRedisCachedUrlFetcher(...));
    }


    /**
     * @covers ::getRedisConfig
     */
    function test_getRedisConfig()
    {
        $this->injector->execute(getRedisConfig(...));
    }

    /**
     * @covers ::getRedisOptions
     */
    function test_getRedisOptions()
    {
        $this->injector->execute(getRedisOptions(...));
    }

    /**
     * @covers ::createPredisClient
     */
    function test_createPredisClient()
    {
        $this->injector->execute(createPredisClient(...));
    }


    /**
     * @covers ::createApiDomain
     */
    function test_createApiDomain()
    {
        $this->injector->execute(createApiDomain(...));
    }

    /**
     * @covers ::createPDOForUser
     */
    function test_createPDOForUser()
    {
        $this->injector->execute(createPDOForUser(...));
    }

    /**
     * @covers ::createSessionConfig
     */
    function test_createSessionConfig()
    {
        $this->injector->execute(createSessionConfig(...));
    }

    /**
     * @covers ::createLocalFilesystem
     */
    function test_createLocalFilesystem()
    {
        $this->injector->execute(createLocalFilesystem(...));
    }

    /**
     * @covers ::createLocalCacheFilesystem
     */
    function test_createLocalCacheFilesystem()
    {
        $this->injector->execute(createLocalCacheFilesystem(...));
    }

    /**
     * @covers ::createMemeFilesystem
     */
    function test_createMemeFilesystem()
    {
        $this->injector->execute(createMemeFilesystem(...));
    }

    /**
     * @covers ::createRoomFileFilesystem
     */
    function test_createRoomFileFilesystem()
    {
        $this->injector->execute(createRoomFileFilesystem(...));
    }

    /**
     * @covers ::createDeployLogRenderer
     */
    function test_createDeployLogRenderer()
    {
        $this->injector->execute(createDeployLogRenderer(...));
    }

    /**
     * @covers ::createMailgun
     */
    function test_createMailgun()
    {
        $this->injector->execute(createMailgun(...));
    }
}
