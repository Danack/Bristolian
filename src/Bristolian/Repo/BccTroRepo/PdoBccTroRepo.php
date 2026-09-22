<?php

namespace Bristolian\Repo\BccTroRepo;

use BristolianGenerated\Database\bcc_tro_information;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Model\BccTroInformation;

class PdoBccTroRepo implements BccTroRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    #[WritesTable(bcc_tro_information::class)]
    public function saveData(string $html): int
    {
        return $this->pdo_simple->insert(
            bcc_tro_information::INSERT,
            [':tro_data' => $html]
        );
    }

    #[ReadsTable(bcc_tro_information::class)]
    #[WritesTable(bcc_tro_information::class)]
    public function saveDataIfNew(string $html): int|null
    {
        $sql = bcc_tro_information::SELECT . " order by id desc limit 1";

        $latestEntry = $this->pdo_simple->fetchOneAsDataOrNull($sql, []);

        if ($latestEntry !== null && $latestEntry['tro_data'] === $html) {
            return null;
        }

        return $this->saveData($html);
    }

    #[ReadsTable(bcc_tro_information::class)]
    public function getMostRecentData(): string|null
    {
        $sql = bcc_tro_information::SELECT . " order by id desc";

        $latest_entry = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [],
            BccTroInformation::class
        );

        if ($latest_entry === null) {
            // Difficult to test: databases may already contain rows, so an empty
            // table is not a reliable fixture.
            return null;
        }

        return $latest_entry->tro_data;
    }
}
