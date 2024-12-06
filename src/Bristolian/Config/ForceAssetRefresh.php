<?php

namespace Bristolian\Config;

interface ForceAssetRefresh
{
    /**
     *
     * true - all assets should be reloaded on each page load
     * false - assets are reloaded on each deploy only
     *
     * @return bool
     */
    public function getForceAssetRefresh(): bool;
}
