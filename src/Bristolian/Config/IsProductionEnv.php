<?php

namespace Bristolian\Config;

interface IsProductionEnv
{
    public function isProductionEnv(): bool;
}
