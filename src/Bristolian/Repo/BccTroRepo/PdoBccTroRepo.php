<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Database\bcc_tro_information;
use Bristolian\Model\Types\BccTro;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Attribute\WritesTable;
use Bristolian\Model\Generated\BccTroInformation;

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

    public function getMostRecentData(): BccTro|null
    {
        $sql = bcc_tro_information::SELECT . " order by id desc";

        $latest_entry = $this->pdo_simple->fetchOneAsObjectOrNull(
            $sql,
            [],
            BccTroInformation::class
        );

        if ($latest_entry === null) {
            return null;
        }

        return BccTro::fromString($latest_entry->tro_data);
    }
}
