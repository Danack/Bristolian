<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Attribute\WritesTable;
use Bristolian\Database\bcc_tro_information;
use Bristolian\Model\Types\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $tros
     * @return int
     */
    #[WritesTable(bcc_tro_information::class)]
    public function saveData(array $tros): int;
}
