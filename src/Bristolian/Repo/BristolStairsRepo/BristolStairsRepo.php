<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;

interface BristolStairsRepo
{
    public function store_stairs_info(
        string $stored_stair_image_file_id,
        string $description,
        float $latitude,
        float $longitude,
        int $steps,
    ): BristolStairInfo;


    /**
     * @return array{0:int, 1:int}
     */
    public function get_total_number_of_steps(): array;

    /**
     * @return BristolStairInfo[]
     */
    public function getAllStairsInfo(): array;

    public function getStairInfoById(int $id): BristolStairInfo|null;

    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params): void;

    public function updateStairPosition(BristolStairsPositionParams $stairs_position_params): void;
}
