<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;

interface BristolStairsRepo
{
//    public function getStairsInfoById(string $id): BristolStairInfo|null;

    public function store_stairs_info(
        string $stored_stair_image_file_id,
        string $description,
        float $latitude,
        float $longitude,
        int $steps,
    ): string;


    /**
     * @return {0:int, 1:int}
     */
    public function get_total_number_of_steps(): array;

    /**
     * @return BristolStairInfo[]
     */
    public function getAllStairsInfo(): array;

    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params);
}


