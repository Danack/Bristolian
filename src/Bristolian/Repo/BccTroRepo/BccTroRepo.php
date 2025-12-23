<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Model\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $tros
     * @return void
     */
    public function saveData(array $tros): void;
}