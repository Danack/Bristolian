<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Database\bristol_stair_info;
use Bristolian\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\Exception\ContentNotFoundException;

class PdoBristolStairsRepo implements BristolStairsRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params): void
    {
        $sql = <<< SQL
update
  bristol_stair_info
set 
  description = :description,
  steps = :steps
where
  id = :id
  limit 1
SQL;

        $params = [
            ":description" => $stairs_info_params->description,
            ":steps" => $stairs_info_params->steps,
            ":id" => $stairs_info_params->bristol_stair_info_id,
        ];

        $rows_affected = $this->pdo_simple->execute($sql, $params);

        if ($rows_affected !== 1) {
            throw ContentNotFoundException::stairs_id_not_found(
                $stairs_info_params->bristol_stair_info_id
            );
        }
    }

    public function updateStairPosition(BristolStairsPositionParams $stairs_position_params): void
    {
        $sql = <<< SQL
update
  bristol_stair_info
set 
  latitude = :latitude,
  longitude = :longitude
where
  id = :id
  limit 1
SQL;

        $params = [
            ":latitude" => $stairs_position_params->latitude,
            ":longitude" => $stairs_position_params->longitude,
            ":id" => $stairs_position_params->bristol_stair_info_id,
        ];

        $rows_affected = $this->pdo_simple->execute($sql, $params);

        if ($rows_affected !== 1) {
            throw ContentNotFoundException::stairs_id_not_found(
                $stairs_position_params->bristol_stair_info_id
            );
        }
    }

    public function store_stairs_info(
        string $stored_stair_image_file_id,
        string $description,
        float $latitude,
        float $longitude,
        int $steps,
    ): BristolStairInfo {
        $sql = bristol_stair_info::INSERT;

        $params = [
            ':stored_stair_image_file_id' => $stored_stair_image_file_id,
            ':description' => $description,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':steps' => $steps,
            ':is_deleted' => 0,
        ];

        $insert_id = $this->pdo_simple->insert($sql, $params);

        $stair_info = $this->getStairInfoById($insert_id);

        if ($stair_info === null) {
            // @codeCoverageIgnoreStart
            // This probably can't happen
            throw new \Exception("Failed to store stairs_info");
            // @codeCoverageIgnoreEnd
        }

        return $stair_info;
    }


    /**
     * @return BristolStairInfo[]
     */
    public function getAllStairsInfo(): array
    {
        $sql = bristol_stair_info::SELECT;

        $sql .= "where is_deleted = 0";

        return $this->pdo_simple->fetchAllAsObjectConstructor(
            $sql,
            [],
            BristolStairInfo::class
        );
    }


    public function getStairInfoById(int $id): BristolStairInfo|null
    {
        $sql = bristol_stair_info::SELECT;
        $sql .= "where id = :id and is_deleted = 0";

        return $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [':id' => $id],
            BristolStairInfo::class
        );
    }


    /**
     * @return array{0:int, 1:int}
     */
    public function get_total_number_of_steps(): array
    {
        $sql = <<< SQL
select sum(1) as flights_of_stairs, sum(steps) as total_steps from bristol_stair_info where is_deleted = 0
SQL;

        $result = $this->pdo_simple->fetchOneAsDataOrNull($sql, []);

        if ($result["total_steps"] === null) {
            // @codeCoverageIgnoreStart
            // This only happens when the DB is empty
            return [0, 0];
            // @codeCoverageIgnoreEnd
        }

        return [
            $result["flights_of_stairs"],
            $result["total_steps"]
        ];
    }
}
