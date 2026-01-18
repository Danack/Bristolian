<?php

namespace Bristolian\Service\BccTroFetcher;

use Bristolian\Model\Types\BccTro;

interface BccTroFetcher
{
    /**
     * @return BccTro[]
     */
    public function fetchTros(): array;
}
