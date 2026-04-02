<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $tros
     * @return int
     */
    public function saveData(array $tros): int;
}
