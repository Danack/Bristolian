<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\bristol_stair_info;
use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;

interface BristolStairsRepo
{
    #[WritesTable(bristol_stair_info::class)]
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
    #[ReadsTable(bristol_stair_info::class)]
    public function get_total_number_of_steps(): array;

    /**
     * @return BristolStairInfo[]
     */
    #[ReadsTable(bristol_stair_info::class)]
    public function getAllStairsInfo(): array;

    #[ReadsTable(bristol_stair_info::class)]
    public function getStairInfoById(int $id): BristolStairInfo|null;

    #[WritesTable(bristol_stair_info::class)]
    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params): void;

    #[WritesTable(bristol_stair_info::class)]
    public function updateStairPosition(BristolStairsPositionParams $stairs_position_params): void;
}
