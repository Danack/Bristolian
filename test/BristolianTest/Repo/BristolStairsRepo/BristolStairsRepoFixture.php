<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for BristolStairsRepo implementations.
 */
abstract class BristolStairsRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the BristolStairsRepo implementation.
     *
     * @return BristolStairsRepo
     */
    abstract public function getTestInstance(): BristolStairsRepo;

    /**
     * Get a test stair image file ID. Override in PDO tests to create actual file.
     */
    protected function getTestStairImageFileId(): string
    {
        return 'test_image_id';
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::store_stairs_info
     */
    public function test_store_stairs_info(): void
    {
        $repo = $this->getTestInstance();

        $stored_stair_image_file_id = $this->getTestStairImageFileId();
        $description = 'Test stairs description';
        $latitude = 51.4600;
        $longitude = -2.6000;
        $steps = 50;

        $stair = $repo->store_stairs_info(
            $stored_stair_image_file_id,
            $description,
            $latitude,
            $longitude,
            $steps
        );

        $this->assertInstanceOf(BristolStairInfo::class, $stair);
        $this->assertSame($description, $stair->description);
        $this->assertSame($latitude, $stair->latitude);
        $this->assertSame($longitude, $stair->longitude);
        $this->assertSame($steps, $stair->steps);
        $this->assertSame($stored_stair_image_file_id, $stair->stored_stair_image_file_id);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::get_total_number_of_steps
     */
    public function test_get_total_number_of_steps(): void
    {
        $repo = $this->getTestInstance();

        [$flights_of_stairs, $total_steps] = $repo->get_total_number_of_steps();

        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        $this->assertIsInt($flights_of_stairs);
        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        $this->assertIsInt($total_steps);
        $this->assertGreaterThanOrEqual(0, $flights_of_stairs);
        $this->assertGreaterThanOrEqual(0, $total_steps);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::getAllStairsInfo
     */
    public function test_getAllStairsInfo(): void
    {
        $repo = $this->getTestInstance();

        $all_stairs = $repo->getAllStairsInfo();

        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        $this->assertIsArray($all_stairs);
        foreach ($all_stairs as $stair) {
            $this->assertInstanceOf(BristolStairInfo::class, $stair);
        }
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::getStairInfoById
     */
    public function test_getStairInfoById_returns_null_or_throws_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance();

        // Note: Some implementations return null, others throw ContentNotFoundException
        // Both behaviors are valid - the interface allows nullable return type
        try {
            $stair = $repo->getStairInfoById(999999);
            // If no exception thrown, verify it returns null
            $this->assertNull($stair);
        } catch (ContentNotFoundException $e) {
            // Exception is also acceptable behavior
            $this->assertInstanceOf(ContentNotFoundException::class, $e);
        }
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::getStairInfoById
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::store_stairs_info
     */
    public function test_getStairInfoById_returns_stair_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $stair = $repo->store_stairs_info(
            $this->getTestStairImageFileId(),
            'Test stairs',
            51.4600,
            -2.6000,
            50
        );

        $found = $repo->getStairInfoById($stair->id);
        $this->assertNotNull($found);
        $this->assertInstanceOf(BristolStairInfo::class, $found);
        $this->assertSame($stair->id, $found->id);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::updateStairInfo
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::store_stairs_info
     */
    public function test_updateStairInfo(): void
    {
        $repo = $this->getTestInstance();

        $stair = $repo->store_stairs_info(
            $this->getTestStairImageFileId(),
            'Original description',
            51.4600,
            -2.6000,
            50
        );

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$stair->id,
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        // Should not throw an exception
        $repo->updateStairInfo($params);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::updateStairPosition
     * @covers \Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::store_stairs_info
     */
    public function test_updateStairPosition(): void
    {
        $repo = $this->getTestInstance();

        $stair = $repo->store_stairs_info(
            $this->getTestStairImageFileId(),
            'Test stairs',
            51.4600,
            -2.6000,
            50
        );

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$stair->id,
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        // Should not throw an exception
        $repo->updateStairPosition($params);
    }
}
