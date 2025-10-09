<?php

namespace Bristolian\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;

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
                '1',
                '51.4556',
                '-2.5943',
                'Steep stairs near Park Street',
                'fake_image_1',
                45,
                0,
                new \DateTimeImmutable('2024-01-15 10:00:00'),
                new \DateTimeImmutable('2024-01-15 10:00:00')
            ),
            new BristolStairInfo(
                '2',
                '51.4516',
                '-2.5883',
                'Historic stairs near Harbourside',
                'fake_image_2',
                32,
                0,
                new \DateTimeImmutable('2024-02-20 14:30:00'),
                new \DateTimeImmutable('2024-02-20 14:30:00')
            ),
            new BristolStairInfo(
                '3',
                '51.4576',
                '-2.5963',
                'Modern stairs at Clifton Triangle',
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
        $new_id = (string)(count($this->stairs) + 1);
        $now = new \DateTimeImmutable();

        $stair_info = new BristolStairInfo(
            $new_id,
            (string)$latitude,
            (string)$longitude,
            $description,
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
            if ($stair->id === (string)$id && $stair->is_deleted === 0) {
                return $stair;
            }
        }

        throw ContentNotFoundException::stairs_id_not_found(
            (string)$id
        );
    }

    public function updateStairInfo(BristolStairsInfoParams $stairs_info_params): void
    {
        foreach ($this->stairs as $index => $stair) {
            if ($stair->id === $stairs_info_params->bristol_stair_info_id) {
                $this->stairs[$index] = new BristolStairInfo(
                    $stair->id,
                    $stair->latitude,
                    $stair->longitude,
                    $stairs_info_params->description,
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
        foreach ($this->stairs as $index => $stair) {
            if ($stair->id === $stairs_position_params->bristol_stair_info_id) {
                $this->stairs[$index] = new BristolStairInfo(
                    $stair->id,
                    (string)$stairs_position_params->latitude,
                    (string)$stairs_position_params->longitude,
                    $stair->description,
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
