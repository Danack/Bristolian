<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\bcc_tro_information;
use Bristolian\Model\Types\BccTro;

interface BccTroRepo
{
    /**
     * @param BccTro[] $tros
     * @return int
     */
    #[WritesTable(bcc_tro_information::class)]
    public function saveData(array $tros): int;

    /**
     * Saves the TRO list only when it differs from the most recently stored list.
     *
     * @param BccTro[] $tros
     * @return int|null Insert id when saved, null when the data is unchanged
     */
    #[ReadsTable(bcc_tro_information::class)]
    #[WritesTable(bcc_tro_information::class)]
    public function saveDataIfNew(array $tros): int|null;

    #[ReadsTable(bcc_tro_information::class)]
    public function getMostRecentData(): BccTro|null;
}
