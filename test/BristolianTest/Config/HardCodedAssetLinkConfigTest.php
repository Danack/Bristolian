<?php

namespace BristolianTest\Config;

use Bristolian\Config\HardCodedAssetLinkConfig;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Config\HardCodedAssetLinkConfig
 */
class HardCodedAssetLinkConfigTest extends BaseTestCase
{
    public function testWorks()
    {
        $forceAssetRefresh = true;
        $commit_sha = "abcdef";

        $config = new HardCodedAssetLinkConfig(
            $forceAssetRefresh,
            $commit_sha,
        );

        $this->assertSame($forceAssetRefresh, $config->getForceAssetRefresh());
        $this->assertSame($commit_sha, $config->getCommitSha());
        $this->assertFalse($config->isProductionEnv());
    }

    public function testProductionEnvCanBeSet()
    {
        $config = new HardCodedAssetLinkConfig(false, "abcdef", true);

        $this->assertTrue($config->isProductionEnv());
    }
}
