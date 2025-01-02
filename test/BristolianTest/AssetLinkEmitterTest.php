<?php

namespace BristolianTest;

use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\Config\HardCodedAssetLinkConfig;

/**
 * @coversNothing
 */
class AssetLinkEmitterTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\SiteHtml\AssetLinkEmitter
     */
    public function testWorks()
    {
        $sha = "abcdef";

        $assetLinkConfig = new HardCodedAssetLinkConfig(false, $sha);
        $linkEmitter = new AssetLinkEmitter($assetLinkConfig);

        $this->assertSame(
            "?version=" . $sha,
            $linkEmitter->getAssetSuffix()
        );

        $assetLinkConfig = new HardCodedAssetLinkConfig(true, $sha);
        $linkEmitter = new AssetLinkEmitter($assetLinkConfig);

        $this->assertStringStartsWith(
            "?time=",
            $linkEmitter->getAssetSuffix()
        );
    }
}
