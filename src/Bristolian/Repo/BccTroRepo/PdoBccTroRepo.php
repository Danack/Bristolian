<?php

namespace Bristolian\Repo\BccTroRepo;

use BristolianGenerated\Database\bcc_tro_information;
use Bristolian\Model\Types\BccTro;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Model\BccTroInformation;

class PdoBccTroRepo implements BccTroRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    /**
     * @param BccTro[] $tros
     */
    #[WritesTable(bcc_tro_information::class)]
    public function saveData(array $tros): int
    {
        [$error, $data] = convertToValue($tros);

        if ($error !== null) {
            throw new \Exception($error);
        }

        $json = json_encode_safe($data);

        return $this->pdo_simple->insert(
            bcc_tro_information::INSERT,
            [':tro_data' => $json]
        );
    }

    /**
     * @param BccTro[] $tros
     */
    #[ReadsTable(bcc_tro_information::class)]
    #[WritesTable(bcc_tro_information::class)]
    public function saveDataIfNew(array $tros): int|null
    {
        [$error, $newData] = convertToValue($tros);

        if ($error !== null) {
            throw new \Exception($error);
        }

        $sql = bcc_tro_information::SELECT . " order by id desc limit 1";

        $latestEntry = $this->pdo_simple->fetchOneAsDataOrNull($sql, []);

        if ($latestEntry !== null) {
            $previousData = json_decode_safe($latestEntry['tro_data']);
            if (bccTroDataEquals($previousData, $newData)) {
                return null;
            }
        }

        return $this->saveData($tros);
    }

    #[ReadsTable(bcc_tro_information::class)]
    public function getMostRecentData(): BccTro|null
    {
        $sql = bcc_tro_information::SELECT . " order by id desc";

        $latest_entry = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [],
            BccTroInformation::class
        );

        if ($latest_entry === null) {
            return null;
        }

        return BccTro::createFromJson($latest_entry->tro_data);
    }
}
