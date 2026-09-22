<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\bcc_tro_information;

interface BccTroRepo
{
    /**
     * @return int Insert id
     */
    #[WritesTable(bcc_tro_information::class)]
    public function saveData(string $html): int;

    /**
     * Saves the fetched page only when it differs from the most recently stored page.
     *
     * @return int|null Insert id when saved, null when the page is unchanged
     */
    #[ReadsTable(bcc_tro_information::class)]
    #[WritesTable(bcc_tro_information::class)]
    public function saveDataIfNew(string $html): int|null;

    #[ReadsTable(bcc_tro_information::class)]
    public function getMostRecentData(): string|null;
}
