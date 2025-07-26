<?php

namespace BristolianTest\Config;

use BristolianTest\BaseTestCase;
use Bristolian\Config\Config;
use Bristolian\Data\DatabaseUserConfig;
use Bristolian\Config\RedisConfig;

/**
 * @covers \Bristolian\Config\Config
 */
class ConfigTest extends BaseTestCase
{
    public function testWorks()
    {
        Config::testValuesArePresent();
        $config = new Config();

        $config->getForceAssetRefresh();
        $config->getVersion();
        $config->isProductionEnv();
        $config->getCommitSha();
        $config->getMailgunApiKey();



        $dbConfig = $config->getDatabaseUserConfig();
        $this->assertInstanceOf(DatabaseUserConfig::class, $dbConfig);

        $config->getDeployTime();
        $config->getDatabaseSchema();

        $redisConfig = $config->getRedisInfo();
        $this->assertInstanceOf(RedisConfig::class, $redisConfig);
    }
}
