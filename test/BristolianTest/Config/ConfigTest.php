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

        $this->assertIsBool($config->getForceAssetRefresh());

        $this->assertIsString($config->getVersion());
        $this->assertIsBool($config->isProductionEnv());

        $this->assertIsString($config->getCommitSha());
        $this->assertIsString($config->getMailgunApiKey());

        $dbConfig = $config->getDatabaseUserConfig();
        $this->assertInstanceOf(DatabaseUserConfig::class, $dbConfig);

        $this->assertIsString($config->getDeployTime());
        $this->assertIsString($config->getDatabaseSchema());

        $redisConfig = $config->getRedisInfo();
        $this->assertInstanceOf(RedisConfig::class, $redisConfig);
    }
}
