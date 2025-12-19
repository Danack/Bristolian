<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Model\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $json_data
     * @return void
     */
    public function saveData(array $tros): void;
}