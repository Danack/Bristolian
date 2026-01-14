<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Model\Generated\BristolStairInfo;

class FakeBristolStairsRepo implements BristolStairsRepo
{
//    private const BRISTOL_CENTRE_LATITUDE = 51.4536491;
//    private const BRISTOL_CENTRE_LONGITUDE = -2.5913353;

    /** @var BristolStairInfo[] */
    private array $stairs = [];

    public function __construct()
    {
        $this->stairs = [
            new BristolStairInfo(
                1,
                'Steep stairs near Park Street',
                51.4556,
                -2.5943,
                'fake_image_1',
                45,
                0,
                new \DateTimeImmutable('2024-01-15 10:00:00'),
                new \DateTimeImmutable('2024-01-15 10:00:00')
            ),
            new BristolStairInfo(
                2,
                'Historic stairs near Harbourside',
                51.4516,
                -2.5883,
                'fake_image_2',
                32,
                0,
                new \DateTimeImmutable('2024-02-20 14:30:00'),
                new \DateTimeImmutable('2024-02-20 14:30:00')
            ),
            new BristolStairInfo(
                3,
                'Modern stairs at Clifton Triangle',
                51.4576,
                -2.5963,
                'fake_image_3',
                28,
                0,
                new \DateTimeImmutable('2024-03-10 09:15:00'),
                new \DateTimeImmutable('2024-03-10 09:15:00')
            ),
        ];
    }

    public function store_stairs_info(
        string $stored_stair_image_file_id,
        string $description,
        float $latitude,
        float $longitude,
        int $steps
    ): BristolStairInfo {
        $new_id = count($this->stairs) + 1;
        $now = new \DateTimeImmutable();

        $stair_info = new BristolStairInfo(
            $new_id,
            $description,
            $latitude,
            $longitude,
            $stored_stair_image_file_id,
            $steps,
            0,
            $now,
            $now
        );

        $this->stairs[] = $stair_info;

        return $stair_info;
    }

    public function get_total_number_of_steps(): array
    {
        $flights_of_stairs = 0;
        $total_steps = 0;

        foreach ($this->stairs as $stair) {
            if ($stair->is_deleted === 0) {
                $flights_of_stairs++;
                $total_steps += $stair->steps;
            }
        }

        return [$flights_of_stairs, $total_steps];
    }

    public function getAllStairsInfo(): array
    {
        return array_filter($this->stairs, fn($stair) => $stair->is_deleted === 0);
    }

    public function getStairInfoById(int $id): BristolStairInfo|null
    {
        foreach ($this->stairs as $stair) {
            if ($stair->id === $id && $stair->is_deleted === 0) {
                return $stair;
            }
        }

        throw ContentNotFoundException::stairs_id_not_found(
            (string)$id
        );
    }

    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params): void
    {
        $id = (int)$stairs_info_params->bristol_stair_info_id;
        foreach ($this->stairs as $index => $stair) {
            if ($stair->id === $id) {
                $this->stairs[$index] = new BristolStairInfo(
                    $stair->id,
                    $stairs_info_params->description,
                    $stair->latitude,
                    $stair->longitude,
                    $stair->stored_stair_image_file_id,
                    (int)$stairs_info_params->steps,
                    $stair->is_deleted,
                    $stair->created_at,
                    new \DateTimeImmutable()
                );
                return;
            }
        }

        throw ContentNotFoundException::stairs_id_not_found(
            $stairs_info_params->bristol_stair_info_id
        );
    }

    public function updateStairPosition(BristolStairsPositionParams $stairs_position_params): void
    {
        $id = (int)$stairs_position_params->bristol_stair_info_id;
        foreach ($this->stairs as $index => $stair) {
            if ($stair->id === $id) {
                $this->stairs[$index] = new BristolStairInfo(
                    $stair->id,
                    $stair->description,
                    (float)$stairs_position_params->latitude,
                    (float)$stairs_position_params->longitude,
                    $stair->stored_stair_image_file_id,
                    $stair->steps,
                    $stair->is_deleted,
                    $stair->created_at,
                    new \DateTimeImmutable()
                );
                return;
            }
        }

        throw ContentNotFoundException::stairs_id_not_found(
            $stairs_position_params->bristol_stair_info_id
        );
    }
}
