<?php

declare(strict_types = 1);

namespace Bristolian;

class AssetLinkEmitter
{
    public function __construct(private Config $config)
    {
    }

    public function getAssetSuffix(): string
    {
        $forcesRefresh = $this->config->getForceAssetRefresh();

        if ($forcesRefresh) {
            return '?time=' . time();
        }

        $sha = $this->config->getCommitSha();

        return "?version=" . $sha;
    }
}
