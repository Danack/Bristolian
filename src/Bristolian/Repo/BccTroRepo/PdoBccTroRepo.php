<?php

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Database\bcc_tro_information;
use Bristolian\Model\Types\BccTro;
use Bristolian\PdoSimple\PdoSimple;

class PdoBccTroRepo implements BccTroRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    /**
     * @param BccTro[] $tros
     * @return void
     */
    public function saveData(array $tros): void
    {
        [$error, $data] = convertToValue($tros);

        if ($error !== null) {
            throw new \Exception($error);
        }

        $json = json_encode_safe($data);

        $this->pdo_simple->insert(
            bcc_tro_information::INSERT,
            [':tro_data' => $json]
        );
    }

    public function getMostRecentData(): null
    {
        return null;
    }

}