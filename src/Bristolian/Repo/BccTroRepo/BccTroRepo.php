<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $tros
     * @return void
     */
    public function saveData(array $tros): void;
}
