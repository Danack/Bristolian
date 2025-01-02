<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

use Bristolian\Config\AssetLinkEmitterConfig;

class AssetLinkEmitter
{
    public function __construct(private AssetLinkEmitterConfig $config)
    {
    }

    public function getAssetSuffix(): string
    {
        // If $forcesRefresh is true - assets are refreshed every page load
        $forcesRefresh = $this->config->getForceAssetRefresh();
        if ($forcesRefresh) {
            return '?time=' . time();
        }

        // If $forcesRefresh is true - assets are refreshed every deploy
        $sha = $this->config->getCommitSha();

        return "?version=" . $sha;
    }
}
