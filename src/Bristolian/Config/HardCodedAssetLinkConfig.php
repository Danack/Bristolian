<?php

namespace Bristolian\Config;

class HardCodedAssetLinkConfig implements AssetLinkEmitterConfig
{
    public function __construct(
        private bool $forceAssetRefresh,
        private string $commit_sha
    ) {
    }

    public function getForceAssetRefresh(): bool
    {
        return $this->forceAssetRefresh;
    }

    public function getCommitSha(): string
    {
        return $this->commit_sha;
    }
}
